<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('address')->nullable();
            $table->string('contact_number', 255)->nullable();
            $table->string('user_type', 255)->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('player_id')->nullable();
            $table->string('latitude', 255)->nullable();
            $table->string('longitude', 255)->nullable();
            $table->rememberToken();
            $table->timestamp('last_notification_seen')->nullable();
            $table->tinyInteger('status')->nullable()->default('1');
            $table->string('uid')->nullable();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
