<?php
// app/Services/PathwayScorer.php

namespace App\Services;

use App\Enums\Pathway;
use App\Enums\QuestionType;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

// Menghitung profil skor 4 jalur. Hasil selalu berupa persentase tiap jalur (bukan satu label),
// relatif terhadap skor maksimum yang mungkin dicapai di tes itu.
class PathwayScorer
{
    /** @return array<string, int|null> mis. ['saintek' => 82, 'soshum' => 58, 'bahasa' => 74, 'keagamaan' => null] */
    public function score(TestAttempt $attempt): array
    {
        $attempt->loadMissing(['test.questions.options', 'answers']);
        $answers = $attempt->answers->keyBy('question_id');

        $raw = $max = array_fill_keys(array_column(Pathway::cases(), 'value'), 0);

        foreach ($attempt->test->questions as $question) {
            $answer = $answers->get($question->id);

            foreach (array_keys($raw) as $pathway) {
                if ($question->type === QuestionType::Likert) {
                    if ($question->pathway?->value !== $pathway) {
                        continue;
                    }
                    $max[$pathway] += 4;
                    $raw[$pathway] += $answer?->value ? $answer->value - 1 : 0;
                } else {
                    $max[$pathway] += $question->options->max(fn ($o) => $o->weightFor($pathway)) ?? 0;
                    $chosen = $question->options->firstWhere('id', $answer?->question_option_id);
                    $raw[$pathway] += $chosen?->weightFor($pathway) ?? 0;
                }
            }
        }

        return collect($raw)
            ->map(fn (int $value, string $pathway) => $max[$pathway] > 0 ? (int) round($value / $max[$pathway] * 100) : null)
            ->all();
    }

    /**
     * Profil gabungan santri: rata-rata skor dari percobaan terakhir di setiap tes yang sudah dikumpulkan.
     *
     * @return array<string, int|null>|null
     */
    public function profileFor(User $user): ?array
    {
        $latest = $this->latestAttempts($user);
        if ($latest->isEmpty()) {
            return null;
        }

        return collect(Pathway::cases())->mapWithKeys(function (Pathway $p) use ($latest) {
            $values = $latest->map(fn (TestAttempt $a) => $a->scores[$p->value] ?? null)->filter(fn ($v) => $v !== null);

            return [$p->value => $values->isEmpty() ? null : (int) round($values->avg())];
        })->all();
    }

    /** @return Collection<int, TestAttempt> */
    public function latestAttempts(User $user): Collection
    {
        return $user->testAttempts()
            ->whereNotNull('submitted_at')
            ->with('test')
            ->latest('submitted_at')
            ->get()
            ->unique('potential_test_id')
            ->values();
    }

    /** Jalur dengan skor tertinggi (maks. 2) untuk rekomendasi. @return list<Pathway> */
    public function topPathways(array $profile, int $limit = 2): array
    {
        return collect($profile)->filter(fn ($v) => $v !== null)->sortDesc()->keys()->take($limit)
            ->map(fn (string $p) => Pathway::from($p))->values()->all();
    }

    /** Kalimat ringkas, mis. "Kuat di Saintek dan Bahasa". */
    public function headline(array $profile): string
    {
        $top = $this->topPathways($profile);

        return match (count($top)) {
            0 => 'Profil belum lengkap',
            1 => 'Kuat di '.$top[0]->label(),
            default => 'Kuat di '.$top[0]->label().' dan '.$top[1]->label(),
        };
    }
}
