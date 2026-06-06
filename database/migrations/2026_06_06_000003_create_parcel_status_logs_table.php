<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parcel_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_receive_id')->constrained('order_receives')->cascadeOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parcel_status_logs');
    }
};
