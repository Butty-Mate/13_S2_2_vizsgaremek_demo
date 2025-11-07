<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Location;
use App\Models\Camping;
use App\Models\CampingSpot;
use App\Models\Booking;

class CampingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin user (owner)
        $owner = User::where('email', 'admin@admin.com')->first();
        $guest = User::where('email', 'guest@test.com')->first();

        // Create locations
        $balaton = Location::create([
            'city' => 'Siófok',
            'county' => 'Somogy',
            'postcode' => 8600,
            'street' => 'Balaton part',
            'street_number' => '123',
        ]);

        $tisza = Location::create([
            'city' => 'Tiszafüred',
            'county' => 'Jász-Nagykun-Szolnok',
            'postcode' => 5350,
            'street' => 'Tisza part',
            'street_number' => '45',
        ]);

        $velence = Location::create([
            'city' => 'Velence',
            'county' => 'Fejér',
            'postcode' => 2481,
            'street' => 'Velence part',
            'street_number' => '67',
        ]);

        $budapest = Location::create([
            'city' => 'Budapest',
            'county' => 'Budapest',
            'postcode' => 1039,
            'street' => 'Duna part',
            'street_number' => '12',
        ]);

        // Create campings
        $balatoniKemping = Camping::create([
            'user_id' => $owner->id,
            'location_id' => $balaton->id,
            'camping_name' => 'Balatoni Napfény Kemping',
            'description' => 'Családbarát kemping a Balaton partján, közvetlen vízparti hozzáféréssel. Tiszta környezet, modern sanitáris blokk, és gyerekjátszótér.',
            'image_url' => 'https://picsum.photos/id/1015/800/600',
            'company_name' => 'Napfény Kemping Kft.',
            'tax_id' => '12345678-1-23',
            'billing_address' => '8600 Siófok, Balaton part 123',
        ]);

        $tiszaiKemping = Camping::create([
            'user_id' => $owner->id,
            'location_id' => $tisza->id,
            'camping_name' => 'Tisza-tavi Horgász Kemping',
            'description' => 'Horgászparadicsom a Tisza-tó partján. Csónakkölcsönzés, horgászengedély árusítás, és kiváló halfogási lehetőségek.',
            'image_url' => 'https://picsum.photos/id/1018/800/600',
            'company_name' => 'Horgász Kemping Bt.',
            'tax_id' => '87654321-2-34',
            'billing_address' => '5350 Tiszafüred, Tisza part 45',
        ]);

        $velenceiKemping = Camping::create([
            'user_id' => $owner->id,
            'location_id' => $velence->id,
            'camping_name' => 'Velencei Romantika Kemping',
            'description' => 'Csendes, nyugodt kemping pároknak és családoknak. Wifi, étterem, és kerékpárkölcsönzés elérhető.',
            'image_url' => 'https://picsum.photos/id/1024/800/600',
            'company_name' => 'Romantika Kemping Zrt.',
            'tax_id' => '11223344-3-45',
            'billing_address' => '2481 Velence, Velence part 67',
        ]);

        $budapestiKemping = Camping::create([
            'user_id' => $owner->id,
            'location_id' => $budapest->id,
            'camping_name' => 'Budapest City Kemping',
            'description' => 'Modern városi kemping a Duna-partján. Kiváló tömegközlekedési kapcsolat, közel a belvároshoz. Ideális városnézéshez.',
            'image_url' => 'https://picsum.photos/id/1031/800/600',
            'company_name' => 'City Kemping Budapest Kft.',
            'tax_id' => '99887766-4-56',
            'billing_address' => '1039 Budapest, Duna part 12',
        ]);

        // Create camping spots for all campings
        $spotTypes = ['standard', 'premium', 'vip'];
        
        // Balatoni Kemping (4 rows x 5 columns = 20 spots)
        $spotCounter = 1;
        for ($row = 1; $row <= 4; $row++) {
            for ($col = 1; $col <= 5; $col++) {
                $type = $spotTypes[array_rand($spotTypes)];
                $price = match($type) {
                    'standard' => 5000,
                    'premium' => 6500,
                    'vip' => 8000,
                };
                
                CampingSpot::create([
                    'camping_id' => $balatoniKemping->id,
                    'name' => "Hely {$spotCounter}",
                    'row' => $row,
                    'column' => $col,
                    'type' => $type,
                    'capacity' => rand(2, 4),
                    'price_per_night' => $price,
                    'is_available' => $spotCounter <= 15,
                    'rating' => rand(40, 50) / 10,
                    'tags' => 'wifi,árnyék,víz',
                    'services' => 'elektromos csatlakozó,grillező',
                    'description' => "Kényelmes {$type} hely közvetlen vízparti elhelyezkedéssel.",
                ]);
                $spotCounter++;
            }
        }

        // Tisza-tó (3 rows x 5 columns = 15 spots)
        $spotCounter = 1;
        for ($row = 1; $row <= 3; $row++) {
            for ($col = 1; $col <= 5; $col++) {
                $type = $spotTypes[array_rand($spotTypes)];
                $price = match($type) {
                    'standard' => 4500,
                    'premium' => 5800,
                    'vip' => 7200,
                };
                
                CampingSpot::create([
                    'camping_id' => $tiszaiKemping->id,
                    'name' => "Horgász hely {$spotCounter}",
                    'row' => $row,
                    'column' => $col,
                    'type' => $type,
                    'capacity' => rand(2, 4),
                    'price_per_night' => $price,
                    'is_available' => $spotCounter <= 12,
                    'rating' => rand(38, 48) / 10,
                    'tags' => 'horgászat,csónak,wifi',
                    'services' => 'elektromos csatlakozó,hal tisztítóhely',
                    'description' => "Horgászoknak ideális {$type} hely.",
                ]);
                $spotCounter++;
            }
        }

        // Velence (3 rows x 4 columns = 12 spots)
        $spotCounter = 1;
        for ($row = 1; $row <= 3; $row++) {
            for ($col = 1; $col <= 4; $col++) {
                $type = $spotTypes[array_rand($spotTypes)];
                $price = match($type) {
                    'standard' => 4800,
                    'premium' => 6200,
                    'vip' => 7500,
                };
                
                CampingSpot::create([
                    'camping_id' => $velenceiKemping->id,
                    'name' => "Romantika hely {$spotCounter}",
                    'row' => $row,
                    'column' => $col,
                    'type' => $type,
                    'capacity' => rand(2, 4),
                    'price_per_night' => $price,
                    'is_available' => $spotCounter <= 10,
                    'rating' => rand(42, 50) / 10,
                    'tags' => 'romantikus,wifi,étterem',
                    'services' => 'elektromos csatlakozó,privát grillező,kerékpár',
                    'description' => "Romantikus {$type} hely nyugodt környezetben.",
                ]);
                $spotCounter++;
            }
        }

        // Budapest (2 rows x 5 columns = 10 spots)
        $spotCounter = 1;
        for ($row = 1; $row <= 2; $row++) {
            for ($col = 1; $col <= 5; $col++) {
                $type = $spotTypes[array_rand($spotTypes)];
                $price = match($type) {
                    'standard' => 6000,
                    'premium' => 7500,
                    'vip' => 9000,
                };
                
                CampingSpot::create([
                    'camping_id' => $budapestiKemping->id,
                    'name' => "City hely {$spotCounter}",
                    'row' => $row,
                    'column' => $col,
                    'type' => $type,
                    'capacity' => rand(2, 4),
                    'price_per_night' => $price,
                    'is_available' => $spotCounter <= 8,
                    'rating' => rand(43, 50) / 10,
                    'tags' => 'városi,wifi,tömegközlekedés',
                    'services' => 'elektromos csatlakozó,wifi,zárható tároló,biciklikölcsönző',
                    'description' => "Modern {$type} hely városi környezetben, közel a látnivalókhoz.",
                ]);
                $spotCounter++;
            }
        }

        // Create some bookings
        $balatoniSpots = CampingSpot::where('camping_id', $balatoniKemping->id)->where('is_available', false)->take(5)->get();
        
        foreach ($balatoniSpots as $index => $spot) {
            Booking::create([
                'user_id' => $guest->id,
                'camping_id' => $balatoniKemping->id,
                'camping_spot_id' => $spot->id,
                'arrival_date' => now()->addDays($index * 3)->format('Y-m-d'),
                'departure_date' => now()->addDays($index * 3 + 2)->format('Y-m-d'),
                'status' => $index < 2 ? 'confirmed' : 'pending',
            ]);
        }

        echo "✅ Minta adatok sikeresen létrehozva!\n";
        echo "   - 4 lokáció (Balaton, Tisza-tó, Velencei-tó, Budapest)\n";
        echo "   - 4 kemping összesen 57 hellyel\n";
        echo "   - 5 minta foglalás\n";
    }
}
