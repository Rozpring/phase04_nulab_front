<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyPlan extends Model
{
    protected $fillable = [
        'user_id',
        'imported_issue_id',
        'title',
        'description',
        'plan_type',
        'scheduled_date',
        'scheduled_time',
        'end_time',
        'duration_minutes',
        'priority',
        'ai_reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'scheduled_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
        ];
    }

    /**
     * 計画タイプの定数
     */
    public const PLAN_TYPES = [
        'study' => ['label' => '学習', 'icon' => 'book-open', 'color' => 'indigo'],
        'work' => ['label' => '作業', 'icon' => 'briefcase', 'color' => 'sky'],
        'break' => ['label' => '休憩', 'icon' => 'sun', 'color' => 'amber'],
        'review' => ['label' => '復習', 'icon' => 'arrow-path', 'color' => 'purple'],
    ];

    /**
     * ステータスの定数
     */
    public const STATUSES = [
        'planned' => ['label' => '予定', 'color' => 'gray'],
        'in_progress' => ['label' => '進行中', 'color' => 'sky'],
        'completed' => ['label' => '完了', 'color' => 'emerald'],
        'skipped' => ['label' => 'スキップ', 'color' => 'rose'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function importedIssue(): BelongsTo
    {
        return $this->belongsTo(ImportedIssue::class);
    }

    /**
     * 計画タイプの表示情報を取得
     */
    public function getPlanTypeInfoAttribute(): array
    {
        return self::PLAN_TYPES[$this->plan_type] ?? self::PLAN_TYPES['study'];
    }

    /**
     * ステータスの表示情報を取得
     */
    public function getStatusInfoAttribute(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['planned'];
    }

    /**
     * 時間表示用フォーマット
     */
    public function getTimeRangeAttribute(): string
    {
        $start = $this->scheduled_time ? $this->scheduled_time->format('H:i') : '--:--';
        $end = $this->end_time ? $this->end_time->format('H:i') : '--:--';
        return "{$start} - {$end}";
    }

    /**
     * 所要時間の表示
     */
    public function getDurationDisplayAttribute(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}時間{$minutes}分";
        } elseif ($hours > 0) {
            return "{$hours}時間";
        } else {
            return "{$minutes}分";
        }
    }
}
