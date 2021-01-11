<?php

namespace Modules\User\Models;

use Modules\Auth\Models\AppUserLoginLogModel;
use Modules\Zeqiao\Models\DoctorModel;
use Modules\Zeqiao\Models\LecturerModel;
use Modules\Zeqiao\Models\Traits\BelongsDoctobTrait;
use Modules\Zeqiao\Models\Traits\BelongsLecturerTrait;

class UserModel extends UserModelAbstract
{

    use BelongsDoctobTrait;
    use BelongsLecturerTrait;

    // 推送开关
    const Push = "push";
    const pushTrue = 1;
    const pushFalse = 0;

    //性别
    const Gender = "gender";
    const genderM = "m"; // 男
    const genderW = "w"; // 女
    const genderN = "n"; // 未知

    //是否是会员
    const isVipTrue = 1;
    const isVipFalse = 0;

    const isVipAutoRenewTrue = 1;
    const isVipAutoRenewFalse = 0;


    public function usersAttentions(){
        return $this->hasMany(\Modules\User\Models\UsersAttentionModel::class,"user_id","id");
    }

    public function appUserLoginLogs(){
        return $this->hasMany(AppUserLoginLogModel::class,"user_id","id");
    }

    public function pushMessages(){
        return $this->hasMany(\Modules\Message\Models\PushMessageModel::class,"user_id","id");
    }

    public function userMessages(){
        return $this->hasMany(\Modules\Message\Models\UserMessageModel::class,"user_id","id");
    }

    public function userSocial(){
        return $this->hasMany(UserSocialModel::class, 'user_id', 'id');
    }

    public function weixinUserSocial(){
        return $this->hasOne(UserSocialModel::class, 'user_id', 'id')->where('type', UserSocialModel::typeWeiXin);
    }

    public function weiboUserSocial(){
        return $this->hasOne(UserSocialModel::class, 'user_id', 'id')->where('type', UserSocialModel::typeWeibo);
    }

    public function qqUserSocial(){
        return $this->hasOne(UserSocialModel::class, 'user_id', 'id')->where('type', UserSocialModel::typeQq);
    }


    protected static function getGlobalScopesType() {
        return [self::typeUser];
    }


    public function contentComments(){
        return $this->hasMany(\Modules\Cms\Models\ContentCommentModel::class,"user_id","id");
    }


    /**
     * 粉丝用户
     */
    public function fansUsers(){
        return $this->belongsToMany(self::class, "user_fans", "user_id", "fans_user_id");
    }

    /**
     * 关注用户
     */
    public function subUsers(){
        return $this->belongsToMany(self::class, "user_fans", "fans_user_id", "user_id");
    }




    public function subLecturer(){
        return $this->belongsToMany(LecturerModel::class, "lecturer_id", "fans_user_id", "lecturer_id");
    }


    public function testActivityUserRecords(){
        return $this->hasMany(\Modules\TestActivity\Models\TestActivityUserRecordModel::class,"user_id","id");
    }


    public function testActivityUserRecord(){
        return $this->hasOne(\Modules\TestActivity\Models\TestActivityUserRecordModel::class,"user_id","id");
    }


    public function dynamics(){
        return $this->hasMany(\Modules\Dynamic\Models\DynamicModel::class,"user_id","id");
    }

    public function dynamicTimelines(){
        return $this->hasMany(\Modules\Dynamic\Models\DynamicTimelineModel::class,"user_id","id");
    }

    public function dynamicComments(){
        return $this->hasMany(\Modules\Dynamic\Models\DynamicCommentModel::class,"user_id","id");
    }

    public function coupons(){
        return $this->hasMany(\Modules\Coupon\Models\CouponModel::class,"user_id","id");
    }
}