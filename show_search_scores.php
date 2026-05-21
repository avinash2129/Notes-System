<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Note;
use App\Services\AiService;

$aiService = app(AiService::class);

// Generate embedding for search query
$queryEmbedding = $aiService->generateEmbedding('programming');

// Get all notes with embeddings
$notes = Note::whereNotNull('embedding')->get();

// Calculate similarity scores
$scoredNotes = [];
foreach ($notes as $note) {
    $noteEmbedding = $note->getEmbedding();
    $similarity = AiService::cosineSimilarity($queryEmbedding, $noteEmbedding);
    $scoredNotes[] = [
        'title' => $note->title,
        'similarity' => $similarity,
    ];
}

// Sort by similarity descending
usort($scoredNotes, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

echo "Search Results for: 'programming'\n";
echo "=====================================\n\n";

foreach ($scoredNotes as $index => $result) {
    echo ($index + 1) . ". " . $result['title'] . "\n";
    echo "   Similarity: " . number_format($result['similarity'], 4) . " (0=different, 1=identical)\n\n";
}
