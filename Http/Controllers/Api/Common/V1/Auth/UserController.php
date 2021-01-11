<?php

namespace Modules\User\Http\Controllers\Api\Common\V1\Auth;

use App\Exceptions\ServerExp;
use App\Helpers\CityHelper;
use App\Helpers\EnvHelper;
use App\Helpers\ValidateHelper;
use App\Http\Controllers\Api\BaseController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Socialite\Facades\Socialite;
use Modules\Auth\Service\ValidCodeService;
use Modules\User\Http\Resources\Common\UserResource;
use Modules\User\Models\UserModel;
use Modules\User\Models\UserSocialModel;
use Modules\User\Service\User\UserService;
use Modules\User\Service\User\UserSocialService;

class UserController extends BaseController {


    public function index(Request $request) {
        $curUser = $this->getUser();

        return $this->response(new UserResource($curUser, false));
    }

    public function update(){

        $curUser = $this->getUser();

        $service = new UserService($curUser);


        $nickname = \Jinput::get('nickname');
        if (!is_null($nickname)){
            if (!$nickname){
                throw new ServerExp('昵称不能为空');
            }
            $service->setNickname($nickname);
        }

        $realname = \Jinput::get('realname');
        if (!is_null($realname)){
            if (!$realname){
                throw new ServerExp('姓名不能为空');
            }
            $service->setRealname($realname);
        }

        $gender = \Jinput::get('gender');
        if (!is_null($gender)){
            if ($gender == UserModel::genderM){
                $service->setGender(UserModel::genderM);
            }elseif($gender == UserModel::genderW){
                $service->setGender(UserModel::genderW);
            }else{
                $service->setGender(UserModel::genderN);
            }
        }

        $summary = \Jinput::get("summary");
        if(!is_null($summary)){
            $service->setSummary($summary);
        }

        $province = \Jinput::get('province');
        $city = \Jinput::get('city');
        $county = \Jinput::get('county');

        if (!is_null($province) || !is_null($city) || !is_null($county)){
            if (empty($province)){
                throw new ServerExp('地址参数不正确');
            }

            if (!CityHelper::verifyAddress($province, $city, $county)){
                throw new ServerExp('地址参数不正确');
            }
            $service->setProvince($province);
            $service->setCity($city);
            $service->setCounty($county);
        }

        $birthday = \Jinput::get('birthday');
        if (!is_null($birthday)){
            if ($birthday){
                $service->setBirthday($birthday);
            }else{
                $service->setBirthday('');
            }
        }


        $avatar = \Jinput::get('avatar');
        if (!is_null($avatar)){
            if ($avatar){
                $service->setAvatar($avatar);
            }else{
                $service->setAvatar('');
            }
        }

        $push = \Jinput::get('push');
        if (!is_null($push)){
            if (!is_null($push)){
                $service->setPush(UserModel::pushTrue);
            }else{
                $service->setPush(UserModel::pushFalse);
            }
        }


        $verifyCode = \Jinput::get('verify_code');

        $phone = \Jinput::get('phone');
        if($phone){


            if (empty($verifyCode)){
                throw new ServerExp('缺少参数验证码');
            }
            $code = ValidCodeService::getCode(ValidCodeService::validTypeBindAccount,$phone);
            if ($code != $verifyCode){
                throw new ServerExp('新手机号验证码错误');
            }

            $exsitUser = UserModel::where('phone', $phone)->first();
            if ($exsitUser){
                throw new ServerExp('手机号已被使用');
            }

            $service->setPhone($phone);
//            $service->setAccount($phone);

            ValidCodeService::deleteCode(ValidCodeService::validTypeBindAccount,$phone);
        }

        $push = \Jinput::get('push');
        if (!is_null($push)){
            if ($push){
                $service->setPush(UserModel::pushTrue);
            }else{
                $service->setPush(UserModel::pushFalse);
            }
        }


        $service->save();

        return $this->response();
    }


