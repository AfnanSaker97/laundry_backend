<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LaundryPrice;
class LaundryPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laundryPrices = [
            [ 'item_type_en' => 'Men\'s Abaya', 'item_type_ar' => ' عباية رجالي ','price'=>'100','laundry_id'=> '1'],
            [ 'item_type_en' => 'Women\'s Abaya', 'item_type_ar' => 'عباية نسواني','price'=>'100','laundry_id'=> '1'],
            [ 'item_type_en' => 'Men\'s Abaya', 'item_type_ar' => 'عباية رجالي ','price'=>'90','laundry_id'=> '2'],
            [ 'item_type_en' => 'Women\'s Abaya', 'item_type_ar' => 'عباية نسواني','price'=>'90','laundry_id'=> '2'],
            [ 'item_type_en' => 'Men\'s Abaya', 'item_type_ar' => 'عباية رجالي ','price'=>'95','laundry_id'=> '3'],
            [ 'item_type_en' => 'Women\'s Abaya', 'item_type_ar' => 'عباية نسواني','price'=>'95','laundry_id'=> '3'],
            [ 'item_type_en' => 'Men\'s Abaya', 'item_type_ar' => 'عباية رجالي ','price'=>'99','laundry_id'=> '4'],
            [ 'item_type_en' => 'Women\'s Abaya', 'item_type_ar' => 'عباية نسواني','price'=>'99','laundry_id'=> '4'],
        
           ];
          foreach ($laundryPrices as $laundryPrice) {
            LaundryPrice::create($laundryPrice);
        }
    }
}
