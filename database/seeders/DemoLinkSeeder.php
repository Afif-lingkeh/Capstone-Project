<?php
// database/seeders/DemoLinkSeeder.php

namespace Database\Seeders;

use App\Enums\InstitutionType;
use App\Enums\LinkStatus;
use App\Models\Country;
use App\Models\Institution;
use App\Models\OfficialLink;
use App\Models\User;
use Illuminate\Database\Seeder;

// Data contoh untuk development. Status "verified" di sini BUKAN hasil verifikasi sungguhan:
// sebelum go-live, setiap tautan wajib diverifikasi ulang oleh admin pesantren.
class DemoLinkSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction() && ! config('peduli.allow_demo_seed')) {
            $this->command?->warn('DemoLinkSeeder dilewati di production.');

            return;
        }

        $countries = Country::pluck('id', 'iso2');
        $verifier = User::where('username', 'admin')->first();

        $data = [
            // [nama, kategori, negara, kota, [[label, url, status], ...]]
            ['Universitas Indonesia', InstitutionType::Ptn, 'ID', 'Depok', [
                ['Situs Utama', 'https://www.ui.ac.id', LinkStatus::Verified],
                ['Penerimaan Mahasiswa Baru', 'https://penerimaan.ui.ac.id', LinkStatus::Pending],
            ]],
            ['Institut Teknologi Bandung', InstitutionType::Ptn, 'ID', 'Bandung', [
                ['Situs Utama', 'https://www.itb.ac.id', LinkStatus::Verified],
            ]],
            ['Universitas Gadjah Mada', InstitutionType::Ptn, 'ID', 'Yogyakarta', [
                ['Situs Utama', 'https://ugm.ac.id', LinkStatus::Verified],
            ]],
            ['UIN Syarif Hidayatullah Jakarta', InstitutionType::Ptn, 'ID', 'Tangerang Selatan', [
                ['Situs Utama', 'https://www.uinjkt.ac.id', LinkStatus::Verified],
            ]],
            ['Universitas Trisakti', InstitutionType::Pts, 'ID', 'Jakarta Barat', [
                ['Situs Utama', 'https://trisakti.ac.id', LinkStatus::Verified],
            ]],
            ['Politeknik Keuangan Negara STAN', InstitutionType::Kedinasan, 'ID', 'Tangerang Selatan', [
                ['Situs Utama', 'https://pknstan.ac.id', LinkStatus::Verified],
            ]],
            ['Politeknik Statistika STIS', InstitutionType::Kedinasan, 'ID', 'Jakarta Timur', [
                ['Situs Utama', 'https://www.stis.ac.id', LinkStatus::Verified],
            ]],
            ['Institut Pemerintahan Dalam Negeri', InstitutionType::Kedinasan, 'ID', 'Sumedang', [
                ['Situs Utama', 'https://www.ipdn.ac.id', LinkStatus::Verified],
            ]],
            ['Universitas Al-Azhar', InstitutionType::International, 'EG', 'Kairo', [
                ['Situs Utama', 'https://www.azhar.edu.eg', LinkStatus::Verified],
            ]],
            ['Comenius University Bratislava', InstitutionType::International, 'SK', 'Bratislava', [
                ['Situs Utama', 'https://uniba.sk', LinkStatus::Verified],
            ]],
        ];

        foreach ($data as [$name, $type, $iso2, $city, $links]) {
            $institution = Institution::updateOrCreate(
                ['name' => $name, 'country_id' => $countries[$iso2]],
                ['type' => $type, 'city' => $city],
            );

            foreach ($links as [$label, $url, $status]) {
                $link = OfficialLink::firstOrNew(['url' => $url]);
                $link->fill(['institution_id' => $institution->id, 'label' => $label]);
                $link->forceFill([
                    'status' => $status,
                    'verification_source' => $status === LinkStatus::Verified ? 'Data contoh pengembangan. Wajib diverifikasi ulang sebelum go-live.' : null,
                    'verified_by' => $status === LinkStatus::Verified ? $verifier?->id : null,
                    'verified_at' => $status === LinkStatus::Verified ? now() : null,
                ])->save();
            }
        }
    }
}
