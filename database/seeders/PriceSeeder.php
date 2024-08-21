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
            [ 'laundry_id' => '1', 'laundry_item_id' => '1', 'price' => 50 ],
            [ 'laundry_id' => '1', 'laundry_item_id' => '2', 'price' => 40 ],
            [ 'laundry_id' => '2', 'laundry_item_id' => '1', 'price' => 60 ],
            [ 'laundry_id' => '2', 'laundry_item_id' => '2', 'price' => 50 ],
            [ 'laundry_id' => '3', 'laundry_item_id' => '1', 'price' => 50 ],
            [ 'laundry_id' => '3', 'laundry_item_id' => '2', 'price' => 50 ],
            [ 'laundry_id' => '4', 'laundry_item_id' => '1', 'price' => 50 ],
            [ 'laundry_id' => '4', 'laundry_item_id' => '2', 'price' => 40 ],
        ];

        foreach ($prices as $price) {
            Price::create($price); // تمرير كل عنصر بشكل فردي
        }
    }
}
