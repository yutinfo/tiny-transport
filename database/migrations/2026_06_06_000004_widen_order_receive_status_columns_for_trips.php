<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE order_receives MODIFY delivery_status VARCHAR(255) NOT NULL DEFAULT 'waiting'");
            DB::statement("ALTER TABLE order_receives MODIFY payment_status VARCHAR(255) NOT NULL DEFAULT 'waiting'");

            return;
        }

        Schema::table('order_receives', function (Blueprint $table) {
            $table->string('delivery_status')->default('waiting')->index()->change();
            $table->string('payment_status')->default('waiting')->index()->change();
        });
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE order_receives MODIFY delivery_status ENUM('waiting', 'received', 'delivering', 'delivered') NOT NULL DEFAULT 'waiting'");
            DB::statement("ALTER TABLE order_receives MODIFY payment_status ENUM('waiting', 'success', 'fail', 'cancel') NOT NULL DEFAULT 'waiting'");

            return;
        }

        Schema::table('order_receives', function (Blueprint $table) {
            $table->enum('delivery_status', ['waiting', 'received', 'delivering', 'delivered'])->default('waiting')->change();
            $table->enum('payment_status', ['waiting', 'success', 'fail', 'cancel'])->default('waiting')->change();
        });
    }
};
