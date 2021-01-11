<?php


namespace Modules\User\Http\Controllers\Api\Common\V1;


use App\Exceptions\ServerExp;
use App\Helpers\CityHelper;
use App\Helpers\ValidateHelper;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Modules\Auth\Service\ValidCodeService;
use Modules\User\Http\Resources\Common\UserResource;
use Modules\User\Models\UserModel;
use Modules\User\Service\User\UserService;

class UserController extends BaseController
{

    protected $modelClass = UserModel::class;  // 绑定的模型类

    protected $serviceClass = UserService::class; // 绑定的服务类

    protected $resourceClass = UserResource::class;//用戶返回結構



    /**
     * 关注用户
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ServerExp
     */
    public function sub(){
        $userId = \Jinput::get("user_id");
        if (empty($userId)){
            throw new ServerExp("缺少参数ID");
        }

        $userModel = UserModel::find($userId);
        if (empty($userModel)){
            throw new ServerExp("用户不存在");
        }

        $curUser = $this->getUser();

        $service = new UserService($curUser);
        $service->subUser($userModel);


        return $this->response();
    }

    /**
     * 取消关注用户
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ServerExp
     */
    public function unSub(){
        $userId = \Jinput::get("user_id");
        if (empty($userId)){
            throw new ServerExp("缺少参数ID");
        }

        $userModel = UserModel::find($userId);
        if (empty($userModel)){
            throw new ServerExp("用户不存在");
        }

        $curUser = $this->getUser();

        $service = new UserService($curUser);
        $service->unSubUser($userModel);

        return $this->response();
    }

    /**
     * 用户的粉丝
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ServerExp
     */
    public function fansUsers(){
        $id = \Jinput::get("user_id");

        $user = UserModel::find($id);
        if (empty($user)){
            throw new ServerExp("用户不存在");
        }

        $limit = \Jinput::get("limit", 10);

        $fansUserModels = $user->fansUsers()->paginate($limit);

        $fansUsers = UserResource::collection($fansUserModels);

        $data = [
            "list" => $fansUsers,
            "total" => $fansUserModels->total()
        ];

        return $this->response($data);
    }

    /**
     * 用户关注的用户
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ServerExp
     */
    public function subUsers(){
        $id = \Jinput::get("user_id");

        $user = UserModel::find($id);
        if (empty($user)){
            throw new ServerExp("用户不存在");
        }

        $limit = \Jinput::get("limit", 10);

        $subUserModels = $user->subUsers()->paginate($limit);

        $subUsers = UserResource::collection($subUserModels);

        $data = [
            "list" => $subUsers,
            "total" => $subUserModels->total()
        ];

        return $this->response($data);
    }

}
