<?php

namespace Database\Factories;

use App\Models\BacklogSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BacklogSetting>
 */
class BacklogSettingFactory extends Factory
{
    protected $model = BacklogSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'space_url' => 'https://' . $this->faker->domainWord() . '.backlog.com',
            'api_key' => $this->faker->regexify('[A-Za-z0-9]{40}'),
            'selected_project_id' => $this->faker->optional(0.7)->numerify('####'),
            'selected_project_name' => $this->faker->optional(0.7)->words(2, true),
            'is_connected' => $this->faker->boolean(80),
            'last_synced_at' => $this->faker->optional(0.6)->dateTimeBetween('-7 days', 'now'),
        ];
    }

    /**
     * 接続済み状態
     */
    public function connected(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_connected' => true,
            'last_synced_at' => now(),
        ]);
    }

    /**
     * 未接続状態
     */
    public function disconnected(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_connected' => false,
            'last_synced_at' => null,
        ]);
    }
}
