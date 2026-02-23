<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/products.json');
        if (!File::exists($path)) {
            return;
        }
        $json = File::get($path);
        $items = json_decode($json, true);
        if (!is_array($items)) {
            return;
        }
        foreach ($items as $row) {
            $category = Category::where('slug', Str::slug($row['category']))->first();
            if (!$category) {
                $category = Category::firstOrCreate([
                    'slug' => Str::slug($row['category']),
                ], [
                    'name' => $row['category'],
                ]);
            }
            $product = Product::firstOrCreate(
                ['code' => $row['code']],
                [
                    'category_id' => $category->id,
                    'name' => $row['name'],
                    'slug' => $row['slug'] ?? null,
                    'description' => $row['description'] ?? null,
                    'price' => $row['price'] ?? null,
                    'currency' => $row['currency'] ?? 'LKR',
                    'cover_image' => $row['cover_image'] ?? null,
                    'featured' => $row['featured'] ?? false,
                    'status' => $row['status'] ?? 'active',
                    'seo_title' => $row['seo_title'] ?? null,
                    'seo_description' => $row['seo_description'] ?? null,
                ]
            );
            $sort = 0;
            foreach ($row['images'] ?? [] as $img) {
                $product->images()->firstOrCreate(['path' => $img['path'] ?? $img], [
                    'alt' => $img['alt'] ?? null,
                    'sort_order' => $sort++,
                ]);
            }
            $sort = 0;
            foreach ($row['colors'] ?? [] as $c) {
                $product->colors()->firstOrCreate(['name' => $c['name']], [
                    'hex' => $c['hex'] ?? null,
                    'sort_order' => $sort++,
                ]);
            }
            $sort = 0;
            foreach ($row['sizes'] ?? [] as $s) {
                $name = is_array($s) ? ($s['name'] ?? '') : $s;
                $product->sizes()->firstOrCreate(['name' => $name], [
                    'sort_order' => $sort++,
                ]);
            }
            $sort = 0;
            foreach ($row['materials'] ?? [] as $m) {
                $text = is_array($m) ? ($m['text'] ?? '') : $m;
                $product->materials()->firstOrCreate(['text' => $text], [
                    'sort_order' => $sort++,
                ]);
            }
            $sort = 0;
            foreach ($row['care'] ?? [] as $c) {
                $text = is_array($c) ? ($c['text'] ?? '') : $c;
                $product->careInstructions()->firstOrCreate(['text' => $text], [
                    'sort_order' => $sort++,
                ]);
            }
        }
    }
}
