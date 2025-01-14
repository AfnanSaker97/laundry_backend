<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Laundry;
use App\Models\AddressLaundry;
class LaundrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laundry_addresses = [
            [ 
                'city' => 'Abu Dabi',
                'address_line_1' => '123 Main St',
                'lat' => 33.5138,
                'lng' => 36.2765,
                'laundry_id' => 1,      
            ],
            [
                'city' => 'Dubi', 
                'address_line_1' => '456 Elm St', 
                'lat' => 33.8886, 
                'lng' => 35.4955,
                'laundry_id' => 1,
            ],
            [
                'city' => 'Amman',
                'address_line_1' => '789 Oak St',
                'lat' => 31.9539,
                'lng' => 35.9106,
                'laundry_id' => 2,
            ],
            [
                'city' => 'Dubi',
                'address_line_1' => '101 Palm St',
                'lat' => 30.0444,
                'lng' => 31.2357,
                'laundry_id' => 3,
            ],
        ];
        
        foreach ($laundry_addresses as $address) {
            AddressLaundry::create($address); // تأكد من استخدام النموذج الصحيح هنا
        }

     /*   $laundries = [
            [
                'name_en' => 'Pesana Laundry',
                'name_ar' => 'غسيل الملابس بيسانا',
                'description_ar'=> 'خدمة غسيل ممتازة توفر لك الراحة والجودة في أبوظبي.',
                'description_en'=> 'Excellent laundry service providing you with comfort and quality in Abu Dhabi.',
                'phone_number' => '0978678678567',
                'admin_id' => '1',
                'city' => 'Abu Dabi',
                'address_line_1' => '123 Main St',
                'lat' => '33.5138',
                'lng' => '36.2765',
                'point' =>'50'
            ],
            [
                'name_en' => 'Sunshine Laundry',
                'name_ar' => 'غسيل الملابس في صن شاين',
                'description_ar'=> 'أفضل خدمات الغسيل في دبي لتلبية جميع احتياجاتك.',
                'description_en'=> 'Top-notch laundry services in Dubai to meet all your needs.',
                'phone_number' => '0987654321',
                'city' => 'Dubi',
                'address_line_1' => '456 Elm St',
                'lat' => '33.8886',
                'lng' => '35.4955',
                'admin_id' => '2',
                'point' =>'50'
            ],
            [
                'name_en' => 'Quick Clean Laundry',
                'name_ar' => 'غسيل سريع التنظيف',
                'description_ar'=> 'خدمات غسيل سريعة وفعالة في عمان.',
                'description_en'=> 'Quick and efficient laundry services in Amman.',
                'phone_number' => '0123456789',
                'city' => 'Amman',
                'address_line_1' => '789 Oak St',
                'lat' => '31.9539',
                'lng' => '35.9106',
                'admin_id' => '3',
                'point' =>'50'
            ],
            [
                'name_en' => 'Fresh Start Laundry',
                'name_ar' =>'بداية جديدة للغسيل',
                'description_ar'=> 'ابدأ يومك بنظافة مع خدمات الغسيل في دبي.',
                'description_en'=> 'Start your day fresh with laundry services in Dubai.',
                'phone_number' => '0234567890',
                'city' => 'Dubi',
                'address_line_1' => '101 Palm St',
                'lat' => '30.0444',
                'lng' => '31.2357',
                'admin_id' => '4',
                'point' =>'50'
            ]
        ];
        
          foreach ($laundries as $laundry) {
            Laundry::create($laundry);
        } */

    }
}
