<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\GeminiChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(private GeminiChatbotService $chatbotService) {}

    /**
     * Affiche la page chatbot avec l'historique de l'utilisateur.
     */
    public function index()
    {
        $history = auth()->user()
            ->conversations()
            ->latest()
            ->limit(20)
            ->get()
            ->reverse();

        return view('chatbot.index', compact('history'));
    }

    /**
     * Traite un message et retourne la réponse du chatbot.
     */
    public function ask(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $reply = $this->chatbotService->ask(auth()->user(), $request->message);

        $conversation = Conversation::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'reply'   => $reply,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'reply'    => $reply,
                'saved_at' => $conversation->created_at->toIso8601String(),
            ]);
        }

        return back()->with('chatbot_reply', $reply);
    }

    /**
     * Efface l'historique de conversation de l'utilisateur.
     */
    public function clear()
    {
        auth()->user()->conversations()->delete();
        return response()->json(['status' => 'cleared']);
    }
}
