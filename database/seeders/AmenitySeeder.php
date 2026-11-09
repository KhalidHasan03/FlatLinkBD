<?php
namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            'Lift/Elevator', 'Parking', 'Generator', 'CCTV', 'Security Guard',
            'Swimming Pool', 'Gym', 'Community Hall', 'Children Play Area',
            'Rooftop Access', 'Gas Connection', 'Electricity Backup',
            'Water Supply', 'Waste Disposal', 'Intercom', 'Fire Safety',
            'Park/Garden View', 'Prayer Room', 'Visitor Parking', 'Servant Quarter',
        ];

        foreach ($amenities as $amenity) {
            Amenity::updateOrCreate(
                ['name' => $amenity],
                ['icon' => Str::slug($amenity), 'is_active' => true]
            );
        }
    }
}
