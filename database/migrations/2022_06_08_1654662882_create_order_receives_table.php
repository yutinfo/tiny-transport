<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderReceivesTable extends Migration
{
    public function up()
    {
        Schema::create('order_receives', function (Blueprint $table) {

        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->text('parcel_code')->nullable();
		$table->text('parcel_description')->nullable();
        $table->string('receive_name', 100)->nullable();
        $table->string('receive_mobile', 15)->nullable();
        $table->text('receive_address')->nullable();
        $table->unsignedBigInteger('province_id')->nullable();
        $table->unsignedBigInteger('amphures_id')->nullable();
        $table->unsignedBigInteger('district_id')->nullable();
        $table->string('province_name', 100)->nullable();
        $table->string('amphures_name', 100)->nullable();
        $table->string('district_name', 100)->nullable();
        $table->string('zip_code', 10)->nullable();
        $table->enum('parcel_pickup_type',['pickup', 'delivery'])->default('delivery');
        $table->enum('payment_type',['immediately','on_delivery'])->default('immediately');


        $table->enum('delivery_status',['waiting','received','delivering','delivered'])->default('waiting');
        $table->enum('payment_status',['waiting', 'success', 'fail', 'cancel'])->default('waiting');

        $table->decimal('parcel_pice', 14, 2)->default(0);
        $table->string('created_by', 100)->nullable();
        $table->string('updated_by', 100)->nullable();
        $table->timestamps();

        $table->index('order_id');
        $table->index('province_id');
        $table->index('amphures_id');
        $table->index('district_id');


        });
    }

    public function down()
    {
        Schema::dropIfExists('order_receives');
    }
}
