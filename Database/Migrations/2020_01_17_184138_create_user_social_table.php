<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSocialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable("user_social")) {
            Schema::create('user_social', function (Blueprint $table) {//@SV:User\UserSocialService@
                $table->bigIncrements('id');
                $table->integer('user_id')->default(0);//@t:f@f
                $table->string('type');//f
                $table->string('nickname',100); // 昵称
                $table->string('gender',1); // 性别
                $table->string('avatar',200); // 头像
                $table->string('unionid',50);//f
                $table->string('result_json',2000);//f
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_social');
    }
}
