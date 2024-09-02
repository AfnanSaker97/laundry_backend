<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Laundry;
class LaundrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
$laundries = [
    [
        'name_en' => 'Pesana Laundry',
        'name_ar' => 'غسيل الملابس بيسانا',
       'phone_number' => '0978678678567',
        'admin_id' => '1',
        'city' => 'Abu Dabi',
        'address_line_1' => '123 Main St',
        'lat' => '33.5138',
        'lng' => '36.2765'
    ],
    [
        'name_en' => 'Sunshine Laundry',
        'name_ar' => 'غسيل الملابس في صن شاين',
        'phone_number' => '0987654321',
        'city' => 'Dubi',
        'address_line_1' => '456 Elm St',
        'lat' => '33.8886',
        'lng' => '35.4955',
        'admin_id' => '2'
    ],
    [
        'name_en' => 'Quick Clean Laundry',
        'name_ar' => 'غسيل سريع التنظيف',
        'phone_number' => '0123456789',
        'city' => 'Amman',
        'address_line_1' => '789 Oak St',
        'lat' => '31.9539',
        'lng' => '35.9106',
         'admin_id' => '3'
    ],
    [
        'name_en' => 'Fresh Start Laundry',
        'name_ar' =>'بداية جديدة للغسيل',
        'phone_number' => '0234567890',
        'city' => 'Dubi',
        'address_line_1' => '101 Palm St',
        'lat' => '30.0444',
        'lng' => '31.2357',
        'admin_id' => '4'
    ]
];
          foreach ($laundries as $laundry) {
            Laundry::create($laundry);
        } 
    }
}
