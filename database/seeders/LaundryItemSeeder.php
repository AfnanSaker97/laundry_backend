<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LaundryItem;


class LaundryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $LaundryItems = [
            [ 'item_type_en' => 'Men\'s Abaya', 'item_type_ar' => ' عباية رجالي ', 'url_image'=> 'https://laundry-backend.tecrek.com/public/LaundryItem/IMG_2775.WEBP'],
            [ 'item_type_en' => 'Women\'s Abaya', 'item_type_ar' => 'عباية نسواني', 'url_image'=>'https://laundry-backend.tecrek.com/public/LaundryItem/IMG_2776.WEBP'],
         
           ];
          foreach ($LaundryItems as $LaundryItem) {
            LaundryItem::create($LaundryItem);
        }
    }
}