    public function changePassword(){
        $curUser = $this->getUser();

        $verifyCode = \Jinput::get("verify_code");
        if (empty($verifyCode)){
            throw new ServerExp("缺少参数验证码");
        }

        $password = \Jinput::get("password");
        if (empty($password)){
            throw new ServerExp("请输入密码");
        }


        $way = \Jinput::get('way');
        switch ($way) {
            case 'email':
                $account = $curUser->email;
                $code = ValidCodeService::getCode(ValidCodeService::vaildTypeChangePwd, $curUser->email);
                break;
            case 'phone':
                $account = $curUser->phone;
                $code = ValidCodeService::getCode(ValidCodeService::vaildTypeChangePwd, $curUser->phone);
                break;
            default:
                throw new ServerExp('不支持的方式');
        }

        if ($code != $verifyCode){
            throw new ServerExp('验证码错误');
        }


        $service = new UserService($curUser);
        $service->setPassword($password);
        $service->save();
        ValidCodeService::deleteCode(ValidCodeService::vaildTypeChangePwd, $account);
        return $this->response();
    }


    public function bindAccount(){
        $type = \Jinput::get("type");
        $curUser = $this->getUser();
        switch ($type){
            case "phone":
                $oldAccount = $curUser->phone;
                if ($curUser->phone){
                    $changeVerifyCode = \Jinput::get("old_account_verify_code");
                    if (empty($changeVerifyCode)){
                        throw new ServerExp("缺少参数验证码");
                    }
                    $code = ValidCodeService::getCode(ValidCodeService::valideTypeChangeAccount, $oldAccount);
                    if ($code  != $changeVerifyCode){
                        throw new ServerExp("验证码不正确");
                    }
                }
                break;
            case "email":
                $oldAccount = $curUser->email;
                if ($curUser->email){
                    $changeVerifyCode = \Jinput::get("old_account_verify_code");
                    if (empty($changeVerifyCode)){
                        throw new ServerExp("缺少参数修改验证码");
                    }
                    $code = ValidCodeService::getCode(ValidCodeService::valideTypeChangeAccount, $oldAccount);
                    if ($code  != $changeVerifyCode){
                        throw new ServerExp("验证码不正确");
                    }
                }
                break;
            default:
                throw new ServerExp("不支持的方式");
        }
        $checkOldAccountVerifyCode = \Jinput::get("check_old_account_verify_code");
        if ($checkOldAccountVerifyCode){

            return $this->response();
        }



        $account = \Jinput::get("account");
        if (empty($account)){
            throw new ServerExp("缺少参数账户名");
        }

        $curUser = $this->getUser();


        $verifyCode = \Jinput::get("verify_code");
        if (empty($verifyCode)){
            throw new ServerExp("缺少参数验证码");
        }



        $service = new UserService($curUser);
        switch ($type){
            case "phone":
                $service->setPhone($account);
                break;
            case "email":
                $service->setEmail($account);
                break;
            default:
                throw new ServerExp("不支持的方式");
        }


        $code = ValidCodeService::getCode(ValidCodeService::validTypeBindAccount, $account);
        if ($code != $verifyCode){
            throw new ServerExp("验证码不正确");
        }

        $service->save();
        if ($oldAccount){
            ValidCodeService::deleteCode(ValidCodeService::valideTypeChangeAccount, $oldAccount);
        }

        ValidCodeService::deleteCode(ValidCodeService::validTypeBindAccount, $account);


        return $this->response();
    }



    //发送验证码接口
    public function sendVerifyCode() {
        $type = \Jinput::get('type');

        $way = \Jinput::get('way');
        $account = \Jinput::get('account');
        if (!$account) {
            throw new ServerExp('缺少参数账户名');
        }
        switch ($way) {
            case 'email':
                if (!ValidateHelper::isEmail($account)) {
                    throw new ServerExp('邮箱格式不正确');
                }
                break;
            case 'phone':
                if (!ValidateHelper::isPhone($account)) {
                    throw new ServerExp('手机号格式不正确');
                }
                break;
            default:
                throw new ServerExp('不支持的验证方式');
        }

        switch ($type) {
            case ValidCodeService::validTypeBindAccount :
                $code = $this->sendBindAccount();
                break;
            case ValidCodeService::validTypeChangePayPwd:
                $code = $this->sendChangePayPwd();
                break;
            case ValidCodeService::validTypeUnbindAccount:
                $code = $this->sendUnbindAccount();
                break;
            case ValidCodeService::vaildTypeChangePwd:
                $code = $this->sendChangePassword();
                break;
            case ValidCodeService::valideTypeChangeAccount:
                $code = $this->sendChangeAccount();
                break;
            case ValidCodeService::validTypeCancelAccount:
                $code = $this->sendCancleAccount();
                break;
            default:
                throw new ServerExp("发送失败");

        }

        return $this->response(EnvHelper::isDev() ? $code : null);
    }

