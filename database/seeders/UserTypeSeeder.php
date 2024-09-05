<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserType;
class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_types = [
 
            [ 'type' => 'admin'],
            [ 'type' => 'User'],
            [ 'type' => 'driver'],
            [ 'type' => 'super admin'],
           ];
          foreach ($user_types as $user_type) {
            UserType::create($user_type);
        }
    }
}
