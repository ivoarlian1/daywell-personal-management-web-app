<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'author', 'isbn', 'description', 'total_pages', 'current_page', 'status', 'started_at', 'finished_at', 'cover_path', 'rating'];

    protected $casts = [
        'started_at' => 'date',
        'finished_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notes()
    {
        return $this->hasMany(BookNote::class);
    }
}
