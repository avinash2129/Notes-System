<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$notes = App\Models\Note::all(['id', 'title']);
echo "Total notes: " . count($notes) . "\n\n";
foreach ($notes as $note) {
    echo $note->id . ". " . $note->title . "\n";
}
