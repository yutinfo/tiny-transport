<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DriverSeeder extends Seeder
{
    /**
     * Seed three sample drivers: two with a linked login account, one without.
     */
    public function run()
    {
        // 1) Driver with a linked account — reuse the seeded "driver" user if present.
        $linkedUserA = User::updateOrCreate(
            ['username' => 'driver'],
            [
                'name' => 'สมชาย',
                'last_name' => 'ใจดี',
                'email' => 'driver@admin.com',
                'status' => 'active',
                'role_name' => User::ROLE_DRIVER,
                'username_verified_at' => now(),
                'password' => 'password',
                'remember_token' => Str::random(10),
            ]
        );

        Driver::updateOrCreate(
            ['mobile' => '0811111111'],
            [
                'code' => Driver::generateCode(),
                'name' => 'สมชาย',
                'last_name' => 'ใจดี',
                'license_plate' => '1กก-1111',
                'driver_license_no' => 'D1111111',
                'area_name' => 'กรุงเทพมหานคร',
                'note' => 'คนขับประจำโซนกลางเมือง',
                'status' => Driver::STATUS_ACTIVE,
                'user_id' => $linkedUserA->id,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]
        );

        // 2) Second driver with a freshly created linked account.
        $linkedUserB = User::updateOrCreate(
            ['username' => 'driver2'],
            [
                'name' => 'สมหญิง',
                'last_name' => 'รักงาน',
                'email' => 'driver2@admin.com',
                'status' => 'active',
                'role_name' => User::ROLE_DRIVER,
                'username_verified_at' => now(),
                'password' => 'password',
                'remember_token' => Str::random(10),
            ]
        );

        Driver::updateOrCreate(
            ['mobile' => '0822222222'],
            [
                'code' => Driver::generateCode(),
                'name' => 'สมหญิง',
                'last_name' => 'รักงาน',
                'license_plate' => '2ขข-2222',
                'driver_license_no' => 'D2222222',
                'area_name' => 'นนทบุรี',
                'note' => 'คนขับประจำโซนนนทบุรี-ปทุมธานี',
                'status' => Driver::STATUS_ACTIVE,
                'user_id' => $linkedUserB->id,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]
        );

        // 3) Driver without any login account (temporary / outsource).
        Driver::updateOrCreate(
            ['mobile' => '0833333333'],
            [
                'code' => Driver::generateCode(),
                'name' => 'อนุชา',
                'last_name' => 'พร้อมวิ่ง',
                'license_plate' => '3คค-3333',
                'driver_license_no' => null,
                'area_name' => 'สมุทรปราการ',
                'note' => 'คนขับรายวัน ไม่มีบัญชี portal',
                'status' => Driver::STATUS_ACTIVE,
                'user_id' => null,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]
        );
    }
}
