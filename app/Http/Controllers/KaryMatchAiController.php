<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class KaryMatchAiController extends Controller
{
    /** Perantara AI untuk SPA KaryMatch. Kunci API hanya ada di server (.env). */
    public function complete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'max:6000'],
        ]);

        $provider = config('services.ai.provider', 'gemini');
        $key = config("services.{$provider}.key");

        if (! in_array($provider, ['gemini', 'anthropic'], true)) {
            return response()->json(['message' => 'AI_PROVIDER tidak dikenal.'], 503);
        }
        if (! $key) {
            return response()->json(['message' => 'Kunci API untuk '.$provider.' belum diisi di .env'], 503);
        }

        try {
            $response = $provider === 'gemini'
                ? $this->callGemini($key, $data['prompt'])
                : $this->callAnthropic($key, $data['prompt']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Layanan AI tidak dapat dihubungi.'], 502);
        }

        if ($response->failed()) {
            report(new \RuntimeException("AI error ({$provider}) {$response->status()}: {$response->body()}"));

            return response()->json(['message' => 'Layanan AI menolak permintaan.'], 502);
        }

        $text = $provider === 'gemini'
            ? collect($response->json('candidates.0.content.parts', []))->pluck('text')->implode('')
            : collect($response->json('content', []))->where('type', 'text')->pluck('text')->implode('');

        return response()->json(['text' => $text]);
    }

    private function callGemini(string $key, string $prompt)
    {
        $model = config('services.gemini.model');

        return Http::withHeaders(['x-goog-api-key' => $key])
            ->timeout(45)->acceptJson()
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'maxOutputTokens' => 1500,
                    'thinkingConfig' => ['thinkingBudget' => 0],
                ],
            ]);
    }

    private function callAnthropic(string $key, string $prompt)
    {
        return Http::withHeaders([
            'x-api-key' => $key,
            'anthropic-version' => config('services.anthropic.version', '2023-06-01'),
        ])->timeout(45)->acceptJson()->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 1000,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]);
    }
}
