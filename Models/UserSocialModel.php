<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class UserSocialModel extends Model
{
    protected $table = "user_social";

    const typeWeiXin = "weixin";
    const typeQq = "qq";
    const typeWeibo = "weibo";
    const typeApple = "apple";

    //性别
    const Gender = "gender";
    const genderM = "m"; // 男
    const genderW = "w"; // 女
    const genderN = "n"; // 未知

    public static function getTypeText($type){
        $data = [
            self::typeWeiXin => '微信',
            self::typeQq => 'QQ',
            self::typeWeibo => '微博'
        ];

        return array_get($data, $type, '');
    }

    public function user(){
        return $this->belongsTo(UserModel::class,"user_id","id");
    }

    public function setResultJsonAttribute($value){
        $this->attributes['result_json'] = $value ? json_encode($value) : '[]';
        return $this;
    }

    public function getResultJsonAttribute($value){
        return $value ? json_decode($value, true) : [];
    }

}
