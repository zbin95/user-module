<?php

namespace Modules\User\Service\User;

use App\Service\BaseService;
use Modules\User\Models\UserFansModel;

class UserFansService extends BaseService {

    public static $modelClass = UserFansModel::class;

    public function setUser($user){
        $this->setForeignKey("user_id", "user", $user);
        return $this;
    }

    public function setFansUser($fansUser){
        $this->setForeignKey("user_id", "fansUser", $fansUser);
        return $this;
    }

}