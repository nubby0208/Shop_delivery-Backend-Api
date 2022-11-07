<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->json('pickup_point')->nullable();
            $table->json('delivery_point')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('parcel_type')->nullable();
            $table->double('total_weight')->nullable()->default('0');
            $table->double('total_distance')->nullable()->default('0');
            $table->dateTime('date')->nullable();
            $table->dateTime('pickup_datetime')->nullable();
            $table->dateTime('delivery_datetime')->nullable();
            $table->unsignedBigInteger('parent_order_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_collect_from')->nullable()->comment('on_pickup, on_delivery');
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->double('fixed_charges')->nullable()->default('0');
            $table->double('weight_charge')->nullable()->default('0');
            $table->double('distance_charge')->nullable()->default('0');
            $table->json('extra_charges')->nullable();
            $table->double('total_amount')->nullable()->default('0');
            $table->tinyInteger('pickup_confirm_by_client')->nullable()->default('0')->comment('0-not confirm , 1 - confirm');
            $table->tinyInteger('pickup_confirm_by_delivery_man')->nullable()->default('0')->comment('0-not confirm , 1 - confirm');          
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('orders');
    }
}
