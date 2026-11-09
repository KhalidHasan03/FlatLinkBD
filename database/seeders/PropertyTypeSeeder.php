<?php
namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Apartment', 'for_sale' => true, 'for_rent' => true],
            ['name' => 'Flat', 'for_sale' => true, 'for_rent' => true],
            ['name' => 'Duplex', 'for_sale' => true, 'for_rent' => false],
            ['name' => 'Penthouse', 'for_sale' => true, 'for_rent' => false],
            ['name' => 'Studio', 'for_sale' => true, 'for_rent' => true],
            ['name' => 'Sublet', 'for_sale' => false, 'for_rent' => true],
            ['name' => 'Office Space', 'for_sale' => true, 'for_rent' => true],
            ['name' => 'Commercial', 'for_sale' => true, 'for_rent' => true],
        ];

        foreach ($types as $type) {
            PropertyType::updateOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'slug' => Str::slug($type['name']),
                    'for_sale' => $type['for_sale'],
                    'for_rent' => $type['for_rent'],
                    'is_active' => true,
                ]
            );
        }
    }
}
