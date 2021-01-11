<?php

namespace Modules\User\Http\Resources\Admin;

use App\Helpers\FileHelper;
use App\Http\Resources\BaseResource;
use App\Result\PublicResult;
use Modules\Permission\Http\Resources\RoleResource;
use Modules\User\Http\Resources\Base\UserBaseResource;
use Modules\User\Service\User\AdminUserService;
use Spatie\Permission\Models\Permission;

class AdminUserResource extends BaseResource
{
    public function toArray($request){
        if (AdminUserService::isSuperAdmin($this->resource)){
            $permission = Permission::all()->pluck('name')->toArray();
        }else{
            $permission = $this->getAllPermissions()->pluck('name')->toArray();
        }

        $data = [
            "id" => $this->id,
            "account" => $this->account,
            "type" => $this->type,
            "status" => $this->status ? 1 : 0,
            "avatar" => $this->avatar,
            "avatar_url" => $this->avatar ? PublicResult::image(FileHelper::getFile($this->avatar, FileHelper::$nameSpaceAvatar)) : null,
            "roles" => RoleResource::collection($this->roles),
            "role_ids" => $this->roles->pluck('id')->toArray(),
            "permissions" =>$permission
        ];

        return $data;
    }
}