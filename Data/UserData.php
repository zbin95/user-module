<?php

namespace Modules\User\Data;

use Modules\User\Cache\UserCache;
use Modules\User\Models\UserModel;

class UserData {



    public static function getUserSubUserIds($user){
        $ids = UserCache::getData(UserCache::typeUserSubUserIds, $user->id);
        if ($ids === false){
            $ids = $user->subUsers()->pluck("user_id")->toArray();

            UserCache::setData(UserCache::typeUserSubUserIds, $user->id, $ids);
        }

        return $ids;
    }

    public static function getUserFansUserIds($user){
        $ids = UserCache::getData(UserCache::typeUserFansUserIds, $user->id);
        if ($ids === false){
            $ids = $user->fansUsers()->pluck("user_id")->toArray();

            UserCache::setData(UserCache::typeUserFansUserIds, $user->id, $ids);
        }

        return $ids;
    }



}