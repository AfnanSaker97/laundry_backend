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
            [ 'item_type' => 'Abaya','price'=>'100','laundry_id'=> '1'],
        
            [ 'item_type' => 'Abaya','price'=>'100','laundry_id'=> '2'],
            [ 'item_type' => 'Abaya','price'=>'100','laundry_id'=> '3'],
            [ 'item_type' => 'Abaya','price'=>'100','laundry_id'=> '4'],
        
        
           ];
          foreach ($laundryPrices as $laundryPrice) {
            LaundryPrice::create($laundryPrice);
        }
    }
}
