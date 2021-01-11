<?php

namespace Modules\User\Http\Resources\Base;

use App\Helpers\CityHelper;
use App\Helpers\FileHelper;
use App\Helpers\StringHelper;
use App\Http\Resources\BaseResource;
use App\Result\UserResult;
use Modules\User\Models\UserModel;
use Modules\User\Service\User\UserService;

class UserBaseResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $age = null;
        if ($this->birthday){
            $age =  date("Y",time()) - date("Y",$this->birthday);
        }

        $data = [
            "id" => $this->id,
            "phone" => ($this->isCLientForAdministrator() || ($this->getUser() && $this->getUser()->id == $this->id)) ? $this->phone : StringHelper::processPhone($this->phone),
            "email" => ($this->isCLientForAdministrator() || ($this->getUser() && $this->getUser()->id == $this->id)) ? $this->email : StringHelper::processEmail($this->email),
            "showname" => UserResult::getShowname($this->resource, $this->isCLientForAdministrator() ? true : false),
            "account" => $this->account,
            "type" => $this->type,
            "status" => $this->status,

            /**
             * 个人信息
             */
            "nickname" => $this->nickname,
            "realname" => $this->realname,
            "avatar" => $this->avatar,
            "avatar_url" => $this->toImageResource($this->avatar,FileHelper::$nameSpaceAvatar),
            "gender" => $this->gender,
            "birthday" => $this->birthday,
            "age" => $age,
            "pushid" => UserService::getPushId($this->resource),
            "push" => $this->push == UserModel::pushTrue ? 1 : 0,
            "summary" => $this->summary,

            "province" => $this->province,
            "province_name" => CityHelper::getProvinces($this->province),
            "city" => $this->city,
            "city_name" => CityHelper::getCity($this->city),
            "county" => $this->county,
            "county_name" => CityHelper::getCounty($this->county),


            /**
             * 系统关联信息
             */
            "is_vip" => $this->is_vip ? 1:0,
            "vip_starttime"=> 0, //vip开始时间
            "vip_overtime"=> $this->vip_overtime,   //vip结束时间
            "vip_mode" => $this->vip_mode,
            "is_vip_auto_renew" => $this->is_vip_auto_renew,
            "has_pay_password" => $this->pay_password ? 1 :0,
            "has_password" => $this->password ? 1 :0,

            "invite_code" => UserService::getInviteCode($this->resource),
            "created_time" => strtotime($this->created_at),
            "update_time" => strtotime($this->updated_at),
            "is_bind_weixin" => $this->when($this->isResourceDetail(), function (){
                return $this->weixinUserSocial ? 1 : 0;
            }),
            "is_bind_weibo" => $this->when($this->isResourceDetail(), function (){
                return $this->weiboUserSocial ? 1 : 0;
            }),
            "is_bind_qq" => $this->when($this->isResourceDetail(), function (){
                return $this->qqUserSocial ? 1 : 0;
            }),
            "wallet_balance" => $this->wallet_balance,
            "ios_wallet_balance" => $this->ios_wallet_balance,
            "last_login_time" => $this->last_login_time ? : null,
        ];

        $filterData = $this->toArrayFilter();
        if ($filterData){
            $data = array_merge($data,$filterData);
        }

        return $data;
    }


    protected function toArrayFilter(){
        return [];
    }

}
