<?php

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('AnalysisController', function () {
    
    describe('index', function () {
        
        it('requires authentication', function () {
            $this->get('/analysis')
                ->assertRedirect('/login');
        });
        
        it('displays the analysis dashboard', function () {
            $this->actingAs($this->user)
                ->get('/analysis')
                ->assertStatus(200)
                ->assertViewIs('analysis.index');
        });
        
        it('shows stats and advice', function () {
            // いくつかのデータを作成
            ImportedIssue::factory()->count(3)->create([
                'user_id' => $this->user->id,
            ]);
            StudyPlan::factory()->count(2)->create([
                'user_id' => $this->user->id,
                'status' => 'completed',
            ]);
            
            $this->actingAs($this->user)
                ->get('/analysis')
                ->assertStatus(200)
                ->assertViewHas('stats')
                ->assertViewHas('patterns')
                ->assertViewHas('advice')
                ->assertViewHas('weeklyData');
        });
        
    });
    
    describe('report', function () {
        
        it('requires authentication', function () {
            $this->get('/analysis/report')
                ->assertRedirect('/login');
        });
        
        it('displays the report page', function () {
            $this->actingAs($this->user)
                ->get('/analysis/report')
                ->assertStatus(200)
                ->assertViewIs('analysis.report');
        });
        
    });
    
    describe('apiAdvice', function () {
        
        it('requires authentication', function () {
            $this->postJson('/api/analysis/advice')
                ->assertStatus(401);
        });
        
        it('returns advice data', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/analysis/advice');
            
            $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'cached',
                    'data' => [
                        'target_date',
                        'advice',
                    ],
                ]);
        });
        
        it('returns exactly 3 advice items', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/analysis/advice');
            
            $response->assertStatus(200);
            expect(count($response->json('data.advice')))->toBe(3);
        });
        
        it('accepts date parameter', function () {
            $targetDate = '2026-01-15';
            
            $response = $this->actingAs($this->user)
                ->postJson('/api/analysis/advice', ['date' => $targetDate]);
            
            $response->assertStatus(200)
                ->assertJsonPath('data.target_date', $targetDate);
        });
        
        it('can force refresh cache', function () {
            // 初回リクエスト
            $this->actingAs($this->user)
                ->postJson('/api/analysis/advice')
                ->assertJson(['cached' => false]);
            
            // 2回目（キャッシュから）
            $this->actingAs($this->user)
                ->postJson('/api/analysis/advice')
                ->assertJson(['cached' => true]);
            
            // リフレッシュを指定
            $this->actingAs($this->user)
                ->postJson('/api/analysis/advice', ['refresh' => true])
                ->assertJson(['cached' => false]);
        });
        
        it('includes warning for overdue issues', function () {
            ImportedIssue::factory()->create([
                'user_id' => $this->user->id,
                'due_date' => now()->subDays(2),
                'status' => '未対応',
            ]);
            
            $response = $this->actingAs($this->user)
                ->postJson('/api/analysis/advice', ['refresh' => true]);
            
            $advice = $response->json('data.advice');
            $urgentAdvice = collect($advice)->firstWhere('tag', '緊急');
            
            expect($urgentAdvice)->not->toBeNull();
        });
        
    });
    
});
