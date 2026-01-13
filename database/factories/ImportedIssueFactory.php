<?php

namespace Database\Factories;

use App\Models\ImportedIssue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImportedIssue>
 */
class ImportedIssueFactory extends Factory
{
    protected $model = ImportedIssue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $priorities = ['高', '中', '低'];
        $statuses = ['未対応', '処理中', '完了', '処理済み'];
        
        return [
            'user_id' => User::factory(),
            'backlog_issue_id' => $this->faker->unique()->numberBetween(1000, 9999),
            'issue_key' => 'PROJ-' . $this->faker->unique()->numberBetween(1, 999),
            'summary' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(),
            'issue_type' => $this->faker->randomElement(['タスク', 'バグ', '要望']),
            'issue_type_color' => $this->faker->hexColor(),
            'priority' => $this->faker->randomElement($priorities),
            'status' => $this->faker->randomElement($statuses),
            'status_color' => $this->faker->hexColor(),
            'due_date' => $this->faker->optional(0.7)->dateTimeBetween('now', '+30 days'),
            'start_date' => $this->faker->optional(0.5)->dateTimeBetween('-7 days', 'now'),
            'estimated_hours' => $this->faker->optional(0.6)->randomFloat(1, 1, 8),
            'actual_hours' => $this->faker->optional(0.3)->randomFloat(1, 0.5, 10),
            'milestone' => $this->faker->optional(0.4)->word(),
            'assignee_name' => $this->faker->optional(0.8)->name(),
            'backlog_url' => $this->faker->url(),
        ];
    }

    /**
     * 未対応状態
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => '未対応',
        ]);
    }

    /**
     * 完了状態
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => '完了',
        ]);
    }

    /**
     * 高優先度
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => '高',
        ]);
    }

    /**
     * 期限切れ
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-7 days', '-1 day'),
        ]);
    }
}
