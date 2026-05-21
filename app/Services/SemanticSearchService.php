<?php

namespace App\Services;

use App\Models\Note;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SemanticSearchService
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Search notes semantically by query string
     */
    public function search(string $query, int $limit = 10): Collection
    {
        try {
            // Generate embedding for search query
            $queryEmbedding = $this->aiService->generateEmbedding($query);

            // Get all notes with embeddings
            $notes = Note::whereNotNull('embedding')->get();

            // Calculate similarity scores
            $scoredNotes = $notes->map(function (Note $note) use ($queryEmbedding) {
                $noteEmbedding = $note->getEmbedding();
                $similarity = AiService::cosineSimilarity($queryEmbedding, $noteEmbedding);
                
                return [
                    'note' => $note,
                    'similarity' => $similarity,
                ];
            });

            // Sort by similarity descending and take top N results
            return $scoredNotes
                ->sortByDesc('similarity')
                ->take($limit)
                ->pluck('note')
                ->values();
        } catch (\Exception $e) {
            Log::error('Semantic search failed: ' . $e->getMessage());
            throw new \Exception('Semantic search failed: ' . $e->getMessage());
        }
    }
}
