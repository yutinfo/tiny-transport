<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_receives', function (Blueprint $table) {
            $table->decimal('parcel_price', 14, 2)->nullable()->after('parcel_pice');
        });

        // Backfill
        DB::table('order_receives')->whereNull('parcel_price')->update([
            'parcel_price' => DB::raw('parcel_pice')
        ]);
    }

    public function down()
    {
        Schema::table('order_receives', function (Blueprint $table) {
            $table->dropColumn('parcel_price');
        });
    }
};
