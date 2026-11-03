<?php
// app/Http/Controllers/Admin/PotentialTestController.php

namespace App\Http\Controllers\Admin;

use App\Enums\Pathway;
use App\Enums\QuestionType;
use App\Enums\TestDimension;
use App\Http\Controllers\Controller;
use App\Models\PotentialTest;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Bank soal: tes per dimensi, soal pilihan ganda (bobot per jalur) dan Likert (satu jalur per pernyataan).
class PotentialTestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:tests.manage')];
    }

    public function index(): View
    {
        return view('admin.tests.index', [
            'tests' => PotentialTest::withCount(['questions', 'attempts as submitted_count' => fn ($q) => $q->whereNotNull('submitted_at')])->orderBy('dimension')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tests.form', ['test' => new PotentialTest(['estimated_minutes' => 15]), 'dimensions' => TestDimension::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $test = PotentialTest::create($this->validatedTest($request));

        return redirect()->route('admin.tests.edit', $test)->with('success', 'Tes dibuat. Sekarang tambahkan soalnya.');
    }

    public function edit(PotentialTest $test): View
    {
        return view('admin.tests.form', [
            'test' => $test->load('questions.options'),
            'dimensions' => TestDimension::cases(),
            'pathways' => Pathway::cases(),
        ]);
    }

    public function update(Request $request, PotentialTest $test): RedirectResponse
    {
        $data = $this->validatedTest($request);
        if ($data['is_active'] && $test->questions()->doesntExist()) {
            return back()->withInput()->with('warning', 'Tambahkan minimal satu soal sebelum mengaktifkan tes.');
        }

        $test->update($data);

        return back()->with('success', 'Tes diperbarui.');
    }

    public function destroy(PotentialTest $test): RedirectResponse
    {
        if ($test->attempts()->exists()) {
            return back()->with('error', 'Tes yang sudah dikerjakan santri tidak dapat dihapus. Nonaktifkan saja agar riwayat hasil tetap utuh.');
        }

        $test->delete();

        return redirect()->route('admin.tests.index')->with('success', 'Tes dihapus.');
    }

    public function createQuestion(Request $request, PotentialTest $test): View
    {
        return view('admin.tests.question', [
            'test' => $test,
            'question' => new Question(['type' => QuestionType::tryFrom((string) $request->query('jenis')) ?? QuestionType::Likert]),
            'pathways' => Pathway::cases(),
        ]);
    }

    public function storeQuestion(Request $request, PotentialTest $test): RedirectResponse
    {
        [$data, $options] = $this->validatedQuestion($request);

        DB::transaction(function () use ($test, $data, $options) {
            $question = $test->questions()->create([...$data, 'position' => ($test->questions()->max('position') ?? 0) + 1]);
            $question->options()->createMany($options);
        });

        return redirect()->route('admin.tests.edit', $test)->with('success', 'Soal ditambahkan.');
    }

    public function editQuestion(PotentialTest $test, Question $question): View
    {
        abort_unless($question->potential_test_id === $test->id, 404);

        return view('admin.tests.question', ['test' => $test, 'question' => $question->load('options'), 'pathways' => Pathway::cases()]);
    }

    public function updateQuestion(Request $request, PotentialTest $test, Question $question): RedirectResponse
    {
        abort_unless($question->potential_test_id === $test->id, 404);
        [$data, $options] = $this->validatedQuestion($request);

        DB::transaction(function () use ($question, $data, $options) {
            $question->update($data);
            $question->options()->delete();
            $question->options()->createMany($options);
        });

        return redirect()->route('admin.tests.edit', $test)->with('success', 'Soal diperbarui. Hasil tes yang sudah dikumpulkan tidak berubah.');
    }

    public function destroyQuestion(PotentialTest $test, Question $question): RedirectResponse
    {
        abort_unless($question->potential_test_id === $test->id, 404);
        $question->delete();

        return back()->with('success', 'Soal dihapus.');
    }

    private function validatedTest(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'dimension' => ['required', Rule::enum(TestDimension::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** @return array{0: array, 1: list<array>} */
    private function validatedQuestion(Request $request): array
    {
        $pathways = array_column(Pathway::cases(), 'value');
        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'pathway' => ['nullable', 'required_if:type,likert', Rule::enum(Pathway::class)],
            'options' => ['exclude_unless:type,choice', 'required', 'array', 'min:2', 'max:6'],
            'options.*.label' => ['exclude_unless:type,choice', 'required', 'string', 'max:255'],
            'options.*.weights' => ['exclude_unless:type,choice', 'array'],
            'options.*.weights.*' => ['exclude_unless:type,choice', 'nullable', 'integer', 'min:0', 'max:5'],
        ], ['pathway.required_if' => 'Pilih jalur yang diukur pernyataan ini.']);

        $options = collect($data['options'] ?? [])->values()->map(fn (array $o, int $i) => [
            'label' => $o['label'],
            'weights' => collect($pathways)->mapWithKeys(fn ($p) => [$p => (int) ($o['weights'][$p] ?? 0)])->all(),
            'position' => $i,
        ])->all();

        $question = ['body' => $data['body'], 'type' => $data['type'], 'pathway' => $data['type'] === 'likert' ? $data['pathway'] : null];

        return [$question, $options];
    }
}
