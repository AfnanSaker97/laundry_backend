<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Laundry;
class LaundrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laundries = [
            [ 'name' => '','email'=>'','photo'=> 'name','phone_number'=>'','country'=>'','city'=>'','address_line_1'=>'','lat'=>'','lng'=>''],
            [ 'name' => '','email'=>'','photo'=> 'name','phone_number'=>'','country'=>'','city'=>'','address_line_1'=>'','lat'=>'','lng'=>''],
            [ 'name' => '','email'=>'','photo'=> 'name','phone_number'=>'','country'=>'','city'=>'','address_line_1'=>'','lat'=>'','lng'=>''],
            [ 'name' => '','email'=>'','photo'=> 'name','phone_number'=>'','country'=>'','city'=>'','address_line_1'=>'','lat'=>'','lng'=>''],
         
           ];
          foreach ($laundries as $laundry) {
            Laundry::create($laundry);
        } 
    }
}