    public function sendCancleAccount(){
        $curUser = $this->getUser();
        $type = \Jinput::get("way", "phone");
        $account = \Jinput::get("account");
        switch ($type){
            case "email":
                if (empty($curUser->email)){
                    throw new ServerExp("账户未绑定邮箱");
                }
                if (empty($account)){
                    $account = $curUser->email;
                }else{
                    if ($account != $curUser->email){
                        throw new ServerExp("邮箱错误");
                    }
                }
                $code = ValidCodeService::sendMessageByEmail(ValidCodeService::validTypeCancelAccount, $account);
                break;
            case "phone":
                if (empty($curUser->phone)){
                    throw new ServerExp("账户未绑定手机号");
                }
                if (empty($account)){
                    $account = $curUser->phone;
                }else{
                    if ($account != $curUser->phone){
                        throw new ServerExp("手机号错误");
                    }
                }
                $code = ValidCodeService::sendMessageByPhone(ValidCodeService::validTypeCancelAccount, $account);
                break;
            default:
                throw new ServerExp("不支持的类型");
        }

        return $code;
    }

    public function sendChangeAccount(){
        $curUser = $this->getUser();

//        $account = \Jinput::get("account");
        $type = \Jinput::get("way");
        switch ($type){
            case "email":
                $account = $curUser->email;
                if (empty($account)){
                    throw new ServerExp("账户未绑定邮箱");
                }
                $code = ValidCodeService::sendMessageByEmail(ValidCodeService::valideTypeChangeAccount, $account);
                break;
            case "phone":
                $account = $curUser->phone;
                if (empty($account)){
                    throw new ServerExp("账户未绑定手机号");
                }
                $code = ValidCodeService::sendMessageByPhone(ValidCodeService::valideTypeChangeAccount, $account);
                break;
            default:
                throw new ServerExp("不支持的类型");
        }



        return $code;
    }

    public function sendChangePassword(){
        $curUser = $this->getUser();

        $account = \Jinput::get("account");
        $way = \Jinput::get('way');
        switch ($way) {
            case 'email':
                if ($curUser->email != $account){
                    throw new ServerExp('手机号不正确');
                }
                $code = ValidCodeService::sendMessageByEmail(ValidCodeService::vaildTypeChangePwd, $account);
                break;
            case 'phone':
                if ($curUser->phone != $account){
                    throw new ServerExp('手机号不正确');
                }
                $code = ValidCodeService::sendMessageByPhone(ValidCodeService::vaildTypeChangePwd, $account);
                break;
            default:
                throw new ServerExp('不支持的方式');
        }


        return $code;
    }



    //发送绑定账号验证码
    private function sendBindAccount() {
        $account = \Jinput::get("account");
        $way = \Jinput::get('way');
        switch ($way) {
            case 'email':
                $model = UserModel::where('email', $account)->first();
                if ($model) {
                    throw new ServerExp("该邮箱已被使用");
                }
                $code = ValidCodeService::sendMessageByEmail(ValidCodeService::validTypeBindAccount, $account);
                break;
            case 'phone':
                $model = UserModel::getModelByPhone($account);
                if ($model) {
                    throw new ServerExp("该手机号已被注册");
                }
                $code = ValidCodeService::sendMessageByPhone(ValidCodeService::validTypeBindAccount, $account);
                break;
            default:
                throw new ServerExp('不支持的方式');
        }


        return $code;
    }




