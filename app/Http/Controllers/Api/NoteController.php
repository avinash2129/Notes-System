<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Requests\SearchNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use App\Services\AiService;
use App\Services\SemanticSearchService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class NoteController extends Controller
{
    protected AiService $aiService;
    protected SemanticSearchService $searchService;

    public function __construct(AiService $aiService, SemanticSearchService $searchService)
    {
        $this->aiService = $aiService;
        $this->searchService = $searchService;

        // Rate limiting
        $this->middleware('throttle:60,1');
    }

    /**
     * Create a new note
     */
    public function store(StoreNoteRequest $request): JsonResponse
    {
        try {
            // Generate embedding for the content
            $embedding = $this->aiService->generateEmbedding($request->content);

            // Create note with embedding
            $note = Note::create([
                'title' => $request->title,
                'content' => $request->content,
                'embedding' => $embedding,
            ]);

            return response()->json(new NoteResource($note), 201);
        } catch (\Exception $e) {
            Log::error('Note creation failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create note',
                'message' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * Get all notes with pagination
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $limit = min((int)$request->query('limit', 10), 100);
        $page = max((int)$request->query('page', 1), 1);

        $notes = Note::paginate($limit, ['*'], 'page', $page);

        return NoteResource::collection($notes);
    }

    /**
     * Get a single note
     */
    public function show(Note $note): JsonResponse
    {
        return response()->json(new NoteResource($note), 200);
    }

    /**
     * Update a note
     */
    public function update(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        try {
            $data = $request->validated();

            // If content is being updated, regenerate embedding
            if (isset($data['content'])) {
                $data['embedding'] = $this->aiService->generateEmbedding($data['content']);
                // Clear summary so it can be regenerated if needed
                $data['summary'] = null;
            }

            $note->update($data);

            return response()->json(new NoteResource($note), 200);
        } catch (\Exception $e) {
            Log::error('Note update failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update note',
                'message' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * Delete a note (soft delete)
     */
    public function destroy(Note $note): JsonResponse
    {
        $note->delete();
        return response()->json(['message' => 'Note deleted successfully'], 200);
    }

    /**
     * Semantic search for notes
     */
    public function search(SearchNoteRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $query = $validated['q'];
            $limit = min((int)($validated['limit'] ?? 10), 100);

            $results = $this->searchService->search($query, $limit);

            return response()->json([
                'query' => $query,
                'count' => count($results),
                'results' => NoteResource::collection($results),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Semantic search failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Search failed',
                'message' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * Generate AI summary for a note
     */
    public function generateSummary(Note $note): JsonResponse
    {
        try {
            $summary = $this->aiService->generateSummary($note->content);
            $note->update(['summary' => $summary]);

            return response()->json([
                'id' => $note->id,
                'summary' => $summary,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Summary generation failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate summary',
                'message' => $e->getMessage(),
            ], 503);
        }
    }
}
