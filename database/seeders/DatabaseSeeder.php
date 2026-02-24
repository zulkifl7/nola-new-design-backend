<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }

        $adminEmail = env('ADMIN_EMAIL');
        $adminPass = env('ADMIN_PASSWORD');
        if ($adminEmail && $adminPass) {
            $adminName = env('ADMIN_NAME', 'Admin');
            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make($adminPass),
                    'role' => 'admin',
                ]
            );
        }
    }
}
