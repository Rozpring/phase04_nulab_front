/**
 * NextLog App Store - Alpine.js グローバルストア
 * バックエンドAPI完成までの仮実装（localStorage使用）
 */

// ============================================
// モックデータ
// ============================================
const MOCK_PLANS = [
    {
        id: 1,
        title: 'Laravel認証機能を理解する',
        description: 'Laravel Breezeを使った認証機能の実装方法を学習する',
        plan_type: 'study',
        scheduled_date: new Date().toISOString().split('T')[0],
        scheduled_time: '09:00',
        end_time: '11:00',
        duration_minutes: 120,
        priority: 9,
        status: 'planned',
        ai_reason: '優先度が高いため早めに着手することを推奨。',
        issue_key: 'STUDY-1'
    },
    {
        id: 2,
        title: 'Eloquent ORMの基礎を学ぶ',
        description: 'Eloquentを使ったデータベース操作の基本を習得',
        plan_type: 'study',
        scheduled_date: new Date().toISOString().split('T')[0],
        scheduled_time: '11:00',
        end_time: '12:00',
        duration_minutes: 60,
        priority: 5,
        status: 'in_progress',
        ai_reason: 'バランスの取れたスケジュールのため配置。',
        issue_key: 'STUDY-2'
    },
    {
        id: 3,
        title: '昼休み',
        description: null,
        plan_type: 'break',
        scheduled_date: new Date().toISOString().split('T')[0],
        scheduled_time: '12:00',
        end_time: '13:00',
        duration_minutes: 60,
        priority: 10,
        status: 'planned',
        ai_reason: '午後の作業効率を維持するための休憩時間',
        issue_key: null
    },
    {
        id: 4,
        title: 'API設計ドキュメントの作成',
        description: 'RESTful APIの設計書を作成',
        plan_type: 'work',
        scheduled_date: new Date().toISOString().split('T')[0],
        scheduled_time: '13:00',
        end_time: '15:00',
        duration_minutes: 120,
        priority: 9,
        status: 'planned',
        ai_reason: '期限が近づいているため計画的に進める必要あり。',
        issue_key: 'WORK-1'
    },
    {
        id: 5,
        title: 'ユーザー管理機能の実装',
        description: 'ユーザーの登録、編集、削除、一覧表示機能を実装',
        plan_type: 'work',
        scheduled_date: new Date().toISOString().split('T')[0],
        scheduled_time: '15:00',
        end_time: '17:00',
        duration_minutes: 120,
        priority: 9,
        status: 'completed',
        ai_reason: '優先度が高いため早めに着手することを推奨。',
        issue_key: 'DEV-1'
    }
];

const MOCK_ISSUES = [
    {
        id: 1,
        issue_key: 'STUDY-1',
        summary: 'Laravel認証機能を理解する',
        description: 'Laravel Breezeを使った認証機能の実装方法を学習する。セッション管理、ミドルウェア、ガードについて理解を深める。',
        issue_type: 'タスク',
        priority: '高',
        status: '未対応',
        due_date: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        estimated_hours: 4,
        backlog_url: 'https://example.backlog.com/view/STUDY-1'
    },
    {
        id: 2,
        issue_key: 'STUDY-2',
        summary: 'Eloquent ORMの基礎を学ぶ',
        description: 'Eloquentを使ったデータベース操作の基本を習得。リレーション、クエリビルダ、アクセサ・ミューテタについて学ぶ。',
        issue_type: 'タスク',
        priority: '中',
        status: '処理中',
        due_date: new Date(Date.now() + 5 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        estimated_hours: 3,
        backlog_url: 'https://example.backlog.com/view/STUDY-2'
    },
    {
        id: 3,
        issue_key: 'WORK-1',
        summary: 'API設計ドキュメントの作成',
        description: 'RESTful APIの設計書を作成。エンドポイント、リクエスト/レスポンス形式、認証方式を定義する。',
        issue_type: 'タスク',
        priority: '高',
        status: '未対応',
        due_date: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        estimated_hours: 3,
        backlog_url: 'https://example.backlog.com/view/WORK-1'
    }
];

// ============================================
// ユーティリティ関数
// ============================================
function generateId() {
    return Date.now() + Math.random().toString(36).substr(2, 9);
}

function loadFromStorage(key, defaultValue) {
    try {
        const stored = localStorage.getItem(key);
        return stored ? JSON.parse(stored) : defaultValue;
    } catch {
        return defaultValue;
    }
}

function saveToStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        console.warn('localStorage save failed:', e);
    }
}

