<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cats = [
            [
                'name' => 'Seeds',
                'image_path' => 'categories/seeds_plants.jpg',
            ],
            [
                'name' => 'Plants',
                'image_path' => 'categories/plants.jpg',
            ],
            [
                'name' => 'Animals',
                'image_path' => 'categories/home_appliances.jpg',
            ],
            [
                'name' => 'Birds',
                'image_path' => 'categories/furniture.jpg',
            ],
            [
                'name' => 'Tools',
                'image_path' => 'categories/clothing_accessories.jpg',
            ]
        ];


        foreach ($cats as $cat) {
            Category::create($cat);
        }
    }
}
