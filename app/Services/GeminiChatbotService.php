<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class GeminiChatbotService
{
    private string $geminiKey;
    private string $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->geminiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
    }

    /**
     * Méthode principale : analyse la question, récupère le contexte et interroge Gemini.
     */
    public function ask(User $user, string $question): string
    {
        $intents     = $this->detectIntent($question);
        $contextData = $this->fetchContextData($user, $intents);

        // Historique : 3 derniers échanges
        $history = Conversation::where('user_id', $user->id)
            ->latest()
            ->limit(3)
            ->get()
            ->reverse()
            ->values();

        $prompt = $this->buildPrompt($user, $contextData, $history->toArray(), $question);

        return $this->callGemini($prompt, $question, $contextData);
    }

    /**
     * Analyse la question par mots-clés/regex et retourne un tableau d'intents.
     *
     * @return array<string>
     */
    public function detectIntent(string $question): array
    {
        $q       = mb_strtolower($question);
        $intents = [];

        // stock / rupture
        if (preg_match('/\b(stock|rupture|dispo|disponible|reste|restant|quantit[eé])\b/u', $q)) {
            $intents[] = 'stock';
        }

        // commandes
        if (preg_match('/\b(commande|livraison|livr[eé]|statut|suivi|achat|achet[eé]|expédi)\b/u', $q)) {
            $intents[] = 'orders';
        }

        // catalogue général
        if (preg_match('/\b(catalogue|produits?|plantes?|jardinage|jardin|liste|voir|montrer|afficher)\b/u', $q)) {
            $intents[] = 'catalogue';
        }

        // meilleures ventes
        if (preg_match('/\b(meilleure?s? ventes?|populaires?|best.?seller|top|plus vendu|tendance)\b/u', $q)) {
            $intents[] = 'bestsellers';
        }

        // prix / tarif
        if (preg_match('/\b(prix|tarif|co[uû]t|combien|tnd|dinar|cher|pas cher|promotion|promo|r[ée]duction)\b/u', $q)) {
            $intents[] = 'price';
        }

        // conseils / entretien — doit être AVANT catalogue pour avoir priorité sur "jardinage"
        if (preg_match('/\b(conseils?|entretien|entretenir|soins?|arroser|arrosage|engrais|terre|rempotage|luminosit[eé]|cultiver|jardinage)\b/u', $q)) {
            $intents[] = 'advice';
        }

        // Si aucun intent détecté, on charge le catalogue par défaut
        if (empty($intents)) {
            $intents[] = 'catalogue';
        }

        return array_unique($intents);
    }

    /**
     * Charge les données Eloquent selon les intents détectés.
     *
     * @param  array<string> $intents
     * @return array<string, mixed>
     */
    public function fetchContextData(User $user, array $intents): array
    {
        $data = [
            'user_name' => explode(' ', $user->name)[0], // prénom uniquement
        ];

        foreach ($intents as $intent) {
            switch ($intent) {
                case 'stock':
                    $data['low_stock'] = Product::where('stock', '<', 5)
                        ->where('is_active', true)
                        ->with('category')
                        ->get(['id', 'name', 'stock', 'price', 'category_id']);
                    break;

                case 'orders':
                    $data['recent_orders'] = $user->orders()
                        ->latest()
                        ->limit(5)
                        ->with('items.product')
                        ->get()
                        ->map(fn (Order $o) => [
                            'id'     => $o->id,
                            'total'  => $o->total,
                            'status' => $o->status,
                            'date'   => $o->created_at->format('d/m/Y'),
                            'items'  => $o->items
                                ->map(fn ($i) => ($i->product->name ?? '?') . ' x' . $i->quantity)
                                ->join(', '),
                        ]);
                    break;

                case 'catalogue':
                    $data['catalogue'] = Product::where('is_active', true)
                        ->with('category')
                        ->limit(8)
                        ->get(['id', 'name', 'price', 'stock', 'category_id']);
                    break;

                case 'bestsellers':
                    $data['bestsellers'] = Product::withSum('orderItems as total_sold', 'quantity')
                        ->orderByDesc('total_sold')
                        ->limit(3)
                        ->get(['id', 'name', 'price']);
                    break;

                case 'price':
                    $data['prices'] = Product::where('is_active', true)
                        ->with('category')
                        ->get(['id', 'name', 'price', 'category_id']);
                    break;

                case 'advice':
                    $data['advice_products'] = Product::where('is_active', true)
                        ->get(['id', 'name', 'description', 'price']);
                    break;
            }
        }

        return $data;
    }

    /**
     * Construit le prompt final envoyé à Gemini.
     *
     * @param  array<mixed> $history   Tableau de Conversation (objet ou tableau)
     * @param  array<string, mixed> $contextData
     */
    public function buildPrompt(User $user, array $contextData, array $history, string $question): string
    {
        $userName = $contextData['user_name'] ?? explode(' ', $user->name)[0];

        // — Formatage des données selon les clés présentes —
        $dataLines = [];

        if (isset($contextData['low_stock']) && $contextData['low_stock']->isNotEmpty()) {
            $dataLines[] = "### Produits en faible stock (< 5 unités) :";
            foreach ($contextData['low_stock'] as $p) {
                $cat = $p->category->name ?? 'Non classé';
                $dataLines[] = "  - {$p->name} ({$cat}) : {$p->price} TND — stock restant : {$p->stock}";
            }
        }

        if (isset($contextData['recent_orders'])) {
            $dataLines[] = "### Commandes récentes du client :";
            if ($contextData['recent_orders']->isEmpty()) {
                $dataLines[] = "  Aucune commande trouvée.";
            } else {
                foreach ($contextData['recent_orders'] as $o) {
                    $dataLines[] = "  - Commande #{$o['id']} du {$o['date']} — Statut : {$o['status']} — Total : {$o['total']} TND — Articles : {$o['items']}";
                }
            }
        }

        if (isset($contextData['catalogue']) && $contextData['catalogue']->isNotEmpty()) {
            $dataLines[] = "### Catalogue (8 premiers produits actifs) :";
            foreach ($contextData['catalogue'] as $p) {
                $cat = $p->category->name ?? 'Non classé';
                $dataLines[] = "  - {$p->name} ({$cat}) : {$p->price} TND (stock : {$p->stock})";
            }
        }

        if (isset($contextData['bestsellers']) && $contextData['bestsellers']->isNotEmpty()) {
            $dataLines[] = "### Meilleures ventes (top 3) :";
            foreach ($contextData['bestsellers'] as $p) {
                $sold = $p->total_sold ?? 0;
                $dataLines[] = "  - {$p->name} : {$p->price} TND ({$sold} unités vendues)";
            }
        }

        if (isset($contextData['prices']) && $contextData['prices']->isNotEmpty()) {
            $dataLines[] = "### Tarifs de tous les produits actifs :";
            foreach ($contextData['prices'] as $p) {
                $cat = $p->category->name ?? 'Non classé';
                $dataLines[] = "  - {$p->name} ({$cat}) : {$p->price} TND";
            }
        }

        if (isset($contextData['advice_products']) && $contextData['advice_products']->isNotEmpty()) {
            $dataLines[] = "### Produits avec description (conseils) :";
            foreach ($contextData['advice_products'] as $p) {
                $desc = $p->description ? mb_substr($p->description, 0, 120) . '…' : 'Aucune description.';
                $dataLines[] = "  - {$p->name} : {$desc}";
            }
        }

        $dataSection = empty($dataLines)
            ? "Aucune donnée spécifique disponible pour cette question."
            : implode("\n", $dataLines);

        // — Formatage de l'historique —
        $historyLines = [];
        foreach ($history as $conv) {
            $msg   = is_array($conv) ? $conv['message'] : $conv->message;
            $reply = is_array($conv) ? $conv['reply']   : $conv->reply;
            $historyLines[] = "Client: {$msg} / Basma: {$reply}";
        }
        $historySection = empty($historyLines)
            ? "Aucun historique."
            : implode("\n", $historyLines);

        return <<<PROMPT
Tu es Basma, l'assistante virtuelle de Bosten, une boutique tunisienne de plantes et jardinage.
Tu réponds en français (ou arabe si le client écrit en arabe), avec un ton chaleureux et professionnel.

CLIENT : {$userName}

DONNÉES DISPONIBLES :
{$dataSection}

HISTORIQUE (3 derniers échanges) :
{$historySection}

QUESTION ACTUELLE : {$question}

RÈGLES :
- Réponds UNIQUEMENT à partir des données fournies ci-dessus.
- Si la réponse n'est pas dans les données, dis-le poliment.
- Si la question est hors contexte Bosten, réponds : "Je suis spécialisé dans l'aide pour vos commandes et les produits Bosten."
- Maximum 4 phrases sauf si une liste est nécessaire.
- Prix toujours en TND.
PROMPT;
    }

    /**
     * Appel HTTP vers l'API Gemini avec gestion complète des erreurs.
     *
     * @param  array<string, mixed> $contextData  Pour le fallback local
     */
    public function callGemini(string $prompt, string $question = '', array $contextData = []): string
    {
        $client = new Client(['timeout' => 25]);

        try {
            $response = $client->post("{$this->geminiUrl}?key={$this->geminiKey}", [
                'json' => [
                    'contents' => [
                        [
                            'role'  => 'user',
                            'parts' => [['text' => $prompt]],
                        ],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 512,
                        'temperature'     => 0.7,
                    ],
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (empty($text)) {
                Log::warning('GeminiChatbotService: réponse vide de l\'API Gemini', [
                    'body' => $body,
                ]);
                return $question ? $this->localFallback($question, $contextData)
                    : "Je n'ai pas pu obtenir une réponse. Veuillez réessayer.";
            }

            return $text;

        } catch (ConnectException $e) {
            // Timeout ou problème réseau
            Log::warning('GeminiChatbotService: timeout ou connexion impossible', [
                'error' => $e->getMessage(),
            ]);
            return "Désolé, le service IA est momentanément indisponible. Réessayez dans quelques instants.";

        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;

            if ($statusCode === 429) {
                Log::warning('GeminiChatbotService: rate limit atteint (HTTP 429) — fallback local activé', [
                    'error' => $e->getMessage(),
                ]);
                // Clé invalide ou quota épuisé → réponse locale avec les données DB
                return $question ? $this->localFallback($question, $contextData)
                    : "Je suis Basma 🌿. Le service IA est momentanément indisponible, mais je peux vous aider avec les informations disponibles.";
            }

            if (in_array($statusCode, [500, 503])) {
                Log::warning("GeminiChatbotService: erreur serveur Gemini (HTTP {$statusCode})", [
                    'error' => $e->getMessage(),
                ]);
                return "Le service IA rencontre des difficultés techniques.";
            }

            Log::warning('GeminiChatbotService: RequestException non gérée', [
                'status' => $statusCode,
                'error'  => $e->getMessage(),
            ]);
            return $question ? $this->localFallback($question, $contextData)
                : "Une erreur est survenue. Veuillez réessayer.";

        } catch (\Exception $e) {
            Log::warning('GeminiChatbotService: exception générale', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $question ? $this->localFallback($question, $contextData)
                : "Une erreur inattendue s'est produite.";
        }
    }

    /**
     * Réponses locales de secours si l'API Gemini est indisponible.
     *
     * @param  array<string, mixed> $contextData
     */
    public function localFallback(string $question, array $contextData): string
    {
        $q        = mb_strtolower($question);
        $userName = $contextData['user_name'] ?? 'cher client';

        // — Salutations —
        if (preg_match('/\b(bonjour|salut|bonsoir|ahla|marhba|salam|hi|hello)\b/u', $q)) {
            return "Bonjour {$userName} ! 🌿 Je suis Basma, votre assistante Bosten. Comment puis-je vous aider aujourd'hui ?";
        }

        // — Meilleures ventes / bestsellers —
        if (preg_match('/\b(meilleure?s? ventes?|populaires?|best.?seller|top|plus vendu|tendance)\b/u', $q)) {
            if (!empty($contextData['bestsellers']) && $contextData['bestsellers']->isNotEmpty()) {
                $lines = $contextData['bestsellers']->map(fn ($p) =>
                    "🏆 {$p->name} — {$p->price} TND" . ($p->total_sold ? " ({$p->total_sold} vendus)" : "")
                )->join("\n");
                return "Voici nos produits les plus populaires, {$userName} :\n{$lines}";
            }
            // Fallback si pas de données bestsellers : prendre les produits du catalogue
            if (!empty($contextData['catalogue']) && $contextData['catalogue']->isNotEmpty()) {
                $sample = $contextData['catalogue']->take(3)->map(fn ($p) => "🌿 {$p->name} ({$p->price} TND)")->join(", ");
                return "Parmi nos produits les plus appréciés : {$sample}. Visitez notre boutique pour voir tout le catalogue !";
            }
        }

        // — Stock faible / disponibilité —
        if (preg_match('/\b(stock|rupture|dispo|disponible|reste|restant)\b/u', $q)) {
            if (!empty($contextData['low_stock'])) {
                if ($contextData['low_stock']->isEmpty()) {
                    return "Bonne nouvelle {$userName} ! Tous nos produits sont bien approvisionnés en ce moment. 🌱";
                }
                $lines = $contextData['low_stock']->map(fn ($p) =>
                    "⚠️ {$p->name} : {$p->stock} restants"
                )->join("\n");
                return "Voici les produits en stock limité :\n{$lines}\nCommandez vite pour ne pas manquer votre plante préférée !";
            }
        }

        // — Commandes —
        if (preg_match('/\b(commande|livraison|statut|suivi)\b/u', $q)) {
            if (!empty($contextData['recent_orders']) && $contextData['recent_orders']->isNotEmpty()) {
                $last   = $contextData['recent_orders']->first();
                $statut = match ($last['status']) {
                    'pending'   => 'en attente de confirmation ⏳',
                    'confirmed' => 'confirmée ✅',
                    'shipped'   => 'en cours de livraison 🚚',
                    'delivered' => 'livrée 📬',
                    'cancelled' => 'annulée ❌',
                    default     => $last['status'],
                };
                return "Votre dernière commande #{$last['id']} du {$last['date']} est {$statut}, pour un total de {$last['total']} TND.";
            }
            return "Vous n'avez pas encore de commande chez nous. Visitez notre boutique pour découvrir nos plantes ! 🌱";
        }

        // — Prix / tarifs —
        if (preg_match('/\b(prix|tarif|combien|co[uû]t|promo|r[ée]duction)\b/u', $q)) {
            if (!empty($contextData['prices']) && $contextData['prices']->isNotEmpty()) {
                $sample = $contextData['prices']->take(4)->map(fn ($p) => "{$p->name} : {$p->price} TND")->join(" | ");
                return "Voici quelques tarifs : {$sample}. Consultez notre boutique pour la liste complète. 🌿";
            }
            if (!empty($contextData['catalogue']) && $contextData['catalogue']->isNotEmpty()) {
                $sample = $contextData['catalogue']->take(4)->map(fn ($p) => "{$p->name} : {$p->price} TND")->join(" | ");
                return "Voici quelques tarifs : {$sample}.";
            }
        }

        // — Catalogue / produits —
        if (preg_match('/\b(produit|plante|catalogue|liste|jardin|voir|montrer)\b/u', $q)) {
            if (!empty($contextData['catalogue']) && $contextData['catalogue']->isNotEmpty()) {
                $sample = $contextData['catalogue']->take(4)->map(fn ($p) => "🌿 {$p->name} ({$p->price} TND)")->join(", ");
                return "Nous proposons notamment : {$sample}... Visitez notre boutique pour voir tout le catalogue !";
            }
        }

        // — Conseils / entretien —
        if (preg_match('/\b(conseils?|entretien|arroser|arrosage|engrais|soins?|cultiver|jardinage)\b/u', $q)) {
            if (!empty($contextData['advice_products']) && $contextData['advice_products']->isNotEmpty()) {
                // Chercher un produit dont la description matche un mot-clé de la question
                $words   = preg_split('/\s+/', $q);
                $matched = $contextData['advice_products']->first(function ($p) use ($words) {
                    $name = mb_strtolower($p->name ?? '');
                    $desc = mb_strtolower($p->description ?? '');
                    foreach ($words as $word) {
                        if (mb_strlen($word) > 3 && (str_contains($name, $word) || str_contains($desc, $word))) {
                            return true;
                        }
                    }
                    return false;
                });

                $product = $matched ?? $contextData['advice_products']->random();

                if ($product && $product->description) {
                    $desc = mb_substr($product->description, 0, 200);
                    return "🌱 Conseil pour **{$product->name}** : {$desc}\n\nPour des conseils personnalisés, contactez-nous à support@bosten.tn 🌿";
                }

                // Pas de description → conseil générique mais utile
                $names = $contextData['advice_products']->take(3)->pluck('name')->join(', ');
                return "🌿 Nous proposons plusieurs plantes adaptées à tous les niveaux : {$names}. Arrosez régulièrement, placez-les en lumière indirecte et utilisez un terreau adapté. Pour plus de détails, contactez support@bosten.tn !";
            }

            // Conseil générique si pas de données
            return "🌿 Voici quelques conseils jardinage :\n• Arrosez le matin pour éviter l'évaporation\n• Utilisez un terreau adapté à chaque type de plante\n• Évitez l'excès d'eau (cause principale de mort des plantes)\n• Fertilisez au printemps et en été\n\nVisitez notre boutique pour découvrir nos produits ! 🌱";
        }

        // — Remerciements —
        if (preg_match('/\b(merci|شكرا|barak|thank)\b/u', $q)) {
            return "Avec plaisir {$userName} ! 😊 N'hésitez pas si vous avez d'autres questions.";
        }

        // — Hors contexte —
        if (preg_match('/\b(météo|sport|politique|film|musique|recette|cuisine)\b/u', $q)) {
            return "Je suis spécialisé dans l'aide pour vos commandes et les produits Bosten. Pour d'autres sujets, je ne peux malheureusement pas vous aider. 🌿";
        }

        // — Réponse générique — basée sur les données disponibles
        if (!empty($contextData['catalogue']) && $contextData['catalogue']->isNotEmpty()) {
            $sample = $contextData['catalogue']->take(3)->map(fn ($p) => "🌿 {$p->name} ({$p->price} TND)")->join(', ');
            return "Je n'ai pas bien compris votre demande, {$userName}. Voici quelques produits de notre boutique : {$sample}. Posez-moi une question sur le catalogue, les prix, vos commandes ou les conseils jardinage !";
        }

        return "Je n'ai pas bien compris votre demande. 🌿 Posez-moi une question sur nos plantes, vos commandes, les prix ou les conseils jardinage !";
    }
}
