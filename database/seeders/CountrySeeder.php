<?php
// database/seeders/CountrySeeder.php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

// Data referensi: aman dijalankan di production. Negara lain bisa ditambah kapan saja.
class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            'ID' => 'Indonesia',
            'JP' => 'Jepang',
            'RU' => 'Rusia',
            'SK' => 'Slovakia',
            'EG' => 'Mesir',
            'MY' => 'Malaysia',
            'TR' => 'Turki',
            'SA' => 'Arab Saudi',
            'DE' => 'Jerman',
        ];

        foreach ($countries as $iso2 => $name) {
            Country::updateOrCreate(['iso2' => $iso2], ['name' => $name]);
        }
    }
}
