<?php

namespace Modules\User\Http\Controllers\Api\Admin\V1;

use App\Exceptions\ServerExp;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\BaseResource;
use App\Models\BaseModel;
use App\Service\BaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Modules\User\Http\Resources\Admin\AdminUserResource;
use Modules\User\Models\AdminUserModel;
use Modules\User\Models\UserModel;
use Modules\User\Service\User\AdminUserService;
use Modules\User\Service\User\UserService;
use Swoole\Exception;

class AdminUserController extends BaseController
{

    protected $modelClass = AdminUserModel::class;  // 绑定的模型类

    protected $serviceClass = AdminUserService::class; // 绑定的服务类

    protected $resourceClass = AdminUserResource::class;//用戶返回結構

    public function showFilter($query) {
        $query->where("type",UserModel::typeAdmin);
    }

    public function indexFilter($query) {
        $query->where("type",UserModel::typeAdmin);
    }

    public function store(Request $request){
        $curUser = $request->user();

        $this->validate($request, [
            "account" => "required",
            "role_ids" => "required",
        ],[
            "account.required" => "请输入登录用户名",
            "role_ids.required" => "请选择角色",
        ]);

        $id = \Jinput::get("id");
        $model = null;
        if ($id){
            $model = AdminUserModel::where("id",$id)->where("type",UserModel::typeAdmin)->first();
            if (!$model){
                throw new ServerExp("没有该用户信息");
            }
        }

        \DB::beginTransaction();
        try{
            $service = new AdminUserService($model);
            $service->setAccount($request->account);
            if (!is_null($request->password)){
                $service->setPassword($request->password);
            }

            $status = \Jinput::get("status");
            if (!is_null($status)){
                if ($status){
                    $service->setStatus(AdminUserModel::statusActive);
                }else{
                    $service->setStatus(AdminUserModel::statusInactive);
                }
            }
            $service->save();
            $service->syncRoles($request->role_ids);

            \DB::commit();
        }catch (\Exception $exception){
            \DB::rollback();
            throw $exception;
        }

        return $this->response();
    }


    public function update(){
        $id = \Jinput::get("id");
        if (empty($id)){
            throw new ServerExp('缺少参数ID');
        }
        $model = AdminUserModel::where("id",$id)->first();
        if (!$model){
            throw new ServerExp("没有该用户信息");
        }

        $service = new AdminUserService($model);

        $status = \Jinput::get('status');
        if (!is_null($status)){
            if ($status){
                $service->setStatus(UserModel::statusActive);
            }else{
                $service->setStatus(UserModel::statusInactive);
            }
        }

        $service->save();

        return $this->response();
    }




}