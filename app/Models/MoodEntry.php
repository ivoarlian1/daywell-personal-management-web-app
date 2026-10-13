<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoodEntry extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'entry_date', 'mood_score', 'notes'];

    protected $casts = [
        'entry_date' => 'date',
        'mood_score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
