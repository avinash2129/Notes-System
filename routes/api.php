<?php

use App\Http\Controllers\Api\NoteController;
use Illuminate\Support\Facades\Route;

Route::post('/notes', [NoteController::class, 'store']);

Route::get('/notes', [NoteController::class, 'index']);

Route::get('/notes/{note}', [NoteController::class, 'show']);

Route::put('/notes/{note}', [NoteController::class, 'update']);

Route::delete('/notes/{note}', [NoteController::class, 'destroy']);

Route::post('/notes/search', [NoteController::class, 'search']);

Route::post('/notes/{note}/summary', [NoteController::class, 'generateSummary']);