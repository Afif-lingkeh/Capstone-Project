<?php
// app/Http/Controllers/Santri/TestController.php

namespace App\Http\Controllers\Santri;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Models\Major;
use App\Models\PotentialTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Services\PathwayScorer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Tes potensi dari sisi santri: daftar tes, mengerjakan (tersimpan otomatis), dan hasil profil 4 jalur.
class TestController extends Controller
{
    public function __construct(private PathwayScorer $scorer) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $tests = PotentialTest::active()->withCount('questions')->get();
        $attempts = $user->testAttempts()->withCount('answers')->latest()->get()->groupBy('potential_test_id');
        $profile = $this->scorer->profileFor($user);

        return view('tests.index', [
            'tests' => $tests,
            'attempts' => $attempts,
            'profile' => $profile,
            'headline' => $profile ? $this->scorer->headline($profile) : null,
            'history' => $user->testAttempts()->whereNotNull('submitted_at')->with('test')->latest('submitted_at')->get(),
        ]);
    }

    public function start(Request $request, PotentialTest $test): RedirectResponse
    {
        abort_unless($test->is_active, 404);

        $attempt = $request->user()->testAttempts()->firstOrCreate(
            ['potential_test_id' => $test->id, 'submitted_at' => null],
        );

        return redirect()->route('attempts.show', $attempt);
    }

    public function show(Request $request, TestAttempt $attempt): View|RedirectResponse
    {
        $this->authorizeOwner($request, $attempt);
        if ($attempt->isSubmitted()) {
            return redirect()->route('attempts.result', $attempt);
        }

        $attempt->load(['test.questions.options', 'answers']);

        return view('tests.take', [
            'attempt' => $attempt,
            'questions' => $attempt->test->questions,
            'answers' => $attempt->answers->keyBy('question_id'),
            'likert' => QuestionType::likertScale(),
        ]);
    }

    // Simpan satu jawaban (dipanggil otomatis setiap kali santri memilih).
    public function answer(Request $request, TestAttempt $attempt): JsonResponse
    {
        $this->authorizeOwner($request, $attempt);
        abort_if($attempt->isSubmitted(), 409, 'Tes sudah dikumpulkan.');

        $question = $attempt->test->questions()->findOrFail($request->integer('question_id'));
        $this->storeAnswer($attempt, $question, $request->input('answer'));

        return response()->json(['saved_at' => now()->format('H.i'), 'answered' => $attempt->answers()->count()]);
    }

    public function submit(Request $request, TestAttempt $attempt): RedirectResponse
    {
        $this->authorizeOwner($request, $attempt);
        if ($attempt->isSubmitted()) {
            return redirect()->route('attempts.result', $attempt);
        }

        $questions = $attempt->test->questions()->with('options')->get();
        foreach ((array) $request->input('answers', []) as $questionId => $value) {
            if ($question = $questions->firstWhere('id', (int) $questionId)) {
                $this->storeAnswer($attempt, $question, $value);
            }
        }

        $missing = $questions->count() - $attempt->answers()->count();
        if ($missing > 0) {
            return redirect()->route('attempts.show', $attempt)->with('warning', "Masih ada {$missing} soal yang belum dijawab. Jawabanmu yang lain sudah tersimpan.");
        }

        $attempt->update(['submitted_at' => now(), 'scores' => $this->scorer->score($attempt->fresh())]);

        return redirect()->route('attempts.result', $attempt)->with('success', 'Alhamdulillah, tes selesai. Ini profil jalurmu.');
    }

    public function result(Request $request, TestAttempt $attempt): View
    {
        $this->authorizeOwner($request, $attempt);
        abort_unless($attempt->isSubmitted(), 404);

        $top = $this->scorer->topPathways($attempt->scores);

        return view('tests.result', [
            'attempt' => $attempt->load('test'),
            'headline' => $this->scorer->headline($attempt->scores),
            'majors' => Major::whereIn('pathway', $top)->orderBy('name')->get()->groupBy(fn (Major $m) => $m->pathway->value),
            'top' => $top,
        ]);
    }

    private function storeAnswer(TestAttempt $attempt, Question $question, mixed $value): void
    {
        if ($question->type === QuestionType::Likert) {
            validator(['v' => $value], ['v' => ['required', 'integer', 'between:1,5']])->validate();
            $data = ['value' => (int) $value, 'question_option_id' => null];
        } else {
            validator(['v' => $value], ['v' => ['required', 'integer', Rule::in($question->options()->pluck('id'))]])->validate();
            $data = ['question_option_id' => (int) $value, 'value' => null];
        }

        $attempt->answers()->updateOrCreate(['question_id' => $question->id], $data);
        $attempt->touch();
    }

    private function authorizeOwner(Request $request, TestAttempt $attempt): void
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
    }
}
