<?php
// app/Http/Controllers/Admin/ChatLogController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

// Evaluasi jawaban Asisten AI (keputusan Fase 1 no. 8): hanya Super Admin; percakapan bertanda krisis disembunyikan.
class ChatLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:super-admin')];
    }

    public function index(Request $request): View
    {
        $answers = ChatMessage::where('role', 'assistant')
            ->where('is_crisis', false)
            ->whereHas('conversation', fn ($q) => $q->whereDoesntHave('messages', fn ($q) => $q->where('is_crisis', true)))
            ->when($request->query('nilai') === 'buruk', fn ($q) => $q->where('rating', -1))
            ->when($request->query('nilai') === 'baik', fn ($q) => $q->where('rating', 1))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // Pertanyaan santri sebelum setiap jawaban (tanpa identitas santri).
        $questions = ChatMessage::where('role', 'user')
            ->whereIn('chat_conversation_id', $answers->pluck('chat_conversation_id'))
            ->get()
            ->groupBy('chat_conversation_id');

        return view('admin.chat-logs.index', [
            'answers' => $answers,
            'questions' => $questions,
            'stats' => [
                'total' => ChatMessage::where('role', 'assistant')->count(),
                'good' => ChatMessage::where('rating', 1)->count(),
                'bad' => ChatMessage::where('rating', -1)->count(),
                'tokens' => (int) ChatMessage::sum('output_tokens') + (int) ChatMessage::sum('input_tokens'),
            ],
        ]);
    }
}
