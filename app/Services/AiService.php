<?php

namespace App\Services;

use OpenAI;
use OpenAI\Client;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected Client $client;
    protected bool $mock = false;

    public function __construct()
    {
        $apiKey = config('ai.api_key');

        if (!$apiKey) {
            // Run in mock mode for development / offline use.
            $this->mock = true;
            return;
        }

        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Generate embedding for text using OpenAI
     */
    public function generateEmbedding(string $text): array
    {
        try {
            if ($this->mock) {
                // Deterministic pseudo-embedding based on sha256
                $hex = hash('sha256', $text);
                $vector = [];
                // produce 64-dimension vector using hex chunks
                for ($i = 0; $i < 64; $i++) {
                    $chunk = substr($hex, ($i * 4) % strlen($hex), 4);
                    $val = hexdec($chunk) / 0xffff; // 0..1
                    $vector[] = $val * 2 - 1; // map to -1..1
                }
                return $vector;
            }

            $response = $this->client->embeddings()->create([
                'model' => config('ai.model_embedding', 'text-embedding-3-small'),
                'input' => $text,
            ]);

            return $response->embeddings[0]->embedding;

        } catch (\Exception $e) {

            Log::error('Embedding generation failed: ' . $e->getMessage());

            throw new \Exception(
                'Failed to generate embedding: ' . $e->getMessage()
            );
        }
    }

    /**
     * Generate summary for note content using OpenAI
     */
    public function generateSummary(string $content): string
    {
        try {
            if ($this->mock) {
                // Simple rule-based summary: take first 2 sentences or truncate
                $sentences = preg_split('/(?<=[.!?])\\s+/', trim($content));
                $summary = '';
                if ($sentences && count($sentences) >= 2) {
                    $summary = $sentences[0];
                    if (isset($sentences[1])) {
                        $summary .= ' ' . $sentences[1];
                    }
                } else {
                    $summary = mb_substr($content, 0, 250);
                    if (mb_strlen($content) > 250) $summary .= '...';
                }
                return $summary;
            }

            $response = $this->client->chat()->create([
                'model' => config('ai.model_summary', 'gpt-3.5-turbo'),

                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that generates concise summaries.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Please provide a concise summary of this note in 2-3 sentences:\n\n" . $content,
                    ],
                ],

                'temperature' => 0.7,
                'max_tokens' => 150,
            ]);

            return $response->choices[0]->message->content;

        } catch (\Exception $e) {

            Log::error('Summary generation failed: ' . $e->getMessage());

            throw new \Exception(
                'Failed to generate summary: ' . $e->getMessage()
            );
        }
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    public static function cosineSimilarity(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($vector1); $i++) {

            $dotProduct += $vector1[$i] * $vector2[$i];

            $magnitude1 += $vector1[$i] ** 2;

            $magnitude2 += $vector2[$i] ** 2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }
}