<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    
    public function index() {
    $history = auth()->user()->conversations()->latest()->limit(20)->get()->reverse();
    return view('chatbot.index', compact('history'));
}

public function ask(Request $request) {
    $request->validate(['message' => 'required|string|max:500']);

    $reply = app(ChatbotService::class)->ask(auth()->user(), $request->message);

    // Sauvegarder en base
    Conversation::create([
        'user_id' => auth()->id(),
        'message' => $request->message,
        'reply' => $reply,
    ]);

    if ($request->expectsJson()) {
        return response()->json(['reply' => $reply]);
    }

    return back()->with('chatbot_reply', $reply);
}
}
