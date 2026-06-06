<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parcel_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_receive_id')->constrained('order_receives')->cascadeOnDelete();
            $table->string('channel')->index(); // sms, line, email, manual
            $table->string('recipient');
            $table->text('message');
            $table->string('status')->default('pending')->index(); // pending, sent, failed, skipped
            $table->text('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parcel_notifications');
    }
};
