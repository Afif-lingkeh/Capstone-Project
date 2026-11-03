<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Data referensi (aman di production)
            RolePermissionSeeder::class,
            CountrySeeder::class,

            // Data contoh (otomatis dilewati di production)
            DemoUserSeeder::class,
            DemoLinkSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}