    /**注销账号
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ServerExp
     */
    public function destroy(Request $request){
        $curUser = $this->getUser();
        $verifyCode = \Jinput::get("verify_code");
        if (empty($verifyCode)){
            throw new ServerExp("缺少参数验证码");
        }
        $way = \Jinput::get("way", "phone");
        $account = \Jinput::get("account");

        switch ($way){
            case "phone":
                if (empty($curUser->phone)){
                    throw new ServerExp("账户未绑定手机号");
                }
                if (empty($account)){
                    $account = $curUser->phone;
                }else{
                    if ($account != $curUser->phone){
                        throw new ServerExp("手机号错误");
                    }
                }
                break;
            case "email":
                if (empty($curUser->email)){
                    throw new ServerExp("账户未绑定邮箱");
                }
                if (empty($account)){
                    $account = $curUser->email;
                }else{
                    if ($account != $curUser->email){
                        throw new ServerExp("邮箱错误");
                    }
                }
                break;
                throw new ServerExp("验证方式错误");
        }


        $code = ValidCodeService::getCode(ValidCodeService::validTypeCancelAccount, $account);
        if (empty($code) || $code != $verifyCode){
            throw new ServerExp("验证码错误");
        }

        $this->dbBeginTransaction();
        try{

            $caUser = $this->getUser();
            $service = new UserService($caUser);
            // 是否可注销

            if (UserService::isVip($caUser)){
                throw new ServerExp("账号会员未到期，无法注销", "400001");
            }

            if ($curUser->status == UserModel::statusInactive){
                throw new ServerExp("账号当前为冻结状态， 不可以注销", "400001");
            }

            $service->delete();

            $data = [
                "status" => 1,
                "message" => "注销成功"
            ];
            $this->dbCommit();
        }catch (\Exception $exception){
            $this->dbRollback();

            if ($exception instanceof ServerExp && $exception->getError() == "400001"){
                $data = [
                    "status" => 0,
                    "message" => "注销失败"
                ];
            }else{
                throw $exception;
            }


        }

        return $this->response($data);
    }


