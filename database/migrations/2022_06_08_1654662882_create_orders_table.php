<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {

        $table->id();
		$table->string('code');


        $table->string('customer_name', 100)->nullable();
        $table->string('customer_mobile', 10)->nullable();
        $table->text('customer_address')->nullable()->default('NULL');
        $table->string('province_name', 100)->nullable();
        $table->string('amphures_name', 100)->nullable();
        $table->string('district_name', 100)->nullable();
        $table->string('zip_code', 10)->nullable();
        $table->string('car_id')->nullable();
        $table->string('driver_name')->nullable();
        $table->string('driver_mobile')->nullable();
        $table->integer('parcel_amount')->default(0);
        $table->decimal('parcel_total', 14, 2)->default(0);
        $table->enum('order_status',['waiting', 'success', 'fail', 'cancel'])->default('waiting');
        $table->string('created_by', 100)->nullable();
        $table->string('updated_by', 100)->nullable();
        $table->timestamps();


        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
