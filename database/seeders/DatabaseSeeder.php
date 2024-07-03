<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
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
    }
}
