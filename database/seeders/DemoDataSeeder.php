<?php
namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Amenity;
use App\Models\Appointment;
use App\Models\Area;
use App\Models\Buyer;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private array $photos = [
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1400&q=80',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $sellers = $this->seedSellers();
            $buyers = $this->seedBuyers();
            $this->seedStaff();

            $properties = $this->seedProperties($sellers);
            $this->seedEngagement($properties, $buyers);
        });
    }

    private function seedSellers(): array
    {
        $rows = [
            ['name' => 'Rahman Properties', 'email' => 'rahman@flatlinkbd.com', 'phone' => '01711111111', 'business' => 'Rahman Properties Ltd.', 'address' => 'Dhanmondi, Dhaka'],
            ['name' => 'Green Valley Homes', 'email' => 'greenvalley@flatlinkbd.com', 'phone' => '01722222222', 'business' => 'Green Valley Homes', 'address' => 'Gulshan, Dhaka'],
            ['name' => 'Urban Nest Realty', 'email' => 'urbannest@flatlinkbd.com', 'phone' => '01733333333', 'business' => 'Urban Nest Realty', 'address' => 'Uttara, Dhaka'],
            ['name' => 'Skyline Estates', 'email' => 'skyline@flatlinkbd.com', 'phone' => '01744444444', 'business' => 'Skyline Estates', 'address' => 'Banani, Dhaka'],
            ['name' => 'Bay View Properties', 'email' => 'bayview@flatlinkbd.com', 'phone' => '01755555555', 'business' => 'Bay View Properties', 'address' => 'Agrabad, Chattogram'],
        ];

        $sellers = [];

        foreach ($rows as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'seller',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            Seller::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $row['business'],
                    'phone' => $row['phone'],
                    'address' => $row['address'],
                    'nid_number' => 'NID-' . strtoupper(substr(md5($row['email']), 0, 10)),
                    'notes' => 'Verified partner agency.',
                ]
            );

            $sellers[] = $user;
        }

        return $sellers;
    }

    private function seedBuyers(): array
    {
        $rows = [
            ['name' => 'Ayesha Siddika', 'email' => 'ayesha@example.com', 'phone' => '01811111111', 'budget_min' => 5000000, 'budget_max' => 25000000],
            ['name' => 'Tanvir Hasan', 'email' => 'tanvir@example.com', 'phone' => '01822222222', 'budget_min' => 8000000, 'budget_max' => 40000000],
            ['name' => 'Nusrat Jahan', 'email' => 'nusrat@example.com', 'phone' => '01833333333', 'budget_min' => 3000000, 'budget_max' => 15000000],
            ['name' => 'Imran Kabir', 'email' => 'imran@example.com', 'phone' => '01844444444', 'budget_min' => 10000000, 'budget_max' => 60000000],
            ['name' => 'Sadia Islam', 'email' => 'sadia@example.com', 'phone' => '01855555555', 'budget_min' => 2000000, 'budget_max' => 9000000],
        ];

        $buyers = [];

        foreach ($rows as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'buyer',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            Buyer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $row['phone'],
                    'budget_min' => $row['budget_min'],
                    'budget_max' => $row['budget_max'],
                    'preferred_areas' => 'Dhanmondi, Gulshan, Uttara, Banani',
                    'property_type_preference' => 'Apartment, Flat',
                ]
            );

            $buyers[] = $user;
        }

        return $buyers;
    }

    private function seedStaff(): void
    {
        User::updateOrCreate(
            ['email' => 'staff@flatlinkbd.com'],
            [
                'name' => 'Operations Staff',
                'phone' => '01799999999',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedProperties(array $sellers): array
    {
        $rows = [
            ['Luxury 3 Bed Apartment in Dhanmondi', 'dhanmondi', 'apartment', 'sale', 18500000, 1650, 3, 3, 5, 8, 'new', 'Road 27, Dhanmondi, Dhaka', true, 'published', 'published'],
            ['Spacious 4 Bed Family Flat in Gulshan 2', 'gulshan', 'flat', 'sale', 42000000, 2800, 4, 4, 7, 10, 'renovated', 'Road 45, Gulshan 2, Dhaka', true, 'published', 'published'],
            ['Modern Studio Apartment in Banani', 'banani', 'studio', 'rental', 45000, 620, 1, 1, 4, 12, 'new', 'Kemal Ataturk Avenue, Banani, Dhaka', true, 'published', 'published'],
            ['Elegant Duplex in Bashundhara R/A', 'bashundhara-ra', 'duplex', 'sale', 27500000, 2400, 4, 4, 2, 6, 'new', 'Block C, Bashundhara R/A, Dhaka', false, 'published', 'published'],
            ['Affordable 2 Bed Flat in Mirpur', 'mirpur', 'flat', 'sale', 7200000, 950, 2, 2, 3, 7, 'old', 'Section 10, Mirpur, Dhaka', false, 'published', 'published'],
            ['Premium Penthouse in Baridhara', 'baridhara', 'penthouse', 'sale', 65000000, 3600, 5, 5, 12, 12, 'new', 'Diplomatic Zone, Baridhara, Dhaka', true, 'published', 'published'],
            ['Cozy 2 Bed Apartment in Mohammadpur', 'mohammadpur', 'apartment', 'rental', 32000, 880, 2, 2, 5, 9, 'renovated', 'Ring Road, Mohammadpur, Dhaka', false, 'published', 'published'],
            ['Sea View Flat in Agrabad', 'agrabad', 'flat', 'sale', 9800000, 1250, 3, 2, 6, 11, 'new', 'Agrabad C/A, Chattogram', false, 'published', 'published'],
            ['Corporate Office Space in Motijheel', 'motijheel', 'office-space', 'rental', 180000, 2200, 0, 2, 8, 15, 'new', 'Motijheel C/A, Dhaka', false, 'published', 'published'],
            ['Family Flat in Uttara Sector 7', 'uttara', 'flat', 'sale', 11500000, 1350, 3, 3, 4, 8, 'renovated', 'Sector 7, Uttara, Dhaka', true, 'published', 'published'],
            ['Stylish Studio in Zindabazar', 'zindabazar', 'studio', 'rental', 28000, 540, 1, 1, 3, 6, 'new', 'Zindabazar, Sylhet', false, 'published', 'published'],
            ['Commercial Space in Kawran Bazar', 'kawran-bazar', 'commercial', 'rental', 250000, 3000, 0, 2, 1, 14, 'old', 'Kawran Bazar, Dhaka', false, 'published', 'published'],
            ['Bright 3 Bed Flat in Khulshi', 'khulshi', 'flat', 'sale', 13500000, 1480, 3, 3, 5, 9, 'new', 'Khulshi, Chattogram', true, 'published', 'published'],
            ['Lake View Apartment in Gulshan 1', 'gulshan', 'apartment', 'rental', 95000, 1750, 3, 3, 6, 10, 'new', 'Road 88, Gulshan 1, Dhaka', true, 'published', 'published'],
            ['Budget Flat in Mirpur DOHS', 'mirpur-dohs', 'flat', 'sale', 8900000, 1100, 2, 2, 4, 8, 'renovated', 'Mirpur DOHS, Dhaka', false, 'published', 'published'],
            ['Garden Duplex in Uposhohor', 'uposhohor', 'duplex', 'sale', 21500000, 2600, 4, 5, 1, 3, 'new', 'Uposhohor, Sylhet', false, 'published', 'published'],
            ['Furnished Sublet in Dhanmondi', 'dhanmondi', 'sublet', 'rental', 38000, 720, 2, 1, 3, 7, 'renovated', 'Road 12, Dhanmondi, Dhaka', false, 'published', 'published'],
            ['Executive Flat in Banani DOHS', 'banani', 'flat', 'sale', 33500000, 2100, 3, 4, 8, 11, 'new', 'Banani DOHS, Dhaka', false, 'published', 'published'],
            ['New Launch: 3 Bed Apartment in Rampura', 'rampura', 'apartment', 'sale', 10500000, 1290, 3, 3, 2, 10, 'under_construction', 'Rampura, Dhaka', false, 'draft', 'pending'],
            ['Under Review: Duplex in Paltan', 'paltan', 'duplex', 'sale', 24500000, 2750, 4, 4, 1, 5, 'new', 'Paltan, Dhaka', false, 'draft', 'pending'],
            ['Draft Listing: Studio in Tejgaon', 'tejgaon', 'studio', 'rental', 25000, 500, 1, 1, 2, 6, 'renovated', 'Tejgaon, Dhaka', false, 'draft', 'pending'],
        ];

        $amenityIds = Amenity::where('is_active', true)->orderBy('id')->pluck('id')->all();
        $properties = [];

        foreach ($rows as $index => $row) {
            [$title, $areaSlug, $typeSlug, $purpose, $price, $size, $beds, $baths, $floor, $total, $condition, $address, $featured, $status, $verification] = $row;

            $area = Area::where('slug', $areaSlug)->first();
            $type = PropertyType::where('slug', $typeSlug)->first();
            $seller = $sellers[$index % count($sellers)];
            $slug = Str::slug($title);

            $property = Property::updateOrCreate(
                ['slug' => $slug],
                [
                    'seller_id' => $seller->id,
                    'title' => $title,
                    'description' => $this->descriptionFor($title, $beds, $baths, $size, $area?->name, $purpose),
                    'property_type_id' => $type?->id,
                    'purpose' => $purpose,
                    'price' => $price,
                    'price_negotiable' => $purpose === 'sale',
                    'size_sqft' => $size,
                    'bedrooms' => $beds,
                    'bathrooms' => $baths,
                    'floor_no' => $floor,
                    'total_floors' => $total,
                    'condition' => $condition,
                    'address' => $address,
                    'area_id' => $area?->id,
                    'city_id' => $area?->city_id,
                    'featured_image' => $this->photos[$index % count($this->photos)],
                    'verification_status' => $verification,
                    'status' => $status,
                    'views_count' => 40 + ($index * 17),
                    'is_featured' => $featured,
                    'admin_notes' => $status === 'draft' ? 'Awaiting verification by the operations team.' : null,
                ]
            );

            $property->amenities()->sync($this->amenitySlice($amenityIds, $index));

            $property->images()->delete();
            foreach ([0, 1, 2, 3, 4] as $offset) {
                $photo = $this->photos[($index + $offset) % count($this->photos)];
                $property->images()->create([
                    'image_path' => $photo,
                    'sort_order' => $offset,
                    'is_featured' => $offset === 0,
                ]);
            }

            $properties[] = $property;
        }

        return $properties;
    }

    private function amenitySlice(array $ids, int $offset, int $take = 6): array
    {
        if (empty($ids)) {
            return [];
        }

        $count = count($ids);
        $slice = [];

        for ($i = 0; $i < $take && $i < $count; $i++) {
            $slice[] = $ids[($offset + $i) % $count];
        }

        return array_values(array_unique($slice));
    }

    private function descriptionFor(string $title, int $beds, int $baths, int $size, ?string $area, string $purpose): string
    {
        $place = $area ?: 'the heart of the city';
        $tenure = $purpose === 'rental' ? 'monthly rental' : 'sale';

        return "{$title} is a beautifully designed home available for {$tenure}, located in {$place}. "
            . "Offering {$beds} bedrooms, {$baths} bathrooms and approximately {$size} sqft of living space, "
            . "this property blends comfort with modern elegance. Enjoy bright open-plan living areas, premium finishes, "
            . "secure access and excellent connectivity to schools, hospitals, shopping and transport. "
            . "Schedule a visit today and make it yours.";
    }

    private function seedEngagement(array $properties, array $buyers): void
    {
        $published = collect($properties)->where('status', 'published')->values();
        $statuses = ['requested', 'confirmed', 'completed', 'cancelled'];

        foreach ($published->take(10) as $i => $property) {
            $buyer = $buyers[$i % count($buyers)];

            Appointment::updateOrCreate(
                [
                    'property_id' => $property->id,
                    'buyer_id' => $buyer->id,
                ],
                [
                    'seller_id' => $property->seller_id,
                    'scheduled_date' => now()->addDays($i + 2)->toDateString(),
                    'scheduled_time' => sprintf('%02d:00', 10 + ($i % 6)),
                    'status' => $statuses[$i % count($statuses)],
                    'buyer_notes' => 'Please confirm the visiting time.',
                ]
            );
        }

        $stages = ['inquiry', 'visit_done', 'negotiation', 'deal_closed', 'cancelled', 'inquiry', 'negotiation', 'deal_closed'];

        foreach ($published->take(8) as $i => $property) {
            $buyer = $buyers[$i % count($buyers)];
            $stage = $stages[$i];
            $closed = $stage === 'deal_closed';
            $agreed = $closed ? (float) $property->price : null;

            Deal::updateOrCreate(
                [
                    'property_id' => $property->id,
                    'buyer_id' => $buyer->id,
                ],
                [
                    'seller_id' => $property->seller_id,
                    'stage' => $stage,
                    'status' => $closed ? 'closed' : ($stage === 'cancelled' ? 'cancelled' : 'active'),
                    'agreed_price' => $agreed,
                    'commission_type' => $closed ? 'percentage' : null,
                    'commission_value' => $closed ? 2 : null,
                    'commission_amount' => $agreed ? round($agreed * 0.02, 2) : null,
                    'deal_date' => now()->subDays(25 - $i)->toDateString(),
                    'close_date' => $closed ? now()->subDays(2)->toDateString() : null,
                    'notes' => 'Deal tracked from the visit request.',
                ]
            );
        }

        foreach ($buyers as $index => $buyer) {
            foreach ($published->slice($index, 3) as $property) {
                if ($property->seller_id === $buyer->id) {
                    continue;
                }

                Favorite::firstOrCreate([
                    'user_id' => $buyer->id,
                    'property_id' => $property->id,
                ]);
            }

            Notification::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'type' => 'welcome',
                    'title' => 'Welcome to Flat-LinkBD',
                ],
                [
                    'message' => 'Start exploring verified flats and apartments across Bangladesh.',
                    'data' => ['link' => '/properties'],
                ]
            );
        }

        foreach ($published->take(5) as $property) {
            Notification::updateOrCreate(
                [
                    'user_id' => $property->seller_id,
                    'type' => 'appointment_request',
                    'title' => 'New Visit Request',
                    'message' => 'A buyer requested a visit for ' . $property->title . '.',
                ],
                [
                    'data' => ['property_id' => $property->id],
                ]
            );
        }

        $contacts = [
            ['name' => 'Sabbir Ahmed', 'email' => 'sabbir@example.com', 'phone' => '01911111111', 'message' => 'I am interested in the Gulshan apartment. Please share more details.', 'is_read' => false],
            ['name' => 'Farhana Akter', 'email' => 'farhana@example.com', 'phone' => '01922222222', 'message' => 'Do you offer any rental flats near Dhanmondi?', 'is_read' => false],
            ['name' => 'Rakib Hossain', 'email' => 'rakib@example.com', 'phone' => '01933333333', 'message' => 'I would like to schedule a site visit this weekend.', 'is_read' => true],
            ['name' => 'Mehedi Hasan', 'email' => 'mehedi@example.com', 'phone' => '01944444444', 'message' => 'What is the service charge for the Banani studio?', 'is_read' => false],
            ['name' => 'Tahmina Rahman', 'email' => 'tahmina@example.com', 'phone' => '01955555555', 'message' => 'Please share the floor plan for the Bashundhara duplex.', 'is_read' => true],
        ];

        foreach ($contacts as $contact) {
            Contact::updateOrCreate(
                ['email' => $contact['email'], 'message' => $contact['message']],
                $contact
            );
        }

        $admin = User::where('role', 'super_admin')->first();
        $activities = [
            ['action' => 'property_published', 'description' => 'A new property was published for sale.'],
            ['action' => 'deal_closed', 'description' => 'A deal was closed and commission recorded.'],
            ['action' => 'user_registered', 'description' => 'A new seller joined the platform.'],
            ['action' => 'property_verified', 'description' => 'A listing passed the verification review.'],
            ['action' => 'appointment_confirmed', 'description' => 'A site visit was confirmed with the buyer.'],
        ];

        foreach ($activities as $activity) {
            ActivityLog::firstOrCreate(
                ['action' => $activity['action'], 'description' => $activity['description']],
                ['user_id' => $admin?->id, 'properties' => []]
            );
        }
    }
}
