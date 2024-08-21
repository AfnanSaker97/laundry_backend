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
            [ 'item_type_en' => 'Men\'s Abaya', 'item_type_ar' => ' عباية رجالي '],
            [ 'item_type_en' => 'Women\'s Abaya', 'item_type_ar' => 'عباية نسواني'],
         
           ];
          foreach ($LaundryItems as $LaundryItem) {
            LaundryItem::create($LaundryItem);
        }
    }
}
