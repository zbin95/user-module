<?php

namespace Modules\User\Cache;

use App\Cache\BaseCache;
use App\Cache\Traits\DataTarits;

class UserCache extends BaseCache {

    use DataTarits;

    const typeUserFansUserIds = "USER_FANS_USER_IDS";
    const typeUserSubUserIds = "USER_SUB_USER_IDS";


}