<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_receives', function (Blueprint $table) {
            $table->index('delivery_status');
            $table->index('payment_status');
            $table->index('parcel_price');
        });
    }

    public function down()
    {
        Schema::table('order_receives', function (Blueprint $table) {
            $table->dropIndex(['delivery_status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['parcel_price']);
        });
    }
};
