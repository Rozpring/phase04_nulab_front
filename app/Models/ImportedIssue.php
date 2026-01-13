<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportedIssue extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'backlog_issue_id',
        'issue_key',
        'summary',
        'description',
        'issue_type',
        'issue_type_color',
        'priority',
        'status',
        'status_color',
        'due_date',
        'start_date',
        'estimated_hours',
        'actual_hours',
        'milestone',
        'assignee_name',
        'project_id',
        'backlog_url',
        'backlog_created_at',
        'backlog_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'start_date' => 'date',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'backlog_created_at' => 'datetime',
            'backlog_updated_at' => 'datetime',
        ];
    }

    /**
     * 優先度の定数
     */
    public const PRIORITIES = [
        '高' => 'high',
        '中' => 'medium',
        '低' => 'low',
    ];

    /**
     * ステータスの色マッピング
     */
    public const STATUS_COLORS = [
        '未対応' => 'gray',
        '処理中' => 'sky',
        '処理済み' => 'emerald',
        '完了' => 'emerald',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyPlans(): HasMany
    {
        return $this->hasMany(StudyPlan::class);
    }

    /**
     * 優先度の内部表現を取得
     */
    public function getPriorityLevelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? 'medium';
    }

    /**
     * ステータスの色を取得
     */
    public function getStatusColorClassAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    /**
     * 期限切れかどうか
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        return $this->due_date->isPast() && !in_array($this->status, ['完了', '処理済み']);
    }

    /**
     * 期限までの日数
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        return now()->startOfDay()->diffInDays($this->due_date, false);
    }
}
