<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_costs');
    }
};
