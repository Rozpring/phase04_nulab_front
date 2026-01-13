<?php

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('PlanningController', function () {
    
    describe('index', function () {
        
        it('requires authentication', function () {
            $this->get('/planning')
                ->assertRedirect('/login');
        });
        
        it('displays the planning dashboard', function () {
            $this->actingAs($this->user)
                ->get('/planning')
                ->assertStatus(200)
                ->assertViewIs('planning.index');
        });
        
        it('shows imported issues', function () {
            $issue = ImportedIssue::factory()->create([
                'user_id' => $this->user->id,
                'summary' => 'テスト課題',
                'status' => '未対応',
            ]);
            
            $this->actingAs($this->user)
                ->get('/planning')
                ->assertStatus(200)
                ->assertSee('テスト課題');
        });
        
        it('shows today plans', function () {
            $plan = StudyPlan::factory()->create([
                'user_id' => $this->user->id,
                'title' => '今日のタスク',
                'scheduled_date' => today(),
            ]);
            
            $this->actingAs($this->user)
                ->get('/planning')
                ->assertStatus(200)
                ->assertSee('今日のタスク');
        });
        
    });
    
    describe('generate', function () {
        
        it('requires authentication', function () {
            $this->post('/planning/generate')
                ->assertRedirect('/login');
        });
        
        it('redirects with warning when no issues', function () {
            $this->actingAs($this->user)
                ->post('/planning/generate')
                ->assertRedirect('/planning')
                ->assertSessionHas('warning');
        });
        
        it('generates plans from imported issues', function () {
            ImportedIssue::factory()->create([
                'user_id' => $this->user->id,
                'status' => '未対応',
                'estimated_hours' => 2,
            ]);
            
            $this->actingAs($this->user)
                ->post('/planning/generate')
                ->assertRedirect('/planning')
                ->assertSessionHas('success');
            
            expect(StudyPlan::where('user_id', $this->user->id)->count())->toBeGreaterThan(0);
        });
        
    });
    
    describe('apiGenerate', function () {
        
        it('requires authentication', function () {
            $this->postJson('/api/planning/generate')
                ->assertStatus(401);
        });
        
        it('returns error when no issues', function () {
            $this->actingAs($this->user)
                ->postJson('/api/planning/generate')
                ->assertStatus(400)
                ->assertJson(['success' => false]);
        });
        
        it('returns generated plans', function () {
            ImportedIssue::factory()->create([
                'user_id' => $this->user->id,
                'status' => '未対応',
                'estimated_hours' => 2,
            ]);
            
            $response = $this->actingAs($this->user)
                ->postJson('/api/planning/generate');
            
            $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'plans',
                    'target_date',
                ]);
        });
        
    });
    
    describe('updateStatus', function () {
        
        it('requires authentication', function () {
            $plan = StudyPlan::factory()->create();
            
            $this->patchJson("/api/planning/tasks/{$plan->id}/status", ['status' => 'completed'])
                ->assertStatus(401);
        });
        
        it('prevents updating other users plans', function () {
            $otherUser = User::factory()->create();
            $plan = StudyPlan::factory()->create([
                'user_id' => $otherUser->id,
            ]);
            
            $this->actingAs($this->user)
                ->patchJson("/api/planning/tasks/{$plan->id}/status", ['status' => 'completed'])
                ->assertStatus(403);
        });
        
        it('updates plan status', function () {
            $plan = StudyPlan::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'planned',
            ]);
            
            $this->actingAs($this->user)
                ->patchJson("/api/planning/tasks/{$plan->id}/status", ['status' => 'completed'])
                ->assertStatus(200)
                ->assertJson(['success' => true]);
            
            expect($plan->fresh()->status)->toBe('completed');
        });
        
        it('validates status value', function () {
            $plan = StudyPlan::factory()->create([
                'user_id' => $this->user->id,
            ]);
            
            $this->actingAs($this->user)
                ->patchJson("/api/planning/tasks/{$plan->id}/status", ['status' => 'invalid'])
                ->assertStatus(422);
        });
        
    });
    
    describe('timeline', function () {
        
        it('displays timeline view', function () {
            $this->actingAs($this->user)
                ->get('/planning/timeline')
                ->assertStatus(200)
                ->assertViewIs('planning.timeline');
        });
        
    });
    
    describe('calendar', function () {
        
        it('displays calendar view', function () {
            $this->actingAs($this->user)
                ->get('/planning/calendar')
                ->assertStatus(200)
                ->assertViewIs('planning.calendar');
        });
        
    });
    
    describe('gantt', function () {
        
        it('displays gantt view', function () {
            $this->actingAs($this->user)
                ->get('/planning/gantt')
                ->assertStatus(200)
                ->assertViewIs('planning.gantt');
        });
        
    });
    
});
