<?php

namespace Database\Factories;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudyPlan>
 */
class StudyPlanFactory extends Factory
{
    protected $model = StudyPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $planTypes = ['study', 'work', 'break', 'review'];
        $statuses = ['planned', 'in_progress', 'completed', 'skipped'];
        
        $scheduledTime = $this->faker->time('H:i:s');
        $durationMinutes = $this->faker->randomElement([30, 60, 90, 120, 180]);
        
        return [
            'user_id' => User::factory(),
            'imported_issue_id' => null,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional(0.5)->paragraph(),
            'plan_type' => $this->faker->randomElement($planTypes),
            'scheduled_date' => $this->faker->dateTimeBetween('now', '+14 days'),
            'scheduled_time' => $scheduledTime,
            'end_time' => date('H:i:s', strtotime($scheduledTime) + $durationMinutes * 60),
            'duration_minutes' => $durationMinutes,
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement([3, 5, 9, 10]),
            'ai_reason' => $this->faker->optional(0.6)->sentence(),
        ];
    }

    /**
     * 予定状態
     */
    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planned',
        ]);
    }

    /**
     * 進行中
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * 完了状態
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * スキップ状態
     */
    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'skipped',
        ]);
    }

    /**
     * 今日の計画
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_date' => today(),
        ]);
    }

    /**
     * 学習タイプ
     */
    public function study(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => 'study',
        ]);
    }

    /**
     * 作業タイプ
     */
    public function work(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => 'work',
        ]);
    }

    /**
     * 休憩タイプ
     */
    public function break(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => 'break',
        ]);
    }

    /**
     * インポート済み課題に紐付け
     */
    public function forIssue(ImportedIssue $issue): static
    {
        return $this->state(fn (array $attributes) => [
            'imported_issue_id' => $issue->id,
            'user_id' => $issue->user_id,
            'title' => $issue->summary,
        ]);
    }
}
