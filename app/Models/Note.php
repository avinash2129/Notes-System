<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'embedding',
        'summary',
    ];

    protected $casts = [
        'embedding' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the embedding as an array if stored as JSON string
     */
    public function getEmbedding(): array
    {
        if (is_string($this->attributes['embedding'] ?? null)) {
            return json_decode($this->attributes['embedding'], true) ?? [];
        }
        return $this->attributes['embedding'] ?? [];
    }

    /**
     * Set embedding from array
     */
    public function setEmbedding(array $embedding): void
    {
        $this->attributes['embedding'] = json_encode($embedding);
    }
}
