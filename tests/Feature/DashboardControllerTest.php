<?php

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('DashboardController', function () {
    
    describe('index', function () {
        
        it('requires authentication', function () {
            $this->get('/dashboard')
                ->assertRedirect('/login');
        });
        
        it('displays the dashboard', function () {
            $this->actingAs($this->user)
                ->get('/dashboard')
                ->assertStatus(200)
                ->assertViewIs('dashboard');
        });
        
        it('shows today plans', function () {
            StudyPlan::factory()->create([
                'user_id' => $this->user->id,
                'title' => '今日のタスク',
                'scheduled_date' => today(),
                'status' => 'planned',
            ]);
            
            $this->actingAs($this->user)
                ->get('/dashboard')
                ->assertStatus(200)
                ->assertSee('今日のタスク');
        });
        
        it('shows progress statistics', function () {
            StudyPlan::factory()->count(2)->create([
                'user_id' => $this->user->id,
                'scheduled_date' => today(),
                'status' => 'completed',
            ]);
            StudyPlan::factory()->create([
                'user_id' => $this->user->id,
                'scheduled_date' => today(),
                'status' => 'planned',
            ]);
            
            $this->actingAs($this->user)
                ->get('/dashboard')
                ->assertStatus(200)
                ->assertViewHas('progress', function ($progress) {
                    return $progress['total'] === 3 &&
                           $progress['completed'] === 2;
                });
        });
        
        it('shows upcoming deadlines', function () {
            ImportedIssue::factory()->create([
                'user_id' => $this->user->id,
                'summary' => '期限間近の課題',
                'due_date' => now()->addDays(3),
            ]);
            
            $this->actingAs($this->user)
                ->get('/dashboard')
                ->assertStatus(200)
                ->assertSee('期限間近の課題');
        });
        
        it('shows recent activity', function () {
            StudyPlan::factory()->create([
                'user_id' => $this->user->id,
                'title' => '完了したタスク',
                'status' => 'completed',
                'updated_at' => now()->subHours(1),
            ]);
            
            $this->actingAs($this->user)
                ->get('/dashboard')
                ->assertStatus(200)
                ->assertViewHas('recentActivity');
        });
        
        it('only shows own data', function () {
            $otherUser = User::factory()->create();
            
            StudyPlan::factory()->create([
                'user_id' => $otherUser->id,
                'title' => '他ユーザーのタスク',
                'scheduled_date' => today(),
            ]);
            
            $this->actingAs($this->user)
                ->get('/dashboard')
                ->assertStatus(200)
                ->assertDontSee('他ユーザーのタスク');
        });
        
    });
    
});
