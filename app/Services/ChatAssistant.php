<?php
// app/Services/ChatAssistant.php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Enums\LinkStatus;
use App\Models\Article;
use App\Models\ChatConversation;
use App\Models\OfficialLink;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use RuntimeException;

// Asisten AI santri (Claude API). Prinsip:
// - Rekomendasi tautan HANYA dari direktori terverifikasi (lewat tool), tidak pernah mengarang URL.
// - Bukan pemberi diagnosis; saat ada tanda bahaya, empatik dan mengarahkan ke konselor.
// - Tanda krisis dicek dulu dengan kata kunci (cepat & pasti), lalu model bisa memanggil notify_counselor sebagai jaring kedua.
class ChatAssistant
{
    private const MAX_TOOL_ROUNDS = 4;

    public function __construct(private CrisisResponder $crisis, private PathwayScorer $scorer) {}

    public function isConfigured(): bool
    {
        return filled(config('services.anthropic.key'));
    }

    /**
     * @return array{text: string, links: list<array{name: string, domain: string, url: string}>, is_crisis: bool, input_tokens: ?int, output_tokens: ?int}
     */
    public function reply(ChatConversation $conversation, User $santri, string $userText): array
    {
        if ($this->crisis->detect($userText)) {
            $this->crisis->raise($santri, 'chat', $conversation->id);

            return $this->crisisReply();
        }

        $client = new Client(apiKey: config('services.anthropic.key'));
        $messages = $this->history($conversation);
        $links = [];
        $isCrisis = false;
        $inputTokens = $outputTokens = 0;

        try {
            for ($round = 0; $round <= self::MAX_TOOL_ROUNDS; $round++) {
                // Fallback sisi server: bila model utama menolak karena kategori keamanan, API meneruskan ke model lain.
                $response = $client->beta->messages->create(
                    model: config('peduli.chat.model'),
                    maxTokens: 8000,
                    system: $this->systemPrompt($santri),
                    messages: $messages,
                    tools: $this->tools(),
                    outputConfig: ['effort' => config('peduli.chat.effort')],
                    betas: ['server-side-fallback-2026-07-01'],
                    fallbacks: 'default',
                );
                $inputTokens += $response->usage->inputTokens;
                $outputTokens += $response->usage->outputTokens;

                if ($response->stopReason === 'refusal') {
                    return $this->result('Maaf, aku tidak bisa membantu untuk pertanyaan itu. Kamu bisa menanyakannya langsung kepada pembimbing akademik atau konselor pesantren.', $links, $isCrisis, $inputTokens, $outputTokens);
                }

                if ($response->stopReason !== 'tool_use') {
                    $text = collect($response->content)->filter(fn ($b) => $b->type === 'text')->map(fn ($b) => $b->text)->implode("\n\n");

                    return $this->result(trim($text) ?: 'Maaf, aku belum bisa menjawab. Silakan ajukan pertanyaan dengan kalimat lain.', $links, $isCrisis, $inputTokens, $outputTokens);
                }

                // Jalankan tool lalu kirim hasilnya kembali. Isi respons asisten dikembalikan utuh (termasuk blok thinking).
                $toolResults = [];
                foreach ($response->content as $block) {
                    if ($block->type !== 'tool_use') {
                        continue;
                    }
                    $output = $this->runTool($block->name, (array) $block->input, $santri, $conversation, $links, $isCrisis);
                    $toolResults[] = ['type' => 'tool_result', 'toolUseID' => $block->id, 'content' => json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
                }
                $messages[] = ['role' => 'assistant', 'content' => $response->content];
                $messages[] = ['role' => 'user', 'content' => $toolResults];
            }
        } catch (RateLimitException) {
            throw new RuntimeException('Asisten AI sedang ramai dipakai. Silakan coba lagi dalam beberapa menit.');
        } catch (APIConnectionException|APIStatusException $e) {
            Log::warning('Asisten AI gagal', ['error' => $e->getMessage()]);
            throw new RuntimeException('Asisten AI sedang tidak dapat dihubungi. Silakan coba lagi nanti.');
        }

        return $this->result('Maaf, pertanyaannya cukup rumit. Silakan bagi menjadi beberapa pertanyaan yang lebih sederhana.', $links, $isCrisis, $inputTokens, $outputTokens);
    }

    /** Riwayat percakapan (hanya teks) sebagai konteks; pesan krisis tidak dikirim ulang ke model. */
    private function history(ChatConversation $conversation): array
    {
        return $conversation->messages()
            ->where('is_crisis', false)
            ->latest('id')
            ->limit(config('peduli.chat.history_turns'))
            ->get()
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();
    }

    private function systemPrompt(User $santri): array
    {
        $stable = <<<'PROMPT'
        Kamu adalah Asisten PEDULI, asisten Smart Career Center di sebuah pesantren modern di Indonesia.
        Penggunamu santri SMP–SMA, kebanyakan berusia di bawah 18 tahun. Bantu mereka memahami pilihan kuliah, jurusan,
        jalur masuk (SNBP, SNBT, mandiri, beasiswa, kedinasan, kuliah di luar negeri), program kerja/magang luar negeri mitra pesantren,
        dan cara membaca hasil tes potensi (Saintek, Soshum, Bahasa, Keagamaan).

        Cara menjawab:
        - Bahasa Indonesia yang hangat, sopan, dan sederhana, sesuai adab pesantren. Boleh menyapa dengan "kamu".
        - Ringkas: 2–5 paragraf pendek atau daftar singkat. Tanpa tabel dan tanpa judul besar.
        - Jika menyebut situs kampus atau pendaftaran, panggil search_official_links dan sebutkan HANYA domain dari hasilnya.
          Jangan pernah menulis URL atau domain dari ingatanmu sendiri. Jika tidak ditemukan, katakan belum ada di direktori
          dan sarankan bertanya ke pembimbing akademik.
        - Untuk artikel panduan atau program luar negeri pesantren, panggil search_peduli_content.
        - Jadwal dan syarat pendaftaran bisa berubah: ingatkan untuk mencocokkan dengan sumber resmi.
        - Kamu bukan psikolog atau dokter. Jangan memberi diagnosis. Hasil tes adalah gambaran kecocokan, bukan batasan.
        - Jangan meminta atau menyimpan data pribadi (alamat, nomor HP, password).
        - Jika santri bercerita tentang perasaan berat, empatilah dan sarankan menemui konselor pesantren lewat menu Konseling.
          Jika ada tanda bahaya (ingin menyakiti diri, tidak ingin hidup, kekerasan, pelecehan), panggil notify_counselor,
          lalu jawab dengan empati, sarankan segera menemui ustadz/ustadzah atau musyrif/musyrifah terdekat.
        - Untuk keputusan besar, ajak santri bermusyawarah dengan pembimbing akademik dan orang tua.
        PROMPT;

        $profile = $this->scorer->profileFor($santri);
        $sp = $santri->santriProfile;
        $context = 'Konteks santri: '.($sp ? $sp->level->label().' kelas '.$sp->class_name : 'jenjang belum diisi').'.';
        if ($profile) {
            $context .= ' Profil tes potensi terakhir: '.collect($profile)->filter(fn ($v) => $v !== null)->map(fn ($v, $k) => ucfirst($k).' '.$v.'%')->implode(', ').'.';
        }

        return [
            ['type' => 'text', 'text' => $stable],
            ['type' => 'text', 'text' => $context],
        ];
    }

    private function tools(): array
    {
        return [
            [
                'name' => 'search_official_links',
                'description' => 'Cari situs resmi kampus atau sekolah kedinasan di direktori PEDULI yang sudah diverifikasi pesantren. Gunakan setiap kali akan menyebut situs/domain kampus.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => ['query' => ['type' => 'string', 'description' => 'Nama kampus, singkatan, kota, atau negara, mis. "UGM" atau "Al-Azhar"']],
                    'required' => ['query'],
                ],
            ],
            [
                'name' => 'search_peduli_content',
                'description' => 'Cari artikel panduan (SNBP, SNBT, mandiri, beasiswa, luar negeri) dan program kerja/magang luar negeri mitra pesantren.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => ['query' => ['type' => 'string', 'description' => 'Kata kunci, mis. "beasiswa" atau "Jepang"']],
                    'required' => ['query'],
                ],
            ],
            [
                'name' => 'notify_counselor',
                'description' => 'Beri tahu konselor piket pesantren bahwa santri ini mungkin dalam bahaya atau krisis (ingin menyakiti diri, tidak ingin hidup, kekerasan, pelecehan). Jangan dipakai untuk kegalauan biasa.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => ['category' => ['type' => 'string', 'enum' => ['self_harm', 'abuse', 'other_danger']]],
                    'required' => ['category'],
                ],
            ],
        ];
    }

    private function runTool(string $name, array $input, User $santri, ChatConversation $conversation, array &$links, bool &$isCrisis): array
    {
        $query = trim((string) ($input['query'] ?? ''));
        $term = '%'.$query.'%';

        return match ($name) {
            'search_official_links' => (function () use ($term, &$links) {
                $found = OfficialLink::where('status', LinkStatus::Verified)
                    ->where(fn (Builder $q) => $q->where('domain', 'like', $term)->orWhere('label', 'like', $term)
                        ->orWhereHas('institution', fn (Builder $q) => $q->where('name', 'like', $term)->orWhere('city', 'like', $term)
                            ->orWhereHas('country', fn (Builder $q) => $q->where('name', 'like', $term))))
                    ->with('institution')
                    ->limit(5)
                    ->get()
                    ->map(fn (OfficialLink $l) => ['name' => $l->institution->name, 'label' => $l->label, 'domain' => $l->domain, 'url' => $l->url])
                    ->all();
                foreach ($found as $item) {
                    $links[$item['url']] = ['name' => $item['name'], 'domain' => $item['domain'], 'url' => $item['url']];
                }

                return ['results' => $found, 'note' => $found ? 'Hanya tautan ini yang boleh disebut.' : 'Tidak ada di direktori terverifikasi.'];
            })(),
            'search_peduli_content' => [
                'articles' => Article::published()->where(fn (Builder $q) => $q->where('title', 'like', $term)->orWhere('excerpt', 'like', $term))
                    ->limit(4)->get()->map(fn (Article $a) => ['title' => $a->title, 'summary' => $a->excerpt, 'category' => $a->category->label()])->all(),
                'programs' => Program::published()->with(['country', 'partner'])
                    ->where(fn (Builder $q) => $q->where('title', 'like', $term)->orWhere('summary', 'like', $term)->orWhereHas('country', fn (Builder $q) => $q->where('name', 'like', $term)))
                    ->limit(4)->get()->map(fn (Program $p) => ['title' => $p->title, 'country' => $p->country->name, 'partner' => $p->partner->name, 'open' => $p->acceptsInterest(), 'deadline' => $p->deadline?->toDateString()])->all(),
                'note' => 'Arahkan santri membuka menu Info Kampus atau Kerja & Magang di PEDULI untuk detailnya.',
            ],
            'notify_counselor' => (function () use ($santri, $conversation, &$isCrisis) {
                if (! $isCrisis) {
                    $this->crisis->raise($santri, 'chat', $conversation->id);
                    $isCrisis = true;
                }

                return ['status' => 'Konselor sudah diberi tahu.'];
            })(),
            default => ['error' => 'Tool tidak dikenal.'],
        };
    }

    private function crisisReply(): array
    {
        return $this->result(
            "Terima kasih sudah mau bercerita. Kamu tidak sendirian, dan perasaanmu penting.\n\n".
            "Aku sudah memberi tahu konselor pesantren supaya bisa segera menemuimu. Kalau kamu merasa tidak aman sekarang, ".
            "tolong segera temui ustadz/ustadzah, musyrif/musyrifah, atau orang dewasa yang kamu percaya di dekatmu.",
            [], true, null, null,
        );
    }

    private function result(string $text, array $links, bool $isCrisis, ?int $in, ?int $out): array
    {
        return ['text' => $text, 'links' => array_values($links), 'is_crisis' => $isCrisis, 'input_tokens' => $in, 'output_tokens' => $out];
    }
}
