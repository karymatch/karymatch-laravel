<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KaryMatchSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_state_can_be_saved_and_read_back(): void
    {
        $payload = json_encode([['id' => 'job-1', 'title' => 'Junior Developer']], JSON_THROW_ON_ERROR);

        $this->putJson('/karymatch/sync/jobs', ['v' => $payload])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $this->getJson('/karymatch/sync')
            ->assertOk()
            ->assertJsonPath('docs.0.id', 'jobs')
            ->assertJsonPath('docs.0.v', $payload);
    }

    public function test_password_hashes_are_synced_for_cross_device_login(): void
    {
        $payload = json_encode(['a@b.id' => str_repeat('a', 64)], JSON_THROW_ON_ERROR);

        $this->putJson('/karymatch/sync/pw', ['v' => $payload])->assertOk();

        $this->getJson('/karymatch/sync')->assertJsonPath('docs.0.id', 'pw');
    }

    public function test_sync_payload_must_be_a_string(): void
    {
        $this->putJson('/karymatch/sync/jobs', ['v' => ['invalid']])
            ->assertUnprocessable();
    }

    public function test_ai_endpoint_requires_api_key(): void
    {
        config(['services.ai.provider' => 'gemini', 'services.gemini.key' => null]);

        $this->postJson('/karymatch/ai', ['prompt' => 'Halo'])->assertStatus(503);
    }

    public function test_ai_endpoint_returns_text_from_gemini(): void
    {
        config(['services.ai.provider' => 'gemini', 'services.gemini.key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Halo juga']]]]],
        ])]);

        $this->postJson('/karymatch/ai', ['prompt' => 'Halo'])
            ->assertOk()
            ->assertJson(['text' => 'Halo juga']);
    }

    public function test_ai_endpoint_returns_text_from_anthropic(): void
    {
        config(['services.ai.provider' => 'anthropic', 'services.anthropic.key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'Halo juga']],
        ])]);

        $this->postJson('/karymatch/ai', ['prompt' => 'Halo'])
            ->assertOk()
            ->assertJson(['text' => 'Halo juga']);
    }
}
