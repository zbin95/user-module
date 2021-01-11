<?php

namespace Modules\User\Http\Controllers\Api\Admin\V1;

use App\Exceptions\ServerExp;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\BaseResource;
use App\Models\BaseModel;
use App\Service\BaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Models\UserModel;
use Modules\User\Service\User\UserService;

class UserController extends BaseController
{


    protected $modelClass = UserModel::class;  // 绑定的模型类

    protected $serviceClass = UserService::class; // 绑定的服务类

    protected $resourceClass = \Modules\User\Http\Resources\Common\UserResource::class;//用戶返回結構

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function indexFilter($query)
    {

        $query->with("appUserLoginLogs");
        $type = \Jinput::get("type");
        $query = $query->where("type",UserModel::typeUser);

        $keywords = \Jinput::get("keywords");
        if($keywords){
            $query->where(function ($q) use ($keywords){
                $q->where("realname","like","%".$keywords."%")->orWhere("phone","like","%".$keywords."%")->orWhere("email","like","%".$keywords."%")->orWhere("nickname","like","%".$keywords."%");
            });
        }

        $realName = \Jinput::get("realname");
        if ($realName){
            $query = $query->where("realname","like","%".$realName."%");
        }



        $id = \Jinput::get("id");
        if ($id){
            $query = $query->where("id",$id);
        }

        $phone = \Jinput::get("phone");
        if ($phone){
            $query = $query->where("phone","like","%".$phone."%");
        }

        $provinces = \Jinput::get("province",[]);
        if ($provinces){
            $query = $query->whereIn("province",$provinces);
        }

        $citys = \Jinput::get("city",[]);
        if ($citys){
            $query = $query->whereIn("city",$citys);
        }

        $countys = \Jinput::get("county",[]);
        if ($countys){
            $query = $query->whereIn("county",$countys);
        }

        $registerStartTime = \Jinput::get("register_start_time");
        if ($registerStartTime){
            $query = $query->where("created_at",">=",date("Y-m-d H:i:s",$registerStartTime));
        }

        $registerOverTime  = \Jinput::get("register_over_time");
        if ($registerOverTime ){
            $query = $query->where("created_at","<",date("Y-m-d H:i:s",strtotime("+1day",$registerOverTime) ));
        }

        $loginStartTime = \Jinput::get("login_start_time");
        if ($loginStartTime){
            $query = $query->whereHas("appUserLoginLogs",function ($q) use ($registerStartTime){
                $q = $q->where("created_at",">=",date("Y-m-d H:i:s",$registerStartTime));
            });
        }

        $loginOverTime = \Jinput::get("login_over_time");
        if ($loginOverTime){
            $query = $query->whereHas("appUserLoginLogs",function ($q) use ($loginOverTime){
                $q = $q->where("created_at","<",date("Y-m-d H:i:s",strtotime("+1day",$loginOverTime)));
            });
        }


        return parent::indexFilter($query) ;
    }



    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        exit();
        $model = null;
        $id = \Jinput::get("id");
        if ($id){
            $model = UserModel::find($id);
            if (!$model){
                throw new Exception("用户不存在");
            }
        }


        $realName = \Jinput::get("realName");
        if (!$realName){
            throw new ServerExp("缺少参数用户真实姓名");
        }



//        $role = \Jinput::get("role",[]);
//        if (!$role){
//            throw new ServerExp("缺少参数用户角色");
//        }

        $this->dbBeginTransaction();
        try {
            $service = new UserService($model);
            $service->setRealname($realName);
            $service->setType(UserModel::typeAdmin);
            $service->save();
            $this->dbCommit();;
        }catch (\Exception $exception){
            $this->dbRollback();
            throw  $exception;
        }
        return $this->response();

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function showFilter($query)
    {


        return parent::showFilter($query);    
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update()
    {
        $ids = \Jinput::get("id",[]);
        if (!$ids){
            throw new ServerExp("缺少参数id");
        }

        $status = \Jinput::get("status");

        $userModels = UserModel::whereIn("id",$ids)->get();

        $this->dbBeginTransaction();
        try {
            foreach ($userModels as $userModel){
                $service = new UserService($userModel);
                if (!is_null($status)){
                    if ($status){
                        $service->statusActive();
                    }else{
                        $service->statusInactive();
                    }
                }
            }



            $this->dbCommit();;
        }catch (\Exception $exception){
            $this->dbRollback();
            throw  $exception;
        }

        return $this->response();

    }


}
