<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarSyncLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'schedule_id', 'provider_event_id', 'operation', 'sync_status', 'error_code', 'synced_at'];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
