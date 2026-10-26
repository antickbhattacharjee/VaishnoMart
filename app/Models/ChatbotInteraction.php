<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'user_message',
        'bot_response',
        'detected_intent',
        'retrieved_sources',
        'confidence_score',
    ];

    protected function casts(): array
    {
        return [
            'retrieved_sources' => 'array',
            'confidence_score' => 'decimal:3',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
