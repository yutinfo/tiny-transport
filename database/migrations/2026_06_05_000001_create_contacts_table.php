<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['sender', 'receiver', 'both'])->default('receiver');
            $table->string('name', 100);
            $table->string('mobile', 15);
            $table->text('address')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('amphure_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('province_name', 100)->nullable();
            $table->string('amphure_name', 100)->nullable();
            $table->string('district_name', 100)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->unique(['type', 'mobile']);
            $table->index('mobile');
            $table->index('province_id');
            $table->index('amphure_id');
            $table->index('district_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contacts');
    }
};