    /**
     * 当前用户关注的用户
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function subUsers(){
        $curUser = $this->getUser();

        $limit = \Jinput::get("limit");

        $userModels = $curUser->subUsers()->paginate($limit);

        $users = UserResource::collection($userModels);

        $data = [
            "list" => $users,
            "total" => $userModels->total()
        ];

        return $this->response($data);
    }

    /**
     * 当前用户的粉丝
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function fansUsers(){
        $curUser = $this->getUser();

        $limit = \Jinput::get("limit");
        $userModels = $curUser->fansUsers()->paginate($limit);

        $users = UserResource::collection($userModels);

        $data = [
            "list" => $users,
            "total" => $userModels->total()
        ];

        return $this->response($data);
    }




    public function socialiteBind(Request $request){

        $userModel = $this->getUser();

        $type = $request->type;
        if(empty($type)){
            throw new ServerExp("缺少参数type");
        }

        //先校验登录的第三方登录的类型是否正确
        if (!in_array($type,["weixin","qq"])){
            throw new ServerExp('暂不支持');
        }

        $clientType = $type.'_'.$this->client;

        $clientId = config("services.".$clientType.".client_id");
        $clientSecret = config("services.".$clientType.".client_secret");
        $redirectUrl = config("services.".$clientType.".redirect");

        $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl);


        $driver = Socialite::driver($type)->setConfig($config)->stateless()->setHttpClient(new Client(['verify'=> false]));

        try {
            switch ($type){
                case "qq":
                    $driver->withUnionId();
                    $authUser = $driver->userFromToken($request->code);
                    break;
                case "weibo":
                    $authUser = $driver->userFromToken($request->code);
                    break;
                default:
                    $response = $driver->getAccessTokenResponse($request->code);
                    $token = Arr::get($response, 'access_token');;
                    $authUser = $driver->userFromToken($token);
            }

        } catch (\Exception $e) {
            throw new ServerExp('获取用户信息错误，授权失败');
        }
        $socialUser = UserSocialModel::where('type', $type)->where("user_id",$userModel->id)->first();
        if ($socialUser){
            throw new ServerExp('该账号已经绑定'.UserSocialModel::getTypeText($type).'了');
        }

        switch($type){
            case "weixin":
                $unionId = $authUser->offsetExists('unionid') ? $authUser->offsetGet('unionid') : null;
                $nickname = $authUser->offsetGet('nickname');
                $avatar = $authUser->offsetGet('headimgurl');
                $sex = $authUser->offsetGet('sex');
                $gender = $sex == 1 ? UserSocialModel::genderM : ($sex == 2 ? UserSocialModel::genderW : UserSocialModel::genderN) ;
                if($unionId){
                    $socialUser = UserSocialModel::where('type', UserSocialModel::typeWeiXin)->where("unionid",$unionId)->first();
                    if ($socialUser && $socialUser->user){
                        throw new ServerExp('微信已被绑定');
                    }
                    $service = new UserSocialService($socialUser);
                    $service->setUnionId($unionId);
                    $service->setNickname($nickname);
                    $service->setAvatar($avatar);
                    $service->setGender($gender);
                    $service->setResultJson($authUser);
                    $service->setUser($userModel);
                    $service->setType(UserSocialModel::typeWeiXin);
                    $socialUser = $service->save();
                }else{
                    throw new ServerExp('微信授权失败');
                }
                break;
            case "qq":
                $unionId = $authUser->unionid ? $authUser->unionid : null;
                $nickname = $authUser->getNickname();
                $avatar = $authUser->getAvatar();
                $sex = $authUser->offsetExists('gender') ? $authUser->offsetGet('gender') : null;
                $gender = $sex == '男' ? UserSocialModel::genderM : ($sex == '女' ? UserSocialModel::genderW : UserSocialModel::genderN) ;
                if ($unionId){
                    $socialUser = UserSocialModel::where('type', UserSocialModel::typeQq)->where("unionid",$unionId)->first();
                    if ($socialUser && $socialUser->user){
                        throw new ServerExp('QQ已被绑定');
                    }
                    $service = new UserSocialService($socialUser);
                    $service->setUnionId($unionId);
                    $service->setNickname($nickname);
                    $service->setAvatar($avatar);
                    $service->setGender($gender);
                    $service->setResultJson($authUser);
                    $service->setUser($userModel);
                    $service->setType(UserSocialModel::typeQq);
                    $socialUser = $service->save();
                }else{
                    throw new ServerExp('QQ授权失败');
                }

                break;

            case "weibo":
                $unionId = $authUser->getId();
                $nickname = $authUser->getNickname();
                $avatar = $authUser->getAvatar();
                $sex = $authUser->offsetExists('gender') ? $authUser->offsetGet('gender') : null;
                $gender = $sex == 'm' ? UserSocialModel::genderM : ($sex == 'f' ? UserSocialModel::genderW : UserSocialModel::genderN) ;
                if ($unionId){
                    $socialUser = UserSocialModel::where('type', UserSocialModel::typeWeibo)->where("unionid",$unionId)->first();
                    if ($socialUser && $socialUser->user){
                        throw new ServerExp('微博已被绑定');
                    }
                    $service = new UserSocialService($socialUser);
                    $service->setUnionId($unionId);
                    $service->setNickname($nickname);
                    $service->setAvatar($avatar);
                    $service->setGender($gender);
                    $service->setResultJson($authUser);
                    $service->setType(UserSocialModel::typeWeibo);
                    $service->setUser($userModel);
                    $socialUser = $service->save();


                }else{
                    throw new ServerExp('微博授权失败');
                }

                break;
            default:
                throw new ServerExp("暂不支持的方式");
        }

        return $this->response();
    }

    public function socialiteUnbind(Request $request){
        $type = \Jinput::get("type");
        if (!$type){
            throw new ServerExp("缺少参数类型");
        }

        $caUser = $this->getUser();
        $model = null;
        if ($type == UserSocialModel::typeQq){
            $model = $caUser->qqUserSocial;
            if (!$model){
                throw new ServerExp("没有绑定qq");
            }
        } elseif ($type == UserSocialModel::typeWeiXin){
            $model = $caUser->weixinUserSocial;
            if (!$model){
                throw new ServerExp("没有绑定微信");
            }
        } elseif ($type == UserSocialModel::typeWeibo){
            $model = $caUser->weiboUserSocial;
            if (!$model){
                throw new ServerExp("没有绑定微博");
            }
        }

        $service = new UserSocialService($model);
        $service->delete();


        return $this->response();

    }

}