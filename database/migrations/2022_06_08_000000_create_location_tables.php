<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationTables extends Migration
{
    public function up()
    {
        Schema::create('geographies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index();
            $table->string('name_th');
            $table->string('name_en');
            $table->unsignedBigInteger('geography_id')->index();
        });

        Schema::create('amphures', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index();
            $table->string('name_th');
            $table->string('name_en');
            $table->unsignedBigInteger('province_id')->index();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('zip_code')->index();
            $table->string('name_th');
            $table->string('name_en');
            $table->unsignedBigInteger('amphure_id')->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('amphures');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('geographies');
    }
}
