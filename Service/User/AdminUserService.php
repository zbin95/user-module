<?php

namespace Modules\User\Service\User;

use App\Exceptions\ServerExp;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Modules\Auth\Models\AppUserLoginLogModel;
use Modules\Auth\Service\AppUserLoginLogService;
use Modules\User\Models\AdminUserModel;
use Modules\User\Models\UserModel;
use App\Service\BaseService;
use Spatie\Permission\Models\Role;

class AdminUserService extends UserService
{

    public static $modelClass = AdminUserModel::class;

    public function init($model) {
        parent::init($model);
        $model->type = AdminUserModel::typeAdmin;
        return $model; // TODO: Change the autogenerated stub
    }

    /**
     *同步角色
     */
    public function syncRoles($roleIds){
        if (!is_array($roleIds)){
            $roleIds = [$roleIds];
        }
        $roleModels = Role::whereIn("id",$roleIds)->get();
        if($roleModels->toArray()){
            $this->model->syncRoles($roleModels);
        }

        return $this;
    }

    /**
     * 删除用户角色之间的关系；
     */
    protected function deleteUserRoleRelation(){
        $roleIds = $this->model->roles->pluck("id")->toArray();
        $this->syncRoles($roleIds);
    }

    public function beforeDelete() {
        if ($this->model->account == "admin"){
            throw new ServerExp("超级管理员不能删除");
        }

        parent::beforeDelete(); // TODO: Change the autogenerated stub
    }


    public function deleted() {
        $this->deleteUserRoleRelation();
        return parent::deleted(); // TODO: Change the autogenerated stub
    }



    public static function isSuperAdmin($userModel){
        if ($userModel->type == UserModel::typeAdmin){
            if ($userModel->name == 'admin' || $userModel->hasRole(1)){
                return true;
            }
        }

        return false;
    }





}