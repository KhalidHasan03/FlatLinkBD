<?php
namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Dhaka', 'slug' => 'dhaka'],
            ['name' => 'Chattogram', 'slug' => 'chattogram'],
            ['name' => 'Sylhet', 'slug' => 'sylhet'],
            ['name' => 'Khulna', 'slug' => 'khulna'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['slug' => $city['slug']],
                $city + ['is_active' => true]
            );
        }
    }
}
