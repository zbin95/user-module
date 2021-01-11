<?php

namespace Modules\User\Http\Controllers\Api\Admin\V1\Auth;


use Illuminate\Http\Request;
use Modules\Permission\Http\Controllers\BaseController;
use Modules\User\Http\Resources\Admin\AdminUserResource;

class UserController extends BaseController {

    public function index(Request $request) {
        $curUser = $this->getAdminUser();

        return $this->response(new AdminUserResource($curUser));
    }

}