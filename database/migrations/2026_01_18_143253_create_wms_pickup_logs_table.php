<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWmsPickupLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_pickup_logs', function (Blueprint $table) {
            $table->id();
            $table->datetime('pickup_time')->comment('Pickup time from request');
            $table->json('truck_plan')->comment('Full truck plan JSON data');
            $table->integer('total_orders')->comment('Total number of orders processed');
            $table->integer('updated_count')->default(0)->comment('Number of orders successfully updated');
            $table->integer('failed_count')->default(0)->comment('Number of orders failed');
            $table->text('failed_orders')->nullable()->comment('JSON list of failed order details');
            $table->text('request_data')->nullable()->comment('Full request data');
            $table->string('status')->default('success')->comment('success or failed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wms_pickup_logs');
    }
}
