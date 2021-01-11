<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable("users")) {
            Schema::create('users', function (Blueprint $table) {//@SV:User\UserService@
                $table->bigIncrements('id');
                $table->string('account', 100)->nullable()->unique();//f
                $table->string('email', 50)->nullable()->unique();//f
                $table->string('type', 30)->comment('类型（user普通用户 admin管理员）');//f
                $table->string('password')->default('');//f
                $table->string('phone', 13)->default('')->comment("手机号");//f
                $table->string('avatar', 30)->default('')->comment("头像");//f
                $table->char('gender', 1)->default('')->comment("性别");//f
                $table->integer('status')->default(1)->comment("0解冻  1冻结  ");//f
                $table->string('realname', 100)->default('')->comment("昵称");//f
                $table->string('nickname', 100)->default('')->comment("昵称");//f
                $table->string('province', 6)->default('')->comment("省份");//f
                $table->string('city', 6)->default('')->comment("市");//f
                $table->string('county', 6)->default('')->comment("区");//f
                $table->string('summary')->default('')->comment("个人简介");//f
                $table->integer('is_vip')->default(0)->comment('是否是会员');//f
                $table->string('vip_mode')->default("")->comment('VIP类型');//f
                $table->integer('is_vip_auto_renew')->default(0)->comment('vip是否自动续费');//f
                $table->integer('vip_auto_renew_mode')->default(0)->comment('vip是否自动续费');//f
                $table->string('vip_overtime')->default("")->comment('vip结束日期');//f

                $table->integer("birthday")->default(0)->comment("生日");
                $table->integer("push")->default(0)->comment("是否推送");

                $table->string('nickname_initial',100)->default('#')->comment("昵称首字母");
                $table->decimal('wallet_balance')->default(0)->comment("钱包余额");
                $table->decimal('ios_wallet_balance')->default(0)->comment("IOS钱包余额");

                $table->string('last_login_time')->default('')->comment("上次登录时间");
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
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

    }
}
