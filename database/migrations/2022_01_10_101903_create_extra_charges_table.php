<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtraChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extra_charges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('charges_type')->comment('fixed, percentage');
            $table->double('charges')->nullable()->default('0');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->tinyInteger('status')->default('1')->comment('0-inactive , 1 - active');
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
        Schema::dropIfExists('extra_charges');
    }
}
