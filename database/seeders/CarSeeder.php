<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 4; $i++) { // عدد السجلات التي تريد إنشائها
            DB::table('cars')->insert([
                'driver_id' => $faker->uuid, // أو استخدم قيمة ثابتة للتجربة
                'driver_phone' => $faker->phoneNumber,
                'status' => $faker->boolean,
                'lat' => $faker->latitude,
                'lng' => $faker->longitude,
                'laundry_id' => $faker->numberBetween(1, 4), // افترض أن لديك من 1 إلى 10 في جدول `laundries`
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
}
