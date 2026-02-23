<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Dress', 'slug' => 'dress'],
            ['name' => 'Handbag', 'slug' => 'handbag'],
            ['name' => 'Blanket', 'slug' => 'blanket'],
        ];
        foreach ($categories as $row) {
            Category::firstOrCreate(['slug' => $row['slug']], ['name' => $row['name']]);
        }
    }
}
