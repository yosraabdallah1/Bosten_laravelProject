<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use App\Services\GeminiChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock du service Gemini pour éviter les vrais appels API en test
        $this->mock(GeminiChatbotService::class, function ($mock) {
            $mock->shouldReceive('ask')
                ->andReturn('Bonjour ! Je suis Basma, votre assistante Bosten. 🌿');
        });
    }

    // ─── POST /chatbot ────────────────────────────────────────────────────────

    /** @test */
    public function authenticated_user_can_send_chatbot_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/chatbot', ['message' => 'Bonjour !']);

        $response->assertStatus(200)
            ->assertJsonStructure(['reply', 'saved_at'])
            ->assertJsonFragment(['reply' => 'Bonjour ! Je suis Basma, votre assistante Bosten. 🌿']);
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->post('/chatbot', ['message' => 'Bonjour !']);

        // Redirigé vers login (302)
        $response->assertStatus(302);
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }

    /** @test */
    public function empty_message_returns_422_validation_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/chatbot', ['message' => '']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    /** @test */
    public function message_too_long_returns_422_validation_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/chatbot', ['message' => str_repeat('a', 501)]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    /** @test */
    public function missing_message_field_returns_422_validation_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/chatbot', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    /** @test */
    public function conversation_is_saved_in_database_after_ask(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/chatbot', ['message' => 'Avez-vous des plantes tropicales ?']);

        $this->assertDatabaseHas('conversations', [
            'user_id' => $user->id,
            'message' => 'Avez-vous des plantes tropicales ?',
            'reply'   => 'Bonjour ! Je suis Basma, votre assistante Bosten. 🌿',
        ]);
    }

    /** @test */
    public function response_contains_saved_at_timestamp(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/chatbot', ['message' => 'Test timestamp']);

        $response->assertStatus(200)
            ->assertJsonStructure(['reply', 'saved_at']);

        // Vérifier que saved_at est un timestamp ISO 8601 valide
        $savedAt = $response->json('saved_at');
        $this->assertNotNull($savedAt);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $savedAt);
    }

    // ─── POST /chatbot/clear ──────────────────────────────────────────────────

    /** @test */
    public function clear_deletes_user_conversations(): void
    {
        $user = User::factory()->create();

        // Créer quelques conversations
        Conversation::create(['user_id' => $user->id, 'message' => 'Test 1', 'reply' => 'Réponse 1']);
        Conversation::create(['user_id' => $user->id, 'message' => 'Test 2', 'reply' => 'Réponse 2']);

        $this->assertDatabaseCount('conversations', 2);

        $response = $this->actingAs($user)
            ->postJson('/chatbot/clear');

        $response->assertStatus(200)
            ->assertJson(['status' => 'cleared']);

        $this->assertDatabaseCount('conversations', 0);
    }

    /** @test */
    public function clear_only_deletes_current_user_conversations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Conversation::create(['user_id' => $user1->id, 'message' => 'User1 msg', 'reply' => 'Reply 1']);
        Conversation::create(['user_id' => $user2->id, 'message' => 'User2 msg', 'reply' => 'Reply 2']);

        $this->actingAs($user1)->postJson('/chatbot/clear');

        // user1's conversation deleted, user2's kept
        $this->assertDatabaseMissing('conversations', ['user_id' => $user1->id]);
        $this->assertDatabaseHas('conversations', ['user_id' => $user2->id]);
    }
}
