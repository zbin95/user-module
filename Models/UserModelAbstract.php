<?php

namespace Modules\User\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

abstract class UserModelAbstract extends Authenticatable {

    protected $table = 'users';


    use HasApiTokens;
    use SoftDeletes;
    use Notifiable;

    const Status = "status";
    const statusActive = 1; // 活动中
    const statusInactive = 0; // 禁用

    const typeUser = 'user'; //普通用户
    const typeAdmin = 'admin'; //后台管理员账号



    // 电话
    const Phone = "phone";

    //性别
    const Gender = "gender";
    const genderM = "m"; // 男
    const genderW = "w"; // 女
    const genderN = "n"; // 未知



    public static function getAllTypes(){
        $data = [
            self::typeAdmin,
            self::typeUser
        ];

        return $data;
    }

    /**
     * 通过手机号获取用户模型
     * @param $phone
     * @return mixed
     */
    public static function getModelByPhone($phone, $isNewst = 0){
        $model = static::where("phone", $phone)->first();
        return $model;
    }


    abstract protected static function getGlobalScopesType();

    protected static function booted()
    {
        static::addGlobalScope('type', function ($builder) {
            $scopes = static::getGlobalScopesType();
            if ($scopes !== null){
                $builder->whereIn('type', $scopes);
            }
        });
    }


}