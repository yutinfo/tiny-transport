<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_receive_id')->constrained('order_receives')->cascadeOnDelete();
            $table->string('parcel_code')->nullable()->index();
            $table->string('delivery_status')->default('waiting')->index();
            $table->string('payment_status')->default('waiting')->index();
            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->decimal('collected_amount', 12, 2)->default(0);
            $table->string('failed_reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->unique(['trip_id', 'order_receive_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_items');
    }
};
