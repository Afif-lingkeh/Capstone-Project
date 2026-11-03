<?php
// app/Http/Controllers/Admin/SantriImportController.php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\SantriLevel;
use App\Http\Controllers\Controller;
use App\Models\SantriProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Impor akun santri dari CSV (Fase 10). Setiap santri mendapat password acak yang ditampilkan SEKALI untuk dibagikan.
// Kolom: nis, nama, jenjang (smp/sma), kelas, jenis_kelamin (L/P), tahun_masuk, nama_wali (opsional).
class SantriImportController extends Controller implements HasMiddleware
{
    public const COLUMNS = ['nis', 'nama', 'jenjang', 'kelas', 'jenis_kelamin', 'tahun_masuk', 'nama_wali'];

    public static function middleware(): array
    {
        return [new Middleware('permission:users.manage')];
    }

    public function create(): View
    {
        return view('admin.users.import', ['columns' => self::COLUMNS]);
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, self::COLUMNS);
            fputcsv($out, ['20250002', 'Ahmad Fauzi', 'sma', '10A', 'L', now()->year, 'Bapak Fauzan']);
            fclose($out);
        }, 'templat-impor-santri.csv', ['Content-Type' => 'text/csv']);
    }

    public function store(Request $request): View
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $rows = $this->readCsv($request->file('file')->getRealPath());
        $created = [];
        $skipped = [];

        foreach ($rows as $line => $row) {
            $validator = Validator::make($row, [
                'nis' => ['required', 'string', 'max:30', Rule::unique(SantriProfile::class, 'nis'), Rule::unique(User::class, 'username')],
                'nama' => ['required', 'string', 'max:255'],
                'jenjang' => ['required', Rule::enum(SantriLevel::class)],
                'kelas' => ['required', 'string', 'max:30'],
                'jenis_kelamin' => ['required', Rule::enum(Gender::class)],
                'tahun_masuk' => ['required', 'integer', 'digits:4', 'max:'.(now()->year + 1)],
                'nama_wali' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $skipped[] = ['line' => $line, 'nis' => $row['nis'] ?? '—', 'message' => $validator->errors()->first()];

                continue;
            }

            $password = Str::password(10, symbols: false);
            DB::transaction(function () use ($row, $password) {
                $user = User::create([
                    'name' => $row['nama'], 'username' => $row['nis'], 'email' => null,
                    'password' => $password, 'gender' => $row['jenis_kelamin'], 'is_active' => true,
                ]);
                $user->assignRole(Role::Santri->value);
                $user->santriProfile()->create([
                    'nis' => $row['nis'], 'level' => $row['jenjang'], 'class_name' => $row['kelas'],
                    'entry_year' => $row['tahun_masuk'], 'guardian_name' => $row['nama_wali'] ?: null,
                ]);
            });
            $created[] = ['nis' => $row['nis'], 'name' => $row['nama'], 'class' => strtoupper($row['jenjang']).' '.$row['kelas'], 'password' => $password];
        }

        return view('admin.users.import-result', ['created' => $created, 'skipped' => $skipped]);
    }

    /** @return array<int, array<string, string>> nomor baris CSV => data per kolom */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $first = fgets($handle);
        // Excel Indonesia sering menyimpan CSV dengan pemisah titik koma.
        $delimiter = substr_count((string) $first, ';') > substr_count((string) $first, ',') ? ';' : ',';
        $header = array_map(fn ($h) => Str::of($h)->replace("\u{FEFF}", '')->trim()->lower()->snake()->toString(), str_getcsv(trim((string) $first), $delimiter));

        $rows = [];
        $line = 1;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $row = [];
            foreach (self::COLUMNS as $column) {
                $index = array_search($column, $header, true);
                $row[$column] = $index === false ? '' : trim((string) ($data[$index] ?? ''));
            }
            $row['jenjang'] = strtolower($row['jenjang']);
            $row['jenis_kelamin'] = strtoupper($row['jenis_kelamin']);
            $rows[$line] = $row;
        }
        fclose($handle);

        return $rows;
    }
}
