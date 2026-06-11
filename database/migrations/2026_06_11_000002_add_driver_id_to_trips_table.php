<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('driver_id')
                ->nullable()
                ->after('driver_user_id')
                ->constrained('drivers')
                ->nullOnDelete();

            $table->index(['driver_id', 'trip_date']);
        });
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropIndex(['driver_id', 'trip_date']);
            $table->dropColumn('driver_id');
        });
    }
};
