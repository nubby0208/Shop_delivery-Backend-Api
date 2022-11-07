<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryManDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_man_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->tinyInteger('is_verified')->nullable()->default('0');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('delivery_man_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('delivery_man_documents');
    }
}
