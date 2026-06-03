<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes API de l'application Bosten.
| Protégées par le middleware 'auth' (session) — compatible avec l'interface
| web existante (pas de Sanctum requis).
|
*/

Route::middleware('auth')->group(function () {
    Route::post('/chatbot', [ChatbotController::class, 'ask'])->name('api.chatbot.ask');
});
