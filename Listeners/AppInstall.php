<?php

namespace Modules\User\Listeners;

use App\Events\AppInstallEvent;
use Modules\User\Models\AdminUserModel;
use Modules\User\Service\User\AdminUserService;

class AppInstall {


    public function handle(AppInstallEvent $event){

        $user = AdminUserModel::where('account', 'admin')->first();
        if (empty($user)){
            $service = new AdminUserService();
            $service->setAccount('admin');
            $service->setPassword('123456');
            $service->save();
        }
    }

}