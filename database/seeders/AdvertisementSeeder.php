<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Advertisement;
class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Advertisements = [
            ['name_ar' => 'خصم 50% على أول عملية غسيل', 'name_en' => '50% Off on Your First Wash', 'url_media' => 'https://laundry-backend.tecrek.com/public/Advertisement/1.jpeg'],
            ['name_ar' => 'توصيل مجاني لمدة شهر', 'name_en' => 'Free Delivery for a Month', 'url_media' => 'https://laundry-backend.tecrek.com/public/Advertisement/2.jpeg'],
            ['name_ar' => 'غسيل 5 قطع وواحدة مجانية', 'name_en' => 'Wash 5 Items, Get 1 Free', 'url_media' => 'https://laundry-backend.tecrek.com/public/Advertisement/3.jpeg'],
        ];

        foreach ($Advertisements as $Advertisement) {
            Advertisement::create($Advertisement);
        }
    }
    
}
