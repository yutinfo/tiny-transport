<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('mobile', 10);
            $table->string('license_plate', 20)->nullable();
            $table->string('driver_license_no', 20)->nullable();
            $table->string('area_name', 100)->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->unique('mobile');
        });
    }

    public function down()
    {
        Schema::dropIfExists('drivers');
    }
};
