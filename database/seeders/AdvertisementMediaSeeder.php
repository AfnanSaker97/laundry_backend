<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AdvertisementMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
         
            [
                'url_image' => 'https://laundry-backend.tecrek.com/public/Advertisement/3.jpeg',
                'advertisement_id' => 3,
            ],
            [
                'url_image' => 'https://laundry-backend.tecrek.com/public/Advertisement/1.jpeg',
                'advertisement_id' => 4, // Ensure this ID exists in the advertisements table
            ],
            [
                'url_image' => 'https://laundry-backend.tecrek.com/public/Advertisement/2.jpeg',
                'advertisement_id' => 5, // Ensure this ID exists in the advertisements table
            ],
            [
                'url_image' => 'https://laundry-backend.tecrek.com/public/Advertisement/3.jpeg',
                'advertisement_id' => 6,
            ],
        ];

        // Insert the data into the advertisement_media table
        DB::table('advertisement_media')->insert($data);
    
    }
}
