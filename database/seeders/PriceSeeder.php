<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Price;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prices = [
           [ 'laundry_id' => '1', 'laundry_item_id' => '1', 'price' => 3 ,'order_type_id'=>'2','service_id'=>'2'],
            /*  [ 'laundry_id' => '1', 'laundry_item_id' => '1', 'price' => 4 ,'order_type_id'=>'1','service_id'=>'2'],
              [ 'laundry_id' => '1', 'laundry_item_id' => '1', 'price' => 6 ,'order_type_id'=>'2','service_id'=>'1'],
            [ 'laundry_id' => '1', 'laundry_item_id' => '1', 'price' => 12 ,'order_type_id'=>'2','service_id'=>'2'],
   
              [ 'laundry_id' => '1', 'laundry_item_id' => '2', 'price' => 4 ,'order_type_id'=>'1','service_id'=>'1'],
              [ 'laundry_id' => '1', 'laundry_item_id' => '2', 'price' => 3 ,'order_type_id'=>'1','service_id'=>'2'],
              [ 'laundry_id' => '1', 'laundry_item_id' => '2', 'price' => 8 ,'order_type_id'=>'2','service_id'=>'1'],
              [ 'laundry_id' => '1', 'laundry_item_id' => '2', 'price' => 10 ,'order_type_id'=>'2','service_id'=>'2'],
  */
       /*     [ 'laundry_id' => '2', 'laundry_item_id' => '1', 'price' => 6 ,'order_type_id'=>'2','service_id'=>'1'],
            [ 'laundry_id' => '2', 'laundry_item_id' => '1', 'price' => 3 ,'order_type_id'=>'2','service_id'=>'2'],

            [ 'laundry_id' => '2', 'laundry_item_id' => '1', 'price' => 12 ,'order_type_id'=>'1','service_id'=>'1'],
            [ 'laundry_id' => '2', 'laundry_item_id' => '1', 'price' => 4 ,'order_type_id'=>'1','service_id'=>'2']  

     [ 'laundry_id' => '2', 'laundry_item_id' => '2', 'price' => 13 ,'order_type_id'=>'2','service_id'=>'1'],
     [ 'laundry_id' => '2', 'laundry_item_id' => '2', 'price' => 4 ,'order_type_id'=>'2','service_id'=>'2'],
     [ 'laundry_id' => '2', 'laundry_item_id' => '2', 'price' => 18 ,'order_type_id'=>'1','service_id'=>'1'],
     [ 'laundry_id' => '2', 'laundry_item_id' => '2', 'price' => 5 ,'order_type_id'=>'1','service_id'=>'2'],
*/
   /*  
[ 'laundry_id' => '4', 'laundry_item_id' => '1', 'price' => 6,'order_type_id'=>'2','service_id'=>'1'],
[ 'laundry_id' => '4', 'laundry_item_id' => '1', 'price' => 2,'order_type_id'=>'2','service_id'=>'2'],
[ 'laundry_id' => '4', 'laundry_item_id' => '2', 'price' => 10 ,'order_type_id'=>'2','service_id'=>'1'],
[ 'laundry_id' => '4', 'laundry_item_id' => '2', 'price' => 2,'order_type_id'=>'2','service_id'=>'2'],
*/


  /*   
[ 'laundry_id' => '19', 'laundry_item_id' => '1', 'price' => 8,'order_type_id'=>'2','service_id'=>'1'],
[ 'laundry_id' => '19', 'laundry_item_id' => '1', 'price' => 4,'order_type_id'=>'2','service_id'=>'2'],
[ 'laundry_id' => '19', 'laundry_item_id' => '2', 'price' => 11 ,'order_type_id'=>'2','service_id'=>'1'],
[ 'laundry_id' => '19', 'laundry_item_id' => '2', 'price' => 4,'order_type_id'=>'2','service_id'=>'2'],
     
[ 'laundry_id' => '19', 'laundry_item_id' => '1', 'price' => 10,'order_type_id'=>'1','service_id'=>'1'],
[ 'laundry_id' => '19', 'laundry_item_id' => '1', 'price' => 5,'order_type_id'=>'1','service_id'=>'2'],
[ 'laundry_id' => '19', 'laundry_item_id' => '2', 'price' => 15 ,'order_type_id'=>'1','service_id'=>'1'],
[ 'laundry_id' => '19', 'laundry_item_id' => '2', 'price' => 5,'order_type_id'=>'1','service_id'=>'2'],

*/
/*
[ 'laundry_id' => '20', 'laundry_item_id' => '1', 'price' => 8,'order_type_id'=>'2','service_id'=>'1'],
[ 'laundry_id' => '20', 'laundry_item_id' => '1', 'price' => 4,'order_type_id'=>'2','service_id'=>'2'],
[ 'laundry_id' => '20', 'laundry_item_id' => '2', 'price' => 11 ,'order_type_id'=>'2','service_id'=>'1'],
[ 'laundry_id' => '20', 'laundry_item_id' => '2', 'price' => 4,'order_type_id'=>'2','service_id'=>'2'],
 */   
];

        foreach ($prices as $price) {
            Price::create($price); // تمرير كل عنصر بشكل فردي
        }
    }
}
