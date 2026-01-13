<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacklogSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'space_url',
        'api_key',
        'selected_project_id',
        'selected_project_name',
        'is_connected',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'is_connected' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * BacklogのベースAPIURLを取得
     */
    public function getApiBaseUrlAttribute(): ?string
    {
        if (!$this->space_url) {
            return null;
        }
        
        $url = rtrim($this->space_url, '/');
        return $url . '/api/v2';
    }
}
