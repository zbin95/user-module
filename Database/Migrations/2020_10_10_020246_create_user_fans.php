<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable("user_fans")) {
            Schema::create('user_fans', function (Blueprint $table) {//
                $table->bigIncrements('id');
                $table->integer("user_id");//用户ID
                $table->integer("fans_user_id")->default(0);//粉丝用户ID
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
        Schema::dropIfExists('');
    }
}
