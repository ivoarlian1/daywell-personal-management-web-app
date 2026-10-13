<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'timezone', 'locale', 'avatar', 'google_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function diaryEntries()
    {
        return $this->hasMany(DiaryEntry::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function gratitudeItems()
    {
        return $this->hasMany(GratitudeItem::class);
    }

    public function moodEntries()
    {
        return $this->hasMany(MoodEntry::class);
    }

    public function todos()
    {
        return $this->hasMany(Todo::class);
    }

    public function habits()
    {
        return $this->hasMany(Habit::class);
    }

    public function habitLogs()
    {
        return $this->hasMany(HabitLog::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function bookNotes()
    {
        return $this->hasMany(BookNote::class);
    }

    public function googleIntegrations()
    {
        return $this->hasMany(GoogleIntegration::class);
    }

    public function calendarSyncLogs()
    {
        return $this->hasMany(CalendarSyncLog::class);
    }
}
