<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Support\Facades\Http;

class ChatbotService
{
    private string $apiKey;

    private string $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.claude.key');
    }

    public function ask(User $user, string $message): string
    {
        // Étape 3 : récupérer les données métier pertinentes
        $context = $this->buildBusinessContext($user, $message);

        // Étape 4 : construire le prompt intelligent
        $systemPrompt = $this->buildSystemPrompt($context);

        // Historique de conversation (5 derniers échanges)
        $history = Conversation::where('user_id', $user->id)
            ->latest()->limit(5)->get()->reverse()
            ->flatMap(fn ($c) => [
                ['role' => 'user', 'content' => $c->message],
                ['role' => 'assistant', 'content' => $c->reply],
            ])->values()->toArray();

        $messages = array_merge($history, [['role' => 'user', 'content' => $message]]);

        // Étape 5 : appel à l'API
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->apiUrl, [
                'model' => 'claude-opus-4-6',
                'max_tokens' => 1024,
                'system' => $systemPrompt,
                'messages' => $messages,
            ]);

            if ($response->failed()) {
                throw new \Exception('API indisponible : '.$response->status());
            }

            return $response->json('content.0.text');

        } catch (\Exception $e) {
            return 'Désolé, je rencontre un problème technique. Veuillez réessayer dans quelques instants.';
        }
    }

    private function buildBusinessContext(User $user, string $message): array
    {
        // Déterminer ce qui est pertinent selon la question
        $context = ['user_name' => $user->name];

        // Produits disponibles
        $context['products_in_stock'] = Product::where('stock', '>', 0)
            ->select('name', 'price', 'stock')->limit(20)->get()->toArray();

        // Commandes récentes de l'utilisateur
        $context['recent_orders'] = $user->orders()->with('items.product')
            ->latest()->limit(5)->get()->map(fn ($o) => [
                'id' => $o->id,
                'total' => $o->total,
                'status' => $o->status,
                'date' => $o->created_at->format('d/m/Y'),
                'items' => $o->items->map(fn ($i) => $i->product->name.' x'.$i->quantity),
            ])->toArray();

        // Best sellers
        $context['best_sellers'] = app(ProductService::class)->getBestSellers(3)
            ->map(fn ($p) => $p->name.' ('.$p->price.' TND)')->toArray();

        return $context;
    }

    private function buildSystemPrompt(array $context): string
    {
        return "Tu es Basma, l'assistante virtuelle de Bosten, une boutique en ligne tunisienne spécialisée dans les plantes et outils de jardinage.

Tu réponds en français (ou en arabe si l'utilisateur écrit en arabe), avec un ton chaleureux et professionnel.

DONNÉES MÉTIER ACTUELLES :
- Client : {$context['user_name']}
- Produits en stock : ".json_encode($context['products_in_stock'], JSON_UNESCAPED_UNICODE).'
- Commandes récentes du client : '.json_encode($context['recent_orders'], JSON_UNESCAPED_UNICODE).'
- Produits les plus vendus : '.implode(', ', $context['best_sellers'])."

RÈGLES :
- Réponds uniquement en te basant sur les données ci-dessus
- Pour les prix, utilise toujours 'TND' (dinar tunisien)
- Si une question dépasse le cadre de Bosten, redirige poliment vers le support
- Ne réponds jamais à des questions hors contexte jardinage/commerce";
    }
}
