<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingDetail;
use App\Models\ListingImage;
use App\Models\ListingImages;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Seed Categories
        // $seedCategory = Category::create(['name' => 'Seeds & Crops', 'type' => 'seed']);
        // $animalCategory = Category::create(['name' => 'Livestock & Animals', 'type' => 'animal']);
        // $harvestCategory = Category::create(['name' => 'Fresh Produce', 'type' => 'seed']);
        // $toolsCategory = Category::create(['name' => 'Farming Tools', 'type' => 'seed']);

        // 2. Create Pilot Farmers (e.g., Sargodha region)
        $user1 = User::create([
            'name' => 'Muhammad Ali',
            'phone' => '03001234567',
            // 'email' => 'muhammad.ali@farmer.pk',
            'password' => Hash::make('password123'),
            'district' => 'Sargodha',
            'village_or_city' => 'Bhalwal',
            'post_office' => 'Bhalwal Main P.O.',
            'bio' => 'Growing organic heirloom wheat and citrus for over 15 years.',
            'status' => 'active',
            // 'phone_verified_at' => now(),
        ]);

        $user2 = User::create([
            'name' => 'Tariq Riaz',
            'phone' => '03019876543',
            // 'email' => 'tariq.riaz@farmer.pk',
            'password' => Hash::make('password123'),
            'district' => 'Sargodha',
            'village_or_city' => 'Shahpur',
            'post_office' => 'Shahpur Saddar',
            'bio' => 'Livestock breeder specializing in healthy Teddy goats and poultry.',
            'status' => 'active',
            // 'phone_verified_at' => now(),
        ]);

        $user3 = User::create([
            'name' => 'Farooq Khan',
            'phone' => '03025558899',
            // 'email' => 'farooq.khan@farmer.pk',
            'password' => Hash::make('password123'),
            'district' => 'Sargodha',
            'village_or_city' => 'Sargodha City',
            'post_office' => 'University Road P.O.',
            'bio' => 'Orchard owner with premium Kinnow and seasonal citrus crops.',
            'status' => 'active',
            // 'phone_verified_at' => now(),
        ]);

        // 3. Create Listings with Barter Info ("Wanted Description")
        
        // Listing 1: Wheat Seeds
        $listing1 = Listing::create([
            'user_id' => $user1->id,
            // 'category_id' => $seedCategory->id,
            'trade_type' => 'exchange',
            'title' => 'Organic Desi Wheat Seeds (2025 Harvest)',
            'description' => 'Untreated organic seeds kept in airtight mud drums. High yield strain suitable for dry soil.',
            'qty' => '25 kg Bag',
            'wanted_description' => '2 Bags of Organic Fertilizer OR Mustard Seeds (10kg)',
            // 'status' => 'active',
        ]);

        // ListingDetail::create([
        //     'listing_id' => $listing1->id,
        //     'key' => 'Germination Rate',
        //     'value' => '92%'
        // ]);
        // ListingDetail::create([
        //     'listing_id' => $listing1->id,
        //     'key' => 'Harvest Year',
        //     'value' => '2025'
        // ]);

        ListingImages::create([
            'listing_id' => $listing1->id,
            'image_path' => 'listings/wheat-seeds.jpg', // Placeholder or sample path
        ]);


        // Listing 2: Teddy Goat
        $listing2 = Listing::create([
            'user_id' => $user2->id,
            // 'category_id' => $animalCategory->id,
            'trade_type' => 'both',
            'title' => 'Healthy Teddy Breeder Goat (14 Months)',
            'description' => 'Active and healthy male breeder goat. Fully vaccinated with recent health certificate.',
            'qty' => '1 Male Goat',
            'wanted_description' => '4 Bags of Animal Feed OR 3 Layer Chickens',
            // 'status' => 'active',
        ]);

        // ListingDetail::create([
        //     'listing_id' => $listing2->id,
        //     'key' => 'Breed',
        //     'value' => 'Teddy'
        // ]);
        // ListingDetail::create([
        //     'listing_id' => $listing2->id,
        //     'key' => 'Vaccination',
        //     'value' => 'Fully Vaccinated'
        // ]);

        ListingImages::create([
            'listing_id' => $listing2->id,
            'image_path' => 'listings/teddy-goat.jpg',
        ]);


        // Listing 3: Kinnow Oranges
        $listing3 = Listing::create([
            'user_id' => $user3->id,
            // 'category_id' => $harvestCategory->id,
            'trade_type' => 'exchange',
            'title' => 'Fresh Farm-Picked Kinnow Oranges',
            'description' => 'Freshly harvested Kinnow from family orchard. Sweet grade A quality in wooden crates.',
            'qty' => '100 kg Crate',
            'wanted_description' => '50 kg Sugarcane Jaggery (Gur) or Honey',
            // 'status' => 'active',
        ]);

        // ListingDetail::create([
        //     'listing_id' => $listing3->id,
        //     'key' => 'Grade',
        //     'value' => 'A-Quality'
        // ]);

        // ListingImage::create([
        //     'listing_id' => $listing3->id,
        //     'image_path' => 'listings/kinnow.jpg',
        // ]);
    }
}