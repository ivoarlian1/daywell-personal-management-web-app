<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleIntegration extends Model
{
    use HasFactory;

    protected $hidden = ['encrypted_access_token', 'encrypted_refresh_token'];

    protected $fillable = ['user_id', 'provider', 'google_account_email', 'encrypted_access_token', 'encrypted_refresh_token', 'token_expires_at', 'scopes', 'calendar_id', 'status'];

    protected function casts(): array
    {
        return [
            'encrypted_access_token' => 'encrypted',
            'encrypted_refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'scopes' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
