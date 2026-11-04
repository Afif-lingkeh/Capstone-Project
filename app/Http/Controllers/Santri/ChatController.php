<?php
// app/Http/Controllers/Santri/ChatController.php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatAssistant;
use App\Services\CrisisResponder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

// Asisten AI dari sisi santri. Terkunci sampai persetujuan wali tercatat; dibatasi jumlah pesan per hari.
class ChatController extends Controller
{
    public function __construct(private ChatAssistant $assistant, private CrisisResponder $crisis) {}

    public function index(Request $request, ?ChatConversation $conversation = null): View
    {
        $user = $request->user();
        if ($conversation) {
            abort_unless($conversation->user_id === $user->id, 403);
        }
        $conversation ??= $user->chatConversations()->latest('updated_at')->first();

        return view('chat.index', [
            'hasConsent' => $user->hasGuardianConsent(),
            'configured' => $this->assistant->isConfigured(),
            'conversation' => $conversation?->load('messages'),
            'conversations' => $user->chatConversations()->latest('updated_at')->limit(15)->get(),
            'remaining' => $this->remaining($request),
            'onDuty' => $this->crisis->onDutyNames(),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasGuardianConsent(), 403, 'Asisten AI terbuka setelah persetujuan orang tua atau wali tercatat.');

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1500'],
            'conversation_id' => ['nullable', 'integer'],
        ]);

        if (! $this->assistant->isConfigured()) {
            return back()->with('warning', 'Asisten AI belum diaktifkan oleh pesantren.');
        }
        if ($this->remaining($request) <= 0) {
            return back()->withInput()->with('warning', 'Batas pertanyaan hari ini sudah tercapai. Silakan lanjutkan besok. Untuk keperluan mendesak, hubungi pembimbing atau konselor.');
        }

        $conversation = isset($data['conversation_id'])
            ? $user->chatConversations()->findOrFail($data['conversation_id'])
            : $user->chatConversations()->create(['title' => Str::limit($data['message'], 60)]);

        $question = $conversation->messages()->create(['role' => 'user', 'content' => $data['message']]);

        try {
            $reply = $this->assistant->reply($conversation, $user, $data['message']);
        } catch (RuntimeException $e) {
            $question->delete();

            return redirect()->route('chat.show', $conversation)->withInput()->with('error', $e->getMessage());
        }

        if ($reply['is_crisis']) {
            $question->update(['is_crisis' => true]);
        }
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $reply['text'],
            'links' => $reply['links'] ?: null,
            'is_crisis' => $reply['is_crisis'],
            'input_tokens' => $reply['input_tokens'],
            'output_tokens' => $reply['output_tokens'],
        ]);
        $conversation->touch();

        return redirect()->route('chat.show', $conversation)->with('crisis', $reply['is_crisis']);
    }

    public function rate(Request $request, ChatMessage $message): RedirectResponse
    {
        abort_unless($message->role === 'assistant' && $message->conversation->user_id === $request->user()->id, 403);

        $data = $request->validate(['rating' => ['required', 'integer', Rule::in([1, -1])]]);
        $message->update(['rating' => $message->rating === (int) $data['rating'] ? null : (int) $data['rating']]);

        return back();
    }

    private function remaining(Request $request): int
    {
        $sentToday = ChatMessage::where('role', 'user')
            ->whereHas('conversation', fn ($q) => $q->where('user_id', $request->user()->id))
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        return max(0, config('peduli.chat.daily_limit') - $sentToday);
    }
}
