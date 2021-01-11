<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Modules\Auth\Models\AppUserLoginLogModel;

class AdminUserModel extends UserModelAbstract
{

    use HasRoles;

    protected static function getGlobalScopesType() {
        return [self::typeAdmin];
    }
}