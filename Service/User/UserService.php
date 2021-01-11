<?php

namespace Modules\User\Service\User;

use App\Exceptions\ServerExp;
use App\Helpers\InvitationCodeHelper;
use App\Helpers\ValidateHelper;
use App\Http\Controllers\Api\BaseController;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Bridge\PersonalAccessGrant;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use Modules\Auth\Models\AppUserLoginLogModel;
use Modules\Auth\Service\AppUserLoginLogService;
use Modules\Log\Models\GeneralLogModel;
use Modules\Log\Service\GeneralLogService;
use Modules\User\Cache\UserCache;
use Modules\User\Data\UserData;
use Modules\User\Models\AdminUserModel;
use Modules\User\Models\UserModel;
use App\Service\BaseService;
use Modules\User\Models\UserModelAbstract;

class UserService extends BaseService
{

    public static $modelClass = UserModel::class;

    protected function init($model)
    {
        $model->status = UserModel::statusActive;
        $model->gender = UserModel::genderN;
        $model->push = UserModel::pushTrue;
        $model->type = UserModel::typeUser;
        $model->email = null;
        $model->password = "";
        $model->is_vip = UserModel::isVipFalse;
        return $model;
    }


    // 仅后台可用
    public function setAccount($value)
    {
        $exist = AdminUserModel::where("account", $value)->where("id", "!=",$this->model->id)->first();
        if ($exist){
            throw new ServerExp("用户名已经被使用了");
        }

        if (!$this->isCreate()){
            if ($this->model->account == "admin"){
                $exist = AdminUserModel::where("account", "admin")->first();
                if ($exist){
                    if ($value != "admin"){
                        throw new ServerExp("禁止修改admin账户用户名");
                    }
                }
            }

        }

        $this->setFields('account', $value);
        return $this;
    }

    public function setEmail($value)
    {
        $exist = UserModel::where("email", $value)->where("id", "!=", $this->model->id)->first();
        if ($exist){
            throw new ServerExp("邮箱已被使用");
        }
        $this->setFields('email', $value);
        return $this;
    }

    public function setType($value)
    {
        $this->setFields('type', $value);
        return $this;
    }

    public function setPassword($value)
    {
        $this->setFields('password', bcrypt($value));
        return $this;
    }

    public function setRememberToken($value)
    {
        $this->setFields('remember_token', $value);
        return $this;
    }

    public function setPhone($value)
    {
        $exist = UserModel::where("phone", $value)->where("id", "!=", $this->model->id)->first();
        if ($exist){
            throw new ServerExp("手机号已被使用");
        }
        $this->setFields('phone', $value);
        return $this;
    }

    public function setPush($value)
    {
        $this->setFields('push', $value);
        return $this;
    }

    public function setAvatar($value)
    {
        $this->setFields('avatar', $value);
        return $this;
    }

    public function setGender($value)
    {
        $this->setFields('gender', $value);
        return $this;
    }

    public function setSummary($summary) {
        $this->setFields("summary", $summary ? : "");
        return $this;
    }

    public function setStatus($value)
    {
        $this->setFields('status', $value);
        return $this;
    }

    public function setRealname($value)
    {
        $this->setFields('realname', $value);
        return $this;
    }

    public function setNickname($value)
    {
        $this->setFields('nickname', $value);
        return $this;
    }

    public function setProvince($value)
    {
        $this->setFields('province', $value ? : "");
        return $this;
    }

    public function setCity($value)
    {
        $this->setFields('city', $value ? : "");
        return $this;
    }

    public function setCounty($value)
    {
        $this->setFields('county', $value ? : "");
        return $this;
    }

    public function setBirthday($value)
    {
        $this->setFields('birthday', $value);
        return $this;
    }

    public function setIsVip($value)
    {
        $this->setFields('is_vip', $value);
        return $this;
    }

    public function setisVipAutoRenew($value){
        $this->setFields("is_vip_auto_renew", $value);
        return $this;
    }

    public function setVipAutoRenewMode($value){
        $this->setFields("vip_auto_renew_mode", $value);
        return $this;
    }

