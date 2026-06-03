<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\GeminiChatbotService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeminiChatbotServiceTest extends TestCase
{
    use RefreshDatabase;

    private GeminiChatbotService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeminiChatbotService();
    }

    // ─── detectIntent() ──────────────────────────────────────────────────────

    /** @test */
    public function detectIntent_returns_stock_for_stock_keywords(): void
    {
        $intents = $this->service->detectIntent('Quels produits sont en rupture de stock ?');
        $this->assertContains('stock', $intents);
    }

    /** @test */
    public function detectIntent_returns_orders_for_commande_keyword(): void
    {
        $intents = $this->service->detectIntent('Quel est le statut de ma commande ?');
        $this->assertContains('orders', $intents);
    }

    /** @test */
    public function detectIntent_returns_catalogue_for_produits_keyword(): void
    {
        $intents = $this->service->detectIntent('Montrez-moi le catalogue de produits.');
        $this->assertContains('catalogue', $intents);
    }

    /** @test */
    public function detectIntent_returns_bestsellers_for_populaire_keyword(): void
    {
        $intents = $this->service->detectIntent('Quels sont vos produits les plus populaires ?');
        $this->assertContains('bestsellers', $intents);
    }

    /** @test */
    public function detectIntent_returns_price_for_prix_keyword(): void
    {
        $intents = $this->service->detectIntent('Quel est le prix de cette plante ?');
        $this->assertContains('price', $intents);
    }

    /** @test */
    public function detectIntent_returns_advice_for_entretien_keyword(): void
    {
        $intents = $this->service->detectIntent("Comment entretenir cette plante ?");
        $this->assertContains('advice', $intents);
    }

    /** @test */
    public function detectIntent_returns_catalogue_as_default_when_no_match(): void
    {
        $intents = $this->service->detectIntent('Bonjour !');
        $this->assertContains('catalogue', $intents);
    }

    /** @test */
    public function detectIntent_can_return_multiple_intents(): void
    {
        $intents = $this->service->detectIntent('Quel est le prix et le stock disponible ?');
        $this->assertContains('price', $intents);
        $this->assertContains('stock', $intents);
    }

    /** @test */
    public function detectIntent_returns_unique_intents(): void
    {
        $intents = $this->service->detectIntent('produits produits');
        $this->assertSame(array_unique($intents), $intents);
    }

    // ─── buildPrompt() ────────────────────────────────────────────────────────

    /** @test */
    public function buildPrompt_contains_required_sections(): void
    {
        $user = User::factory()->create(['name' => 'Sami Ben Ali']);

        $contextData = ['user_name' => 'Sami'];
        $history     = [];
        $question    = 'Avez-vous des plantes en promo ?';

        $prompt = $this->service->buildPrompt($user, $contextData, $history, $question);

        $this->assertStringContainsString('Basma', $prompt);
        $this->assertStringContainsString('Bosten', $prompt);
        $this->assertStringContainsString('Sami', $prompt);
        $this->assertStringContainsString('DONNÉES DISPONIBLES', $prompt);
        $this->assertStringContainsString('HISTORIQUE', $prompt);
        $this->assertStringContainsString('QUESTION ACTUELLE', $prompt);
        $this->assertStringContainsString($question, $prompt);
        $this->assertStringContainsString('TND', $prompt);
    }

    /** @test */
    public function buildPrompt_includes_history_lines(): void
    {
        $user = User::factory()->create(['name' => 'Leila']);

        $contextData = ['user_name' => 'Leila'];
        $history     = [
            ['message' => 'Bonjour', 'reply' => 'Bonjour Leila !'],
        ];

        $prompt = $this->service->buildPrompt($user, $contextData, $history, 'Merci');

        $this->assertStringContainsString('Client: Bonjour', $prompt);
        $this->assertStringContainsString('Basma: Bonjour Leila', $prompt);
    }

    /** @test */
    public function buildPrompt_shows_no_history_message_when_empty(): void
    {
        $user        = User::factory()->create();
        $contextData = ['user_name' => 'Test'];

        $prompt = $this->service->buildPrompt($user, $contextData, [], 'Question');

        $this->assertStringContainsString('Aucun historique', $prompt);
    }

    // ─── callGemini() — mock HTTP ─────────────────────────────────────────────

    /** @test */
    public function callGemini_returns_text_on_success(): void
    {
        $service = $this->makeServiceWithMock(new Response(200, [], json_encode([
            'candidates' => [
                ['content' => ['parts' => [['text' => 'Bonjour, je suis Basma !']]]]
            ],
        ])));

        $result = $service->callGemini('Un prompt de test');

        $this->assertSame('Bonjour, je suis Basma !', $result);
    }

    /** @test */
    public function callGemini_returns_rate_limit_message_on_429(): void
    {
        $service = $this->makeServiceWithMock(
            new RequestException(
                'Too Many Requests',
                new GuzzleRequest('POST', 'test'),
                new Response(429)
            )
        );

        $result = $service->callGemini('prompt', 'question', []);

        $this->assertStringContainsString('surchargé', $result);
    }

    /** @test */
    public function callGemini_returns_unavailable_message_on_timeout(): void
    {
        $service = $this->makeServiceWithMock(
            new ConnectException(
                'Connection timed out',
                new GuzzleRequest('POST', 'test')
            )
        );

        $result = $service->callGemini('prompt', 'question', []);

        $this->assertStringContainsString('indisponible', $result);
    }

    /** @test */
    public function callGemini_returns_technical_error_message_on_500(): void
    {
        $service = $this->makeServiceWithMock(
            new RequestException(
                'Internal Server Error',
                new GuzzleRequest('POST', 'test'),
                new Response(500)
            )
        );

        $result = $service->callGemini('prompt', 'question', []);

        $this->assertStringContainsString('difficultés techniques', $result);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Crée une instance de GeminiChatbotService avec un client Guzzle mocké.
     */
    private function makeServiceWithMock(\Throwable|Response $response): GeminiChatbotService
    {
        $mock    = new MockHandler([$response]);
        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);

        // On réfléchit le client mocké dans le service via une sous-classe anonyme
        return new class($client) extends GeminiChatbotService {
            public function __construct(private Client $mockClient)
            {
                parent::__construct();
            }

            public function callGemini(string $prompt, string $question = '', array $contextData = []): string
            {
                $geminiKey = config('services.gemini.key', env('GEMINI_API_KEY', 'test-key'));
                $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

                try {
                    $response = $this->mockClient->post("{$geminiUrl}?key={$geminiKey}", [
                        'json' => [
                            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                        ],
                    ]);

                    $body = json_decode((string) $response->getBody(), true);
                    $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

                    if (empty($text)) {
                        return $question ? $this->localFallback($question, $contextData)
                            : "Je n'ai pas pu obtenir une réponse. Veuillez réessayer.";
                    }

                    return $text;

                } catch (ConnectException $e) {
                    \Illuminate\Support\Facades\Log::warning('GeminiChatbotService: timeout', ['error' => $e->getMessage()]);
                    return "Désolé, le service IA est momentanément indisponible. Réessayez dans quelques instants.";

                } catch (RequestException $e) {
                    $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;

                    if ($statusCode === 429) {
                        \Illuminate\Support\Facades\Log::warning('GeminiChatbotService: rate limit (429)', ['error' => $e->getMessage()]);
                        return "Le service est temporairement surchargé. Réessayez dans quelques minutes.";
                    }

                    if (in_array($statusCode, [500, 503])) {
                        \Illuminate\Support\Facades\Log::warning("GeminiChatbotService: erreur serveur ({$statusCode})", ['error' => $e->getMessage()]);
                        return "Le service IA rencontre des difficultés techniques.";
                    }

                    \Illuminate\Support\Facades\Log::warning('GeminiChatbotService: RequestException', ['status' => $statusCode, 'error' => $e->getMessage()]);
                    return $question ? $this->localFallback($question, $contextData)
                        : "Une erreur est survenue. Veuillez réessayer.";

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('GeminiChatbotService: exception générale', ['error' => $e->getMessage()]);
                    return $question ? $this->localFallback($question, $contextData)
                        : "Une erreur inattendue s'est produite.";
                }
            }
        };
    }
}
