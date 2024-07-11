<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\MenuCatergory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         $cuisines = [
            'Afghan',
            'American',
            'Argentinian',
            'Asian',
            'Australian',
            'Austrian',
            'Bangladeshi',
            'Belgian',
            'Brazilian',
            'British',
        ];
        foreach ($cuisines as $cuisine) {
            Category::create(['name' => $cuisine]);
        }
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'res_uuid' => Hash::make('password'),
            'user_type' => 'super_admin',
        ]);


        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'res_uuid' => Hash::make('password'),
            'user_type' => 'super_admin',
        ]);


        $menu_catergory = [
            'PAPADAM',
            'VEG STARTERS',
            'SEA FOOD STARTERS',
            'NON VEG STARTERS',
            'GRILL',
            'VEG CURRIES',
            'NON VEG CURRIES',
            'SEA FOOD CURRIES',
            'HYDERABADI BIRYANIS',
            'RICE & NOODLES',
            'VILLAGE FLAVOURS SPECIALS',
            'BREADS',
            'MANDI',
            'BURGER',
            'DRINKS',
            'DESSERTS',
        ];
        
        foreach ($menu_catergory as $menu_data) {
            MenuCatergory::create([
                'name' => $menu_data,
                'status' => 'active',
            ]);
        }

    }
}
