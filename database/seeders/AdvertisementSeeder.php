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
            [
                'name_ar' => 'خصم 50% على أول عملية غسيل', 
                'name_en' => '50% Off on Your First Wash', 
             //   'url_media' => 'https://laundry-backend.tecrek.com/public/Advertisement/1.jpeg',
                'points' => 30,
                'end_date' => now()->addDays(30), // تحديد تاريخ الانتهاء (مثلاً، بعد 30 يوماً)
                'laundry_id' => 1 // تعيين معرف المغسلة المناسب
            ],
            [
                'name_ar' => 'توصيل مجاني لمدة شهر', 
                'name_en' => 'Free Delivery for a Month', 
               // 'url_media' => 'https://laundry-backend.tecrek.com/public/Advertisement/2.jpeg',
                'points' => 40,
                'end_date' => now()->addDays(30), // تاريخ انتهاء
                'laundry_id' => 1 // تعيين معرف المغسلة المناسب
            ],
            [
                'name_ar' => 'غسيل 5 قطع وواحدة مجانية', 
                'name_en' => 'Wash 5 Items, Get 1 Free', 
              //  'url_media' => 'https://laundry-backend.tecrek.com/public/Advertisement/3.jpeg',
                'points' => 50,
                'end_date' => now()->addDays(30), // تاريخ انتهاء
                'laundry_id' => 1 // تعيين معرف المغسلة المناسب
            ],
        ];
        foreach ($Advertisements as $Advertisement) {
            Advertisement::create($Advertisement);
        }
    }
    
}
