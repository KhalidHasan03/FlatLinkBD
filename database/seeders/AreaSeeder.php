<?php
namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $dhaka = City::where('slug', 'dhaka')->first();
        $chattogram = City::where('slug', 'chattogram')->first();
        $sylhet = City::where('slug', 'sylhet')->first();

        $areas = [
            'Dhanmondi', 'Gulshan', 'Banani', 'Uttara', 'Mirpur',
            'Bashundhara R/A', 'Mohammadpur', 'Baridhara', 'Niketan', 'Mohakhali',
            'Nikunja', 'Badda', 'Mirpur DOHS', 'Tejgaon', 'Farmgate',
            'Shyamoli', 'Lalmatia', 'Shahbagh', 'Kawran Bazar', 'Motijheel',
            'Paltan', 'Malibagh', 'Rampura', 'Khilgaon', 'Shantinagar',
            'Wari', 'Kakrail', 'New Market', 'Hazaribagh', 'Jatrabari',
        ];

        foreach ($areas as $i => $name) {
            Area::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'city_id' => $dhaka?->id,
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]
            );
        }

        $extraAreas = [
            ['name' => 'Agrabad', 'city' => $chattogram, 'sort' => 1],
            ['name' => 'Khulshi', 'city' => $chattogram, 'sort' => 2],
            ['name' => 'Zindabazar', 'city' => $sylhet, 'sort' => 1],
            ['name' => 'Uposhohor', 'city' => $sylhet, 'sort' => 2],
        ];

        foreach ($extraAreas as $area) {
            Area::updateOrCreate(
                ['slug' => Str::slug($area['name'])],
                [
                    'name' => $area['name'],
                    'city_id' => $area['city']?->id,
                    'is_active' => true,
                    'sort_order' => $area['sort'],
                ]
            );
        }
    }
}
