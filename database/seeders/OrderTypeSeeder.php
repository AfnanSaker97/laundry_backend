<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderType;
class OrderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $OrderTypes = [
            [ 'type' => 'un directly', 'price' =>  '30'],
            [ 'type' => 'directly', 'price' =>  '60'],
           ];
          foreach ($OrderTypes as $OrderType) {
            OrderType::create($OrderType);
        }
    }
}
