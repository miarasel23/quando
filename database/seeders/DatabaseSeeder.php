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
        $menu_category = [
            'AFGHAN',
            'AFRICAN',
            'AMERICAN',
            'ARABIC',
            'ARGENTINIAN',
            'ASIAN',
            'ASIAN_FUSION',
            'BBQ',
            'BANGLADESHI',
            'BELGIAN',
            'BRAZILIAN',
            'BRITISH',
            'BURGERS',
            'CAJUN',
            'CAKE AND_COFFEE',
            'CANTONESE',
            'CARIBBEAN',
            'CHINESE',
            'COLOMBIAN',
            'CONTEMPORARY',
            'CREOLE',
            'DESSERT',
            'DIM SUM',
            'DRINKS',
            'EAT AND_DRINK',
            'ECUADORIAN',
            'ERITREAN',
            'ETHIOPIAN',
            'EUROPEAN',
            'FILIPINO',
            'FISH',
            'FISH AND_CHIPS',
            'FRENCH',
            'FUSION',
            'GEORGIAN',
            'GOURMET',
            'GREEK',
            'HIGH_TEA',
            'INDIAN',
            'INDOCHINESE',
            'INTERNATIONAL',
            'IRAQI',
            'IRISH',
            'ITALIAN',
            'IZAKAYA',
            'JAMAICAN',
            'JAPANESE',
            'KOREAN',
            'LATIN_AMERICAN',
            'LEBANESE',
            'MALAYSIAN',
            'MEDITERRANEAN',
            'MEXICAN',
            'MIDDLE_EASTERN',
            'MOROCCAN',
            'NEPALESE',
            'NIGERIAN',
            'PAKISTANI',
            'PASTA',
            'PERSIAN_IRANIAN',
            'PERUVIAN',
            'PIZZA',
            'POLISH',
            'PORTUGUESE',
            'ROMAN',
            'SCOTTISH',
            'SEAFOOD',
            'SICHUAN',
            'SICILIAN',
            'SOUTH AFRICAN',
            'SOUTH_AMERICAN',
            'SOUTHEAST ASIAN',
            'SPANISH',
            'SRI LANKAN',
            'STEAK',
            'SUSHI',
            'TAIWANESE',
            'THAI',
            'THEMED',
            'TURKISH',
            'UKRAINIAN',
            'VEGAN',
            'VEGETARIAN',
            'VIETNAMESE',
        ];


        foreach ($menu_category as $menu_data) {
            MenuCatergory::create([
                'name' => $menu_data,
                'status' => 'active',
            ]);
        }

    }
}
