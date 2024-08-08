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
        'name' => 'Pesana Laundry',

        'photo' => 'pesana_photo.jpg',
        'phone_number' => '0978678678567',

        'city' => 'Abu Dabi',
        'address_line_1' => '123 Main St',
        'lat' => '33.5138',
        'lng' => '36.2765'
    ],
    [
        'name' => 'Sunshine Laundry',
        'photo' => 'sunshine_photo.jpg',
        'phone_number' => '0987654321',
        'city' => 'Dubi',
        'address_line_1' => '456 Elm St',
        'lat' => '33.8886',
        'lng' => '35.4955'
    ],
    [
        'name' => 'Quick Clean Laundry',
        'photo' => 'quickclean_photo.jpg',
        'phone_number' => '0123456789',
        'city' => 'Amman',
        'address_line_1' => '789 Oak St',
        'lat' => '31.9539',
        'lng' => '35.9106'
    ],
    [
        'name' => 'Fresh Start Laundry',
        'photo' => 'freshstart_photo.jpg',
        'phone_number' => '0234567890',
        'city' => 'Dubi',
        'address_line_1' => '101 Palm St',
        'lat' => '30.0444',
        'lng' => '31.2357'
    ]
];
          foreach ($laundries as $laundry) {
            Laundry::create($laundry);
        } 
    }
}