    public function setVipMode($vipMode){
        $this->setFields("vip_mode", $vipMode);
        return $this;
    }

    public function setVipOvertime($time){
        $this->setFields("vip_overtime", $time);
        return $this;
    }

    public function setIsFactory($value)
    {
        $this->setFields('is_factory', $value);
        return $this;
    }

    public function setLastLoginTime($value){
        $this->setFields('last_login_time', $value);
        return $this;
    }

    public function saved() {
        if ($this->isCreate()){
            if($this->model->type == UserModel::typeUser){
                // 注册统计
                GeneralLogService::run(GeneralLogModel::actionTypeRegisterNum, "", "", 1, null, $this->model->id, UserModel::class, $this->mode);
            }
        }
    }

    public function updateLastLoginTime($time = null){
        $this->setLastLoginTime($time ? : time());
        $this->model->save();

        return $this;
    }


    public static function checkPassword($userModel, $password)
    {
        if (!$userModel->password) {
            throw new ServerExp('用户未设置密码');
        }

        if ($password && Hash::check($password, $userModel->password)) {

            return true;
        }

        return false;
    }


    // 登陆
    public static function login(UserModelAbstract $userModel, $data,$scopes = [])
    {
        if ($userModel->status != UserModel::statusActive) {
            throw new ServerExp('您的账户已被冻结，登录失败');
        }

        $ip = array_get($data, 'ip');
        $client = array_get($data, 'client');
        $clientId = config('client.'.$client.'.client');
        if(!$clientId){
            throw new ServerExp('应用未提供服务，请稍后再试');
        }
        Passport::personalAccessClientId($clientId);

        // 调整token过期时间  一个月
        Passport::personalAccessTokensExpireIn(now()->addDays(30));

        $tokenResult = $userModel->createToken($client . ': Personal Access Token App' , $scopes);
        $token = $tokenResult->accessToken;

        //同步登录日志
        self::syncUserLoginLog($userModel, $data);

        $user = new static($userModel);
        $user->updateLastLoginTime();

        Passport::actingAs($userModel);
        return $token;
    }


    /**同步登录日志
     * @param $userModel 用户模型
     * @param $data [ip,  client]
     * @throws ServerExp
     */
    public static function syncUserLoginLog($userModel, $data)
    {

        $ip = array_get($data, "ip");
        if (!$ip) {
            throw new ServerExp("缺少参数IP");
        }


        $client = array_get($data, "client");
        if (!$client) {
            throw new ServerExp("缺少参数client");
        }


        //同步登录日志登录日志；
        if ($client == BaseController::clientApp) {
            $list['user'] = $userModel;
            $list['ip'] = $ip;
            $list['type'] = AppUserLoginLogModel::typeLogin;
            $list['client'] = $client;
            AppUserLoginLogService::create($list);
        }
    }


    public static function isUser($userModel)
    {
        if ($userModel->type == UserModel::typeUser) {

            return true;
        }

        return false;

    }


    public function isFactoryTrue()
    {
        $this->model->is_factory = UserModel::isFactoryTrue;
        $this->model->save();
        return $this;
    }

    public function isFactoryFalse()
    {
        $this->model->is_factory = UserModel::isFactoryFalse;
        $this->model->save();
        return $this;
    }

    public function statusActive()
    {
        $this->model->status = UserModel::statusActive;
        $this->model->save();
        return $this;
    }

    public function statusInactive()
    {
        $this->model->status = UserModel::statusInactive;
        $this->model->save();
        return $this;
    }

    public static function getPushId($userModel){
        return 'user_pushid_'.$userModel->id;
    }

    public static function getInviteCode($userModel){
        $userId = $userModel->id + 32768;
        $inviteCode = InvitationCodeHelper::createCode($userId);
        return $inviteCode;
    }

    public static function inviteCodeToUserId($inviteCode){
        $userId = InvitationCodeHelper::deCode($inviteCode);
        return $userId - 32768;
    }

    public static function getInviteCodeUrl($userModel){
        return "?invite_code=".self::getInviteCode($userModel);
    }


