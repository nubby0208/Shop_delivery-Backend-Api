<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDistanceToAppSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->tinyInteger('auto_assign')->after('notification_settings')->nullable()->default('0');
            $table->string('distance_unit')->after('auto_assign')->nullable()->comment('km, mile');
            $table->double('distance')->after('distance_unit')->nullable()->default('0');
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
