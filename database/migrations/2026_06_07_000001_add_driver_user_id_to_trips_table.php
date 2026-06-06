<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('driver_user_id')
                ->nullable()
                ->after('trip_date')
                ->constrained('users')
                ->nullOnDelete();
 
            $table->index(['driver_user_id', 'trip_date']);
        });
    }
 
    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['driver_user_id']);
            $table->dropIndex(['driver_user_id', 'trip_date']);
            $table->dropColumn('driver_user_id');
        });
    }
};
