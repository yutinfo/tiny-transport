<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
       // User::factory()->count(5)->create();
       $this->call([
           LocationSeeder::class,
           UserSeeder::class,
           DriverSeeder::class,
           SampleDataSeeder::class,
       ]);

    }
}
