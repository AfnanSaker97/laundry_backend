<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
     
        $services = [
            [ 'name_en' => 'Washing', 'name_ar' => 'غسيل'],
            [ 'name_en' => 'Ironing', 'name_ar' => 'كوي'],
            [ 'name_en' => 'Drying', 'name_ar' => 'تجفيف'],
        ];

    foreach ($services as $service) {
        Service::create($service); // تمرير كل عنصر بشكل فردي
    }


    $laundryServices = [
        ['laundry_id' => 1, 'service_id' => 1],
        ['laundry_id' => 1, 'service_id' => 2],
        ['laundry_id' => 2, 'service_id' => 1],
        ['laundry_id' => 2, 'service_id' => 3],
        ['laundry_id' => 3, 'service_id' => 1],
        ['laundry_id' => 3, 'service_id' => 2],
        ['laundry_id' => 3, 'service_id' => 3],
        ['laundry_id' => 4, 'service_id' => 1],
        ['laundry_id' => 4, 'service_id' => 2],
        ['laundry_id' => 4, 'service_id' => 3],
      
    ];

    foreach ($laundryServices as $laundryService) {
        DB::table('laundry_service')->insert($laundryService);
    }
}
    
}
