<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtpVerifyOnPickupDelivery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->tinyInteger('otp_verify_on_pickup_delivery')->after('distance')->nullable()->default('1');
            $table->string('currency')->after('otp_verify_on_pickup_delivery')->nullable();
            $table->string('currency_code')->after('currency')->nullable();
            $table->string('currency_position')->after('currency_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_settings', function (Blueprint $table) {
            //
        });
    }
}
