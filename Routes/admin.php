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


// 当前登录用户
// 当前登录用户信息
Route::get('admin/v1/auth/user', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\Auth\UserController::class, 'index'));

//用户列表
Route::get('admin/v1/user', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\UserController::class, 'index')); //
Route::get('admin/v1/user/detail', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\UserController::class, 'show')); //
//Route::post('admin/v1/user', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\UserController::class, 'store')); //
Route::post('admin/v1/user/update', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\UserController::class, 'update')); //
Route::delete('admin/v1/user', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\UserController::class, 'destroy'));



// 后台管理员
Route::get('admin/v1/admin', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\AdminUserController::class, 'index'));
// 管理员详情
Route::get('admin/v1/admin/detail', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\AdminUserController::class, 'show'));
// 添加管理员
Route::post('admin/v1/admin', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\AdminUserController::class, 'store'));
// 更新管理员
Route::post('admin/v1/admin/update', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\AdminUserController::class, 'update'));
// 删除管理员
Route::delete('admin/v1/admin', RouteHelper::getUrlFromAction(\Modules\User\Http\Controllers\Api\Admin\V1\AdminUserController::class, 'destroy'));