    /**
     * 同步邀请关系
     * @param $user
     * @return $this
     * @throws ServerExp
     */
    public function syncInviteFromUser($user){
        if (empty($user->inviteByRecord)){
            $service = new InviteRecordService();
            $service->setToUser($this->model);
            $service->setFromUser($user);
            $service->save();
        }
        return $this;
    }


    /**
     * 关注指定用户
     * @param $user
     */
    public function subUser($user){
        $this->model->subUsers()->syncWithoutDetaching($user->id);
        UserService::refreshFansCache($user);
        UserService::refreshSubUserCache($this->model);
    }

    /**
     * 取消关注指定用户
     * @param $user
     */
    public function unSubUser($user){
        $this->model->subUsers()->detach($user->id);
        UserService::refreshFansCache($user);
        UserService::refreshSubUserCache($this->model);
    }

    public static function refreshFansCache($user){
        UserCache::deleteData(UserCache::typeUserFansUserIds, $user->id);
    }

    public static function refreshSubUserCache($user){
        UserCache::deleteData(UserCache::typeUserSubUserIds, $user->id);
    }


    /**
     * 是否关注指定用户
     */
    public static function isSub($subUser, $user = null){
        if ($user){
            $subUserIds = UserData::getUserSubUserIds($user);
            if (in_array($subUser->id, $subUserIds)){

                return true;
            }
        }

        return false;
    }

    /**
     * 是否为粉丝
     */
    public static function isFans($fansUser, $user = null){
        if ($user && $fansUser){
            $fansUserIds = UserData::getUserFansUserIds($user);
            if (in_array($fansUser->id, $fansUserIds)){

                return true;
            }
        }

        return false;
    }

    public static function isVip($user){
        if ($user){
            if($user->is_vip == UserModel::isVipTrue){

                return true;
            }
        }

        return false;
    }

    public function addWalletBalance($balance , $save = 1){
        if ($this->isCreate()){
            throw new ServerExp("用户新增时不能使用");
        }
        if (!ValidateHelper::isPositive($balance)){
            throw new ServerExp('必须为正数');
        }
        if ($save){
            $this->addNum('wallet_balance', $balance);
            $this->forgetCache();
        }else{
            $this->model->wallet_balance += $balance;
        }

        return $this;
    }

    public function reduceWalletBalance($balance , $save = 1){
        if ($this->isCreate()){
            throw new ServerExp("用户新增时不能使用");
        }
        if (!ValidateHelper::isPositive($balance)){
            throw new ServerExp('必须为正数');
        }

        if(round($this->model->wallet_balance - $balance, 2) < 0){
            throw new ServerExp('钱包余额不足');
        }

        if ($save){
            $this->reduceNum('wallet_balance', $balance);
            $this->forgetCache();
        }else{
            $this->model->wallet_balance -= $balance;
        }
        return $this;
    }

    public function addIosWalletBalance($balance , $save = 1){
        if ($this->isCreate()){
            throw new ServerExp("用户新增时不能使用");
        }
        if (!ValidateHelper::isPositive($balance)){
            throw new ServerExp('必须为正数');
        }

        if ($save){
            $this->addNum('ios_wallet_balance', $balance);
            $this->forgetCache();
        }else{
            $this->model->ios_wallet_balance += $balance;
        }

        return $this;
    }

    public function reduceIosWalletBalance($balance , $save = 1){
        if ($this->isCreate()){
            throw new ServerExp("用户新增时不能使用");
        }
        if (!ValidateHelper::isPositive($balance)){
            throw new ServerExp('必须为正数');
        }
//        if(round($this->model->ios_wallet_balance - $balance, 2) < 0){
//            throw new ServerExp('钱包余额不足');
//        }

        if ($save){
            $this->reduceNum('ios_wallet_balance', $balance);
            $this->forgetCache();
        }else{
            $this->model->ios_wallet_balance += $balance;
        }

        return $this;
    }

    public function forgetCache(){
//        CacheForget::CacheForget($this->model);
        return $this;
    }

}
