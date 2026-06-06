<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('trip_date')->index();
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_mobile', 15)->nullable();
            $table->string('car_id')->nullable();
            $table->string('area_name', 100)->nullable();
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('total_parcels')->default(0);
            $table->decimal('total_cod_amount', 12, 2)->default(0);
            $table->decimal('collected_amount', 12, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trips');
    }
};
