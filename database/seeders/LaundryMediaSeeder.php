<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LaundryMedia;

class LaundryMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $LaundriesMedia = [
            [ 'laundry_id' => '1', 'url_image' => 'https://laundry-backend.tecrek.com/public/Laundry/11.png'],
            [ 'laundry_id' => '1', 'url_image' => 'https://laundry-backend.tecrek.com/public/Laundry/10.png'],
            [ 'laundry_id' => '1', 'url_image' => 'https://laundry-backend.tecrek.com/public/Laundry/9.png'],
            [ 'laundry_id' => '2', 'url_image' => 'https://laundry-backend.tecrek.com/public/Laundry/8.png'],
            [ 'laundry_id' => '2', 'url_image' => 'https://laundry-backend.tecrek.com/public/Laundry/7.png'],
            [ 'laundry_id' => '2', 'url_image' => 'https://laundry-backend.tecrek.com/public/Laundry/6.png'],
          //  [ 'laundry_id' => '3', 'url_image' => '1'],
           // [ 'laundry_id' => '3', 'url_image' => '2'],
        ];

        foreach ($LaundriesMedia as $media) {
            LaundryMedia::create($media); // تمرير كل عنصر بشكل فردي
        }
    
    }
}
