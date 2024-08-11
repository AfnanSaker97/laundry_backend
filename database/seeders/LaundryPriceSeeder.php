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
            [ 'item_type_en' => 'Abaya', 'item_type_ar' => 'عباية','price'=>'100','laundry_id'=> '1'],
        
            [ 'item_type_en' => 'Abaya', 'item_type_ar' => 'عباية','price'=>'90','laundry_id'=> '2'],
            [ 'item_type_en' => 'Abaya', 'item_type_ar' => 'عباية','price'=>'95','laundry_id'=> '3'],
            [ 'item_type_en' => 'Abaya', 'item_type_ar' => 'عباية','price'=>'99','laundry_id'=> '4'],
        
        
           ];
          foreach ($laundryPrices as $laundryPrice) {
            LaundryPrice::create($laundryPrice);
        }
    }
}
