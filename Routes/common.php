<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\RouteHelper;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//app端

// 当前登录用户
Route::get('common/v1/auth/user', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, 'index'))->middleware("client:common"); // 当前登录用户个人信息
Route::delete('common/v1/auth/user', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, 'destroy'))->middleware("client:common"); // 注销当前用户账户
Route::post('common/v1/auth/user/update', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, 'update'))->middleware("client:common"); // 更新当前用户

Route::post('common/v1/auth/user/send-verify-code', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, 'sendVerifyCode'))->middleware("client:common"); // 发送验证码

Route::post('common/v1/auth/user/change-password', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, 'changePassword'))->middleware("client:common"); // 修改密码

Route::post('common/v1/auth/user/bind-account', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, 'bindAccount'))->middleware("client:common"); // 绑定、修改账户










Route::get('common/v1/auth/user/fans-users', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, "fansUsers"))->middleware("client:common"); // 当前用户的粉丝
Route::get('common/v1/auth/user/sub-users', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, "subUsers"))->middleware("client:common"); // 当前用户关注的用户


// 用户
Route::get('common/v1/user/detail', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\UserController::class, "show")); // 指定用户详情
Route::get('common/v1/user/fans-users', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\UserController::class, "fansUsers")); // 用户的粉丝
Route::get('common/v1/user/sub-users', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\UserController::class, "subUsers")); // 用户关注的用户

Route::post('common/v1/user/sub', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\UserController::class, "sub"))->middleware("client:common"); // 关注某个用户
Route::post('common/v1/user/un-sub', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\UserController::class, "unSub"))->middleware("client:common"); // 取消关注某个用户



Route::post('common/v1/auth/user/socialite-unbind', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, 'socialiteUnbind'))->middleware(['client:common']); // 解绑定第三方
Route::post('common/v1/auth/user/socialite-bind', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Common\V1\Auth\UserController::class, 'socialiteBind'))->middleware(['client:common']); // 绑定第三方


//关注
//Route::get('common/v1/auth/user/attention', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Base\BaseUserAttentionController::class, 'index'))->middleware(['scope:common']);
//Route::post('common/v1/auth/user/attention', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Base\BaseUserAttentionController::class, 'store'))->middleware(['scope:common']);
//Route::delete('common/v1/auth/user/attention', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Base\BaseUserAttentionController::class, 'destroy'))->middleware(['scope:common']);




