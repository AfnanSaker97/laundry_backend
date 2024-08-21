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
    // افترض أن لديك من 1 إلى 10 في جدول `laundries`
    $laundryIds = DB::table('laundries')->pluck('id')->toArray();
 // استرجاع معرّفات السائقين الذين لديهم user_type_id = 3
 $driverIds = DB::table('users')->where('user_type_id', 3)->pluck('id')->toArray();

        for ($i = 0; $i < 4; $i++) { // عدد السجلات التي تريد إنشائها
            DB::table('cars')->insert([
                'driver_id' => $faker->randomElement($driverIds), // اختيار عشوائي من معرّفات السائقين
                'driver_phone' => $faker->phoneNumber,
                'status' => $faker->boolean,
                'lat' => $faker->latitude,
                'lng' => $faker->longitude,
                'laundry_id' => $faker->randomElement($laundryIds), // اختيار عشوائي من معرّفات المغاسل
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
}
