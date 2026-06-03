<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class ChatbotService
{
    // Gemini API (déjà configurée dans .env)
    private string $geminiKey;
    private string $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->geminiKey = env('GEMINI_API_KEY', '');
    }

    public function ask(User $user, string $message): string
    {
        $context      = $this->buildBusinessContext($user);
        $systemPrompt = $this->buildSystemPrompt($context);

        // Historique (5 derniers échanges)
        $history = Conversation::where('user_id', $user->id)
            ->latest()->limit(5)->get()->reverse();

        // Construire le contenu pour Gemini
        $contents = [];

        // Injecter le system prompt comme premier message user/model
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]],
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => "Compris ! Je suis Basma, l'assistante de Bosten. Comment puis-je vous aider ?"]],
        ];

        // Historique
        foreach ($history as $conv) {
            $contents[] = ['role' => 'user',  'parts' => [['text' => $conv->message]]];
            $contents[] = ['role' => 'model', 'parts' => [['text' => $conv->reply]]];
        }

        // Message actuel
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        try {
            $response = Http::timeout(30)
                ->post("{$this->geminiUrl}?key={$this->geminiKey}", [
                    'contents' => $contents,
                    'generationConfig' => [
                        'maxOutputTokens' => 512,
                        'temperature'     => 0.7,
                    ],
                ]);

            if ($response->failed()) {
                throw new \Exception('Gemini API error: ' . $response->status());
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if (empty($text)) {
                throw new \Exception('Réponse vide de l\'API');
            }

            return $text;

        } catch (\Exception $e) {
            // Fallback : réponses intelligentes locales
            return $this->localFallback($message, $context);
        }
    }

    private function buildBusinessContext(User $user): array
    {
        $products = Product::where('stock', '>', 0)
            ->where('is_active', true)
            ->with('category')
            ->select('name', 'price', 'stock', 'category_id', 'description')
            ->limit(30)
            ->get();

        $orders = $user->orders()
            ->with('items.product')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($o) => [
                'id'     => $o->id,
                'total'  => $o->total,
                'status' => $o->status,
                'date'   => $o->created_at->format('d/m/Y'),
                'items'  => $o->items->map(fn ($i) => ($i->product->name ?? '?') . ' x' . $i->quantity)->join(', '),
            ]);

        return [
            'user_name'        => $user->name,
            'products'         => $products,
            'recent_orders'    => $orders,
        ];
    }

    private function buildSystemPrompt(array $context): string
    {
        $productsText = $context['products']->map(fn ($p) =>
            "- {$p->name} : {$p->price} TND (stock: {$p->stock})"
        )->join("\n");

        $ordersText = $context['recent_orders']->isEmpty()
            ? 'Aucune commande.'
            : $context['recent_orders']->map(fn ($o) =>
                "- Commande #{$o['id']} du {$o['date']} — {$o['total']} TND — statut: {$o['status']} — articles: {$o['items']}"
              )->join("\n");

        return <<<PROMPT
Tu es Basma 🌿, l'assistante virtuelle de Bosten, une boutique tunisienne en ligne spécialisée dans les plantes et le jardinage.

PROFIL CLIENT :
Nom : {$context['user_name']}

CATALOGUE DISPONIBLE :
{$productsText}

COMMANDES RÉCENTES DU CLIENT :
{$ordersText}

INSTRUCTIONS :
- Réponds en français (ou en arabe si le client écrit en arabe).
- Ton : chaleureux, naturel, professionnel. Utilise des emojis avec modération.
- Pour les recommandations, base-toi uniquement sur les produits listés ci-dessus.
- Pour les prix, toujours utiliser "TND".
- Si on te demande le statut d'une commande, réfère-toi aux commandes ci-dessus.
- Si la question sort du cadre de Bosten (jardinage, plantes, commandes), réponds poliment que tu n'es pas en mesure d'aider sur ce sujet et redirige vers support@bosten.tn.
- Sois concis (3-4 phrases maximum sauf si une liste est nécessaire).
PROMPT;
    }

    /**
     * Réponses locales si l'API est indisponible.
     */
    private function localFallback(string $message, array $context): string
    {
        $msg = mb_strtolower($message);

        if (str_contains($msg, 'bonjour') || str_contains($msg, 'salut') || str_contains($msg, 'bonsoir')) {
            return "Bonjour {$context['user_name']} ! 🌿 Je suis Basma, votre assistante Bosten. Comment puis-je vous aider aujourd'hui ?";
        }

        if (str_contains($msg, 'commande') || str_contains($msg, 'livraison') || str_contains($msg, 'statut')) {
            if ($context['recent_orders']->isEmpty()) {
                return "Vous n'avez pas encore de commande chez nous. Visitez notre boutique pour découvrir nos plantes ! 🌱";
            }
            $last = $context['recent_orders']->first();
            $statut = match($last['status']) {
                'pending'   => 'en attente de confirmation',
                'confirmed' => 'confirmée',
                'shipped'   => 'en cours de livraison 🚚',
                'delivered' => 'livrée 📬',
                'cancelled' => 'annulée',
                default     => $last['status'],
            };
            return "Votre dernière commande #{$last['id']} du {$last['date']} est actuellement **{$statut}** pour un total de {$last['total']} TND.";
        }

        if (str_contains($msg, 'produit') || str_contains($msg, 'plante') || str_contains($msg, 'prix') || str_contains($msg, 'disponible')) {
            $sample = $context['products']->take(3)->map(fn ($p) => "{$p->name} ({$p->price} TND)")->join(', ');
            return "Nous avons {$context['products']->count()} produits disponibles en ce moment, dont : {$sample}... Visitez notre boutique pour voir tout le catalogue ! 🌿";
        }

        if (str_contains($msg, 'merci') || str_contains($msg, 'شكرا')) {
            return "Avec plaisir {$context['user_name']} ! 😊 N'hésitez pas si vous avez d'autres questions.";
        }

        return "Je suis Basma, votre assistante Bosten 🌿. Je peux vous aider avec notre catalogue de plantes, vos commandes ou vous donner des conseils jardinage. Que souhaitez-vous savoir ?";
    }
}