// ============================================
// Alpine.js ストア定義
// ============================================
export function initStores(Alpine) {
    // -------------------
    // Plans Store (計画管理)
    // サーバーのStudyPlanテーブルからデータを取得
    // -------------------
    Alpine.store('plans', {
        items: [],
        loading: false,
        initialized: false,

        async init() {
            if (this.initialized) return;
            this.initialized = true;
            await this.fetchFromServer();
        },

        async fetchFromServer() {
            this.loading = true;
            try {
                const response = await fetch('/api/planning/daily', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                });

                if (!response.ok) {
                    throw new Error('API request failed');
                }

                const data = await response.json();
                if (data.success && data.data?.lanes) {
                    // レーンデータをフラット配列に変換
                    const lanes = data.data.lanes;
                    const plans = [];

                    Object.entries(lanes).forEach(([status, items]) => {
                        items.forEach(item => {
                            plans.push({
                                id: item.id,
                                title: item.summary,
                                plan_type: 'work', // デフォルト
                                scheduled_date: item.target_date,
                                scheduled_time: '09:00', // TODO: APIから取得
                                end_time: '10:00',
                                duration_minutes: item.duration_minutes || 60,
                                status: item.lane_status || status,
                                ai_reason: item.ai_comment,
                                issue_key: item.issue_key
                            });
                        });
                    });

                    this.items = plans;
                    console.log('Plans loaded from server:', plans.length);
                } else {
                    throw new Error('Invalid response format');
                }
            } catch (error) {
                console.warn('Failed to fetch plans from server, using localStorage fallback:', error);
                // フォールバック: localStorageから読み込み
                this.items = loadFromStorage('lask_plans', MOCK_PLANS);
            } finally {
                this.loading = false;
            }
        },

        getByStatus(status) {
            return this.items.filter(p => p.status === status);
        },

        getToday() {
            const today = new Date().toISOString().split('T')[0];
            return this.items
                .filter(p => p.scheduled_date === today)
                .sort((a, b) => (a.scheduled_time || '').localeCompare(b.scheduled_time || ''));
        },

        async updateStatus(id, newStatus) {
            const index = this.items.findIndex(p => p.id === id);
            if (index === -1) return;

            // 楽観的更新：まずUIを更新
            const oldStatus = this.items[index].status;
            this.items = this.items.map((p, i) =>
                i === index ? { ...p, status: newStatus } : p
            );

            // IDが整数（DBに保存済み）の場合のみAPIを呼び出し
            if (typeof id !== 'number' || !Number.isInteger(id)) {
                console.log('Skipping API call for client-side ID:', id);
                return;
            }

            // APIを呼び出し
            try {
                const response = await fetch(`/api/planning/tasks/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                if (!response.ok) {
                    throw new Error('API request failed');
                }

                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Update failed');
                }
            } catch (error) {
                console.error('Failed to update status:', error);
                // エラー時はロールバック
                this.items = this.items.map((p, i) =>
                    i === index ? { ...p, status: oldStatus } : p
                );
                // 通知を表示
                Alpine.store('notifications')?.showToast('ステータスの更新に失敗しました', 'error');
            }
        },

        update(id, data) {
            const index = this.items.findIndex(p => p.id === id);
            if (index !== -1) {
                this.items[index] = { ...this.items[index], ...data };
            }
        },

        add(plan) {
            const newPlan = {
                id: generateId(),
                status: 'planned',
                ...plan
            };
            this.items.push(newPlan);
            return newPlan;
        },

        remove(id) {
            this.items = this.items.filter(p => p.id !== id);
        },

        async refresh() {
            await this.fetchFromServer();
        }
    });

    // -------------------
    // Issues Store (課題管理)
    // -------------------
    Alpine.store('issues', {
        items: loadFromStorage('lask_issues', MOCK_ISSUES),

        getById(id) {
            return this.items.find(i => i.id === id);
        },

        getByKey(key) {
            return this.items.find(i => i.issue_key === key);
        }
    });

    // -------------------
    // Theme Store (テーマ管理)
    // -------------------
    Alpine.store('theme', {
        // theme-toggleと同じキー('theme')を使用し、デフォルトは'light'
        mode: localStorage.getItem('theme') || 'light',

        init() {
            // 初期化はapp.blade.phpで行うため、ここではapply()を呼ばない
            // システム設定変更の監視も不要（theme-toggleで対応）
        },

        set(mode) {
            this.mode = mode;
            localStorage.setItem('theme', mode);
            this.apply();
        },

        apply() {
            const isDark = this.mode === 'dark' ||
                (this.mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);
        },

        toggle() {
            const modes = ['light', 'dark', 'system'];
            const currentIndex = modes.indexOf(this.mode);
            this.set(modes[(currentIndex + 1) % modes.length]);
        }
    });

    // -------------------
    // Widgets Store (ウィジェット設定)
    // -------------------
    Alpine.store('widgets', {
        state: loadFromStorage('lask_widgets', {
            progress: { visible: true, collapsed: false },
            todayPlans: { visible: true, collapsed: false },
            weekPreview: { visible: true, collapsed: false },
            quickLinks: { visible: true, collapsed: false }
        }),

        toggle(widgetId, property) {
            if (this.state[widgetId]) {
                this.state[widgetId][property] = !this.state[widgetId][property];
                this.save();
            }
        },

        save() {
            saveToStorage('lask_widgets', this.state);
        }
    });

    // -------------------
    // Pomodoro Store (タイマー)
    // -------------------
    Alpine.store('pomodoro', {
        isRunning: false,
        isBreak: false,
        timeLeft: 25 * 60, // 秒
        workDuration: 25 * 60,
        breakDuration: 5 * 60,
        currentPlanId: null,
        intervalId: null,

        start(planId = null) {
            this.currentPlanId = planId;
            this.isRunning = true;
            this.isBreak = false;
            this.timeLeft = this.workDuration;
            this.tick();
        },

        tick() {
            if (this.intervalId) clearInterval(this.intervalId);
            this.intervalId = setInterval(() => {
                if (this.timeLeft > 0) {
                    this.timeLeft--;
                } else {
                    this.onComplete();
                }
            }, 1000);
        },

        pause() {
            this.isRunning = false;
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        },

        resume() {
            this.isRunning = true;
            this.tick();
        },

        reset() {
            this.pause();
            this.timeLeft = this.isBreak ? this.breakDuration : this.workDuration;
        },

        onComplete() {
            this.pause();
            // 音声通知
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(this.isBreak ? '休憩終了！' : '作業完了！', {
                    body: this.isBreak ? '次の作業を始めましょう' : '5分間の休憩をとりましょう'
                });
            }
            // 作業/休憩を切り替え
            this.isBreak = !this.isBreak;
            this.timeLeft = this.isBreak ? this.breakDuration : this.workDuration;
        },

        get formattedTime() {
            const mins = Math.floor(this.timeLeft / 60);
            const secs = this.timeLeft % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
    });

    // -------------------
    // Notifications Store (通知管理)
    // -------------------
    Alpine.store('notifications', {
        permission: 'default', // 'default', 'granted', 'denied'
        enabled: loadFromStorage('lask_notifications_enabled', true),
        settings: loadFromStorage('lask_notification_settings', {
            planStart: true,        // 計画開始時刻の通知
            planReminder: true,     // 計画5分前リマインダー
            pomodoroEnd: true,      // ポモドーロ終了通知
            dailySummary: false,    // 毎日のサマリー通知
            reminderMinutes: 5      // リマインダーの分数
        }),
        scheduledTimers: [],
        toasts: [], // アプリ内通知用

        init() {
            // ブラウザの通知権限を確認
            if ('Notification' in window) {
                this.permission = Notification.permission;
            }
            // 有効な場合は計画通知をスケジュール
            if (this.enabled && this.permission === 'granted') {
                this.scheduleTodayNotifications();
            }
        },

        async requestPermission() {
            if (!('Notification' in window)) {
                this.showToast('このブラウザは通知をサポートしていません', 'error');
                return false;
            }

            try {
                const result = await Notification.requestPermission();
                this.permission = result;
                if (result === 'granted') {
                    this.showToast('通知が有効になりました！', 'success');
                    this.scheduleTodayNotifications();
                    return true;
                } else {
                    this.showToast('通知が拒否されました', 'warning');
                    return false;
                }
            } catch (e) {
                console.error('Notification permission error:', e);
                return false;
            }
        },

        updateSettings(newSettings) {
            this.settings = { ...this.settings, ...newSettings };
            saveToStorage('lask_notification_settings', this.settings);
            // 通知を再スケジュール
            this.clearScheduled();
            if (this.enabled && this.permission === 'granted') {
                this.scheduleTodayNotifications();
            }
        },

        toggle() {
            this.enabled = !this.enabled;
            saveToStorage('lask_notifications_enabled', this.enabled);
            if (this.enabled && this.permission === 'granted') {
                this.scheduleTodayNotifications();
                this.showToast('通知を有効にしました', 'success');
            } else {
                this.clearScheduled();
                this.showToast('通知を無効にしました', 'info');
            }
        },

        scheduleTodayNotifications() {
            this.clearScheduled();

            const plans = Alpine.store('plans')?.getToday() || [];
            const now = new Date();

            plans.forEach(plan => {
                if (!plan.scheduled_time || plan.status === 'completed' || plan.status === 'skipped') {
                    return;
                }

                const [hours, minutes] = plan.scheduled_time.split(':').map(Number);
                const planTime = new Date();
                planTime.setHours(hours, minutes, 0, 0);

                // 計画開始通知
                if (this.settings.planStart && planTime > now) {
                    const delay = planTime.getTime() - now.getTime();
                    const timerId = setTimeout(() => {
                        this.send(`📚 ${plan.title}`, {
                            body: `${plan.scheduled_time} - 計画の開始時間です`,
                            tag: `plan-start-${plan.id}`,
                            icon: '/favicon.ico'
                        });
                    }, delay);
                    this.scheduledTimers.push(timerId);
                }

                // リマインダー通知
                if (this.settings.planReminder) {
                    const reminderTime = new Date(planTime.getTime() - this.settings.reminderMinutes * 60 * 1000);
                    if (reminderTime > now) {
                        const delay = reminderTime.getTime() - now.getTime();
                        const timerId = setTimeout(() => {
                            this.send(`⏰ まもなく開始`, {
                                body: `${plan.title} が${this.settings.reminderMinutes}分後に始まります`,
                                tag: `plan-reminder-${plan.id}`,
                                icon: '/favicon.ico'
                            });
                        }, delay);
                        this.scheduledTimers.push(timerId);
                    }
                }
            });
        },

        clearScheduled() {
            this.scheduledTimers.forEach(id => clearTimeout(id));
            this.scheduledTimers = [];
        },

        send(title, options = {}) {
            if (this.permission !== 'granted' || !this.enabled) {
                return;
            }

            try {
                const notification = new Notification(title, {
                    icon: options.icon || '/favicon.ico',
                    badge: '/favicon.ico',
                    ...options
                });

                notification.onclick = () => {
                    window.focus();
                    notification.close();
                };

                // 音声も鳴らす（オプション）
                this.playSound();

            } catch (e) {
                console.error('Notification error:', e);
            }
        },

        playSound() {
            try {
                const audio = new Audio('/sounds/notification.mp3');
                audio.volume = 0.3;
                audio.play().catch(() => { }); // 音声がない場合は無視
            } catch (e) {
                // 音声ファイルがない場合は無視
            }
        },

        // アプリ内トースト通知
        showToast(message, type = 'info', duration = 3000) {
            const id = generateId();
            this.toasts.push({ id, message, type });

            setTimeout(() => {
                this.dismissToast(id);
            }, duration);
        },

        dismissToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    });

    // -------------------
    // UI Store (モーダル・ショートカット)
    // -------------------
    Alpine.store('ui', {
        modals: {
            planEdit: { open: false, plan: null },
            planCreate: { open: false },
            issueDetail: { open: false, issue: null },
            shortcuts: { open: false },
            help: { open: false }
        },

        openModal(name, data = {}) {
            if (this.modals[name]) {
                Object.assign(this.modals[name], { open: true, ...data });
            }
        },

        closeModal(name) {
            if (this.modals[name]) {
                this.modals[name].open = false;
            }
        },

        closeAllModals() {
            Object.keys(this.modals).forEach(name => {
                this.modals[name].open = false;
            });
        }
    });
}

// ============================================
// キーボードショートカット
// ============================================
export function initKeyboardShortcuts(Alpine) {
    document.addEventListener('keydown', (e) => {
        // モーダルが開いている場合はEscで閉じる
        if (e.key === 'Escape') {
            Alpine.store('ui').closeAllModals();
            return;
        }

        // 入力フィールドがフォーカス中は無視
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            return;
        }

        const isMod = e.metaKey || e.ctrlKey;

        // ? - ショートカットヘルプ
        if (e.key === '?' || (e.key === '/' && e.shiftKey)) {
            e.preventDefault();
            Alpine.store('ui').openModal('shortcuts');
        }

        // Cmd/Ctrl + N - 新規計画
        if (isMod && e.key === 'n') {
            e.preventDefault();
            Alpine.store('ui').openModal('planCreate');
        }

        // Cmd/Ctrl + D - ダッシュボード
        if (isMod && e.key === 'd') {
            e.preventDefault();
            window.location.href = '/dashboard';
        }

        // Cmd/Ctrl + P - 計画ダッシュボード
        if (isMod && e.key === 'p') {
            e.preventDefault();
            window.location.href = '/planning';
        }

        // H - ヘルプ
        if (e.key === 'h' && !isMod) {
            e.preventDefault();
            Alpine.store('ui').openModal('help');
        }

        // Space - ポモドーロ開始/一時停止
        if (e.key === ' ' && !isMod) {
            const pomodoro = Alpine.store('pomodoro');
            if (pomodoro.isRunning) {
                pomodoro.pause();
            } else if (pomodoro.timeLeft > 0) {
                pomodoro.resume();
            }
        }

        // T - テーマ切り替え
        if (e.key === 't' && !isMod) {
            e.preventDefault();
            Alpine.store('theme').toggle();
        }
    });
}
