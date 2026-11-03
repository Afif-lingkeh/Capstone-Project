<?php
// app/Http/Controllers/Admin/MajorController.php

namespace App\Http\Controllers\Admin;

use App\Enums\Pathway;
use App\Http\Controllers\Controller;
use App\Models\Major;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Daftar jurusan per jalur, dipakai rekomendasi hasil tes potensi.
class MajorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:articles.manage')];
    }

    public function index(Request $request): View
    {
        return view('admin.majors.index', [
            'majors' => Major::orderBy('pathway')->orderBy('name')->get()->groupBy(fn (Major $m) => $m->pathway->value),
            'pathways' => Pathway::cases(),
            'editing' => $request->integer('ubah') ? Major::find($request->integer('ubah')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $major = Major::create($this->validated($request));

        return redirect()->route('admin.majors.index')->with('success', "Jurusan {$major->name} ditambahkan.");
    }

    public function update(Request $request, Major $major): RedirectResponse
    {
        $major->update($this->validated($request, $major));

        return redirect()->route('admin.majors.index')->with('success', "Jurusan {$major->name} diperbarui.");
    }

    public function destroy(Major $major): RedirectResponse
    {
        $major->delete();

        return redirect()->route('admin.majors.index')->with('success', 'Jurusan dihapus.');
    }

    private function validated(Request $request, ?Major $major = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('majors')->where('pathway', $request->input('pathway'))->ignore($major?->id)],
            'pathway' => ['required', Rule::enum(Pathway::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'career_prospects' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
