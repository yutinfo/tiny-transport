<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100);
            $table->string('password');
            $table->string('name');
            $table->string('last_name', 100)->nullable();
            $table->string('email')->unique()->nullable();
            $table->enum('status', ['active', 'inactive', 'ban']);
            $table->string('role_name', 100)->nullable();
            $table->timestamp('username_verified_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
