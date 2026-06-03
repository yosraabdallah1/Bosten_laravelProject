<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(private ChatbotService $chatbotService) {}

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

    public function ask(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $reply = $this->chatbotService->ask(auth()->user(), $request->message);

        Conversation::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'reply'   => $reply,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['reply' => $reply]);
        }

        return back()->with('chatbot_reply', $reply);
    }
}
