@php
    // 初期データの準備
    $year = request('year', $year ?? 2025);
    $month = request('month', $month ?? 12);
    
    $currentDate = \Carbon\Carbon::create($year, $month, 1);
    $daysInMonth = $currentDate->daysInMonth;
@endphp

<x-app-layout>
    {{-- CSRFトークン設定 --}}
    @if(!in_array('csrf-token', array_keys(View::getSections())) && !Request::header('X-CSRF-TOKEN'))
        @push('meta')
            <meta name="csrf-token" content="{{ csrf_token() }}">
        @endpush
    @endif

    {{-- スタイル定義 --}}
    <style>
        .gantt-table { user-select: none; }
        
        /* タスクバーのスタイル */
        .task-bar {
            position: relative; touch-action: none; transition: box-shadow 0.1s;
        }
        .task-bar:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 50;
        }
        .task-bar.dragging {
            opacity: 0.9; cursor: grabbing !important; z-index: 100 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        }

        /* リサイズハンドル */
        .resize-handle {
            position: absolute; top: 0; bottom: 0; width: 12px;
            cursor: col-resize; z-index: 20;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.2s;
        }
        .task-bar:hover .resize-handle { opacity: 1; }
        .resize-handle::after {
            content: ""; width: 4px; height: 16px;
            background-color: rgba(0,0,0,0.2); border-radius: 2px;
        }
        .resize-handle.left { left: 0; }
        .resize-handle.right { right: 0; }
    </style>

    <div class="min-h-screen bg-lask-bg-muted dark:bg-gray-900 pb-20 font-sans">
        
        {{-- ▼ 年・月切り替えヘッダーエリア（デザイン調整版） ▼ --}}
        <div class="max-w-[95%] mx-auto pt-8 pb-6 flex flex-col sm:flex-row items-center justify-between gap-6">
            
            {{-- 年月タイトル --}}
            <div class="text-3xl font-extrabold text-slate-700 dark:text-slate-200 tracking-tight flex items-baseline gap-2">
                {{ $year }}<span class="text-xl text-slate-400 font-medium">年</span>
                {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}<span class="text-xl text-slate-400 font-medium">月</span>
            </div>

            {{-- 切り替えフォーム --}}
            <form method="GET" class="flex items-center gap-3 bg-white dark:bg-gray-800 p-2 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                
                {{-- 年セレクト --}}
                <div class="relative">
                    <select name="year" class="appearance-none bg-slate-50 dark:bg-gray-700 hover:bg-slate-100 border-none text-slate-700 dark:text-gray-200 font-bold rounded-xl py-2 pl-4 pr-8 focus:ring-2 focus:ring-indigo-200 cursor-pointer transition-colors">
                        @for ($y = $year - 2; $y <= $year + 2; $y++)
                            <option value="{{ $y }}" @if($y == $year) selected @endif>{{ $y }}年</option>
                        @endfor
                    </select>
                    {{-- アイコン --}}
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                {{-- 月セレクト --}}
                <div class="relative">
                    <select name="month" class="appearance-none bg-slate-50 dark:bg-gray-700 hover:bg-slate-100 border-none text-slate-700 dark:text-gray-200 font-bold rounded-xl py-2 pl-4 pr-8 focus:ring-2 focus:ring-indigo-200 cursor-pointer transition-colors">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @if($m == $month) selected @endif>{{ $m }}月</option>
                        @endfor
                    </select>
                    {{-- アイコン --}}
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                {{-- 表示ボタン --}}
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-6 rounded-xl shadow-md shadow-indigo-200 dark:shadow-none transition-all transform hover:scale-105 active:scale-95">
                    表示
                </button>
            </form>
        </div>

        {{-- ガントチャート表示エリア --}}
        <div class="max-w-[95%] mx-auto bg-white dark:bg-gray-800 rounded-[30px] shadow-xl overflow-hidden p-6 relative">
            <div class="overflow-x-auto">
                <table id="gantt-table" class="gantt-table min-w-full border-collapse table-fixed">
                    <thead>
                        <tr>
                            <th class="w-64 p-4 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 sticky left-0 z-20"></th>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $date = \Carbon\Carbon::create($year, $month, $d);
                                    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                                    $textColor = match($date->dayOfWeek) { 0 => 'text-red-400', 6 => 'text-blue-400', default => 'text-gray-400 dark:text-gray-500' };
                                @endphp
                                <th class="day-col w-10 min-w-[40px] border-b border-gray-100 dark:border-gray-700 text-center pb-2 pt-4">
                                    <div class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $d }}</div>
                                    <div class="text-[10px] {{ $textColor }}">{{ $weekdays[$date->dayOfWeek] }}</div>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @forelse ($ganttTasks as $task)
                            @php
                                $start = \Carbon\Carbon::parse($task['start_date']);
                                $end = \Carbon\Carbon::parse($task['end_date']);
                                if ($end->lt($currentDate->startOfMonth()) || $start->gt($currentDate->endOfMonth())) continue;
                                $startDay = max(1, $start->day);
                                if ($start->lt($currentDate->startOfMonth())) $startDay = 1;
                                $endDay = min($daysInMonth, $end->day);
                                if ($end->gt($currentDate->endOfMonth())) $endDay = $daysInMonth;
                                $duration = $endDay - $startDay + 1;
                                $barBg = 'bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200';
                            @endphp
                            <tr class="h-12 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="w-64 px-4 py-2 text-sm font-bold text-gray-700 dark:text-gray-300 sticky left-0 z-10 bg-white dark:bg-gray-800 border-r border-gray-100 dark:border-gray-700 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] truncate">
                                    {{ $task['title'] }}
                                </td>
                                @if ($startDay > 1)
                                    <td colspan="{{ $startDay - 1 }}" class="p-0 border-r border-gray-50 dark:border-gray-800 relative"><div class="absolute inset-0 flex">@for($i=0;$i<($startDay-1);$i++)<div class="flex-1 border-r border-gray-50 dark:border-gray-800"></div>@endfor</div></td>
                                @endif
                                <td colspan="{{ $duration }}" class="p-1 relative align-middle border-r border-gray-50 dark:border-gray-800">
                                    <div class="absolute inset-0 flex -z-10">@for($i=0;$i<$duration;$i++)<div class="flex-1 border-r border-gray-50 dark:border-gray-800"></div>@endfor</div>
                                    
                                    {{-- タスクバー (ドラッグ＆リサイズ可能) --}}
                                    <div class="task-bar h-8 rounded-lg {{ $barBg }} flex items-center justify-between px-2 text-xs shadow-sm relative overflow-visible whitespace-nowrap cursor-grab"
                                         title="{{ $task['title'] }}"
                                         onmousedown="startDrag(event, {{ $loop->index }}, 'move')">
                                        
                                        <div class="resize-handle left" onmousedown="startDrag(event, {{ $loop->index }}, 'resize-left')"></div>
                                        
                                        <div class="flex items-center gap-2 overflow-hidden flex-1 pointer-events-none px-2">
                                            <span class="font-bold truncate">{{ $task['title'] }}</span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 opacity-80 flex-shrink-0">{{ $start->day }}〜{{ $end->day }}日</span>
                                        </div>

                                        <div class="resize-handle right" onmousedown="startDrag(event, {{ $loop->index }}, 'resize-right')"></div>
                                    </div>
                                </td>
                                @if ($endDay < $daysInMonth)
                                    <td colspan="{{ $daysInMonth - $endDay }}" class="p-0 border-r border-gray-50 dark:border-gray-800 relative"><div class="absolute inset-0 flex">@for($i=0;$i<($daysInMonth-$endDay);$i++)<div class="flex-1 border-r border-gray-50 dark:border-gray-800"></div>@endfor</div></td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $daysInMonth + 1 }}" class="p-10 text-center text-gray-500">タスクがありません</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- JavaScript制御（ドラッグ＆ドロップ、リサイズ、保存） --}}
    <script>
    const ganttTasks = @json($ganttTasks);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    let isDragging = false;
    let dragMode = null;
    let activeTaskIndex = null;
    let activeElement = null;
    
    let startX = 0;
    let currentX = 0;
    let cellWidth = 0;

    // ドラッグ開始処理
    window.startDrag = function(e, idx, mode) {
        e.stopPropagation();
        e.preventDefault();

        const dayCol = document.querySelector('.day-col');
        if (!dayCol) return;
        cellWidth = dayCol.getBoundingClientRect().width;

        isDragging = true;
        dragMode = mode;
        activeTaskIndex = idx;
        activeElement = e.currentTarget.closest('.task-bar');
        
        if (mode.startsWith('resize')) {
             activeElement = e.target.closest('.task-bar');
        }

        startX = e.clientX;
        activeElement.classList.add('dragging');

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    };

    // マウス移動中の処理
    function onMouseMove(e) {
        if (!isDragging || !activeElement) return;

        currentX = e.clientX;
        const diffX = currentX - startX;
        const diffDays = Math.round(diffX / cellWidth);

        if (dragMode === 'move') {
            activeElement.style.transform = `translateX(${diffDays * cellWidth}px)`;
        } 
        else if (dragMode === 'resize-right') {
             activeElement.style.width = `calc(100% + ${diffX}px)`;
        }
        else if (dragMode === 'resize-left') {
            activeElement.style.transform = `translateX(${diffX}px)`;
            activeElement.style.width = `calc(100% - ${diffX}px)`;
        }
    }

    // ドラッグ終了処理
    function onMouseUp(e) {
        if (!isDragging) return;

        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);

        activeElement.classList.remove('dragging');
        activeElement.style.transform = '';
        activeElement.style.width = '';

        const diffX = e.clientX - startX;
        const diffDays = Math.round(diffX / cellWidth);

        if (diffDays !== 0) {
            saveChanges(activeTaskIndex, diffDays, dragMode);
        }

        isDragging = false;
        activeElement = null;
        activeTaskIndex = null;
    }

    // 変更内容の保存処理
    function saveChanges(idx, diffDays, mode) {
        const task = ganttTasks[idx];
        const start = new Date(task.start_date);
        const end = new Date(task.end_date);

        if (mode === 'move') {
            start.setDate(start.getDate() + diffDays);
            end.setDate(end.getDate() + diffDays);
        } else if (mode === 'resize-right') {
            end.setDate(end.getDate() + diffDays);
        } else if (mode === 'resize-left') {
            start.setDate(start.getDate() + diffDays);
        }

        if (start > end) {
            alert('終了日を開始日より前にすることはできません');
            window.location.reload();
            return;
        }

        const toYMD = (d) => d.toISOString().slice(0, 10);

        fetch(`/api/tasks/${task.id}/update-dates`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                start_date: toYMD(start),
                end_date: toYMD(end)
            })
        })
        .then(async res => {
            if (res.ok) {
                window.location.reload();
            } else {
                const err = await res.json();
                alert('保存エラー: ' + (err.message || '不明なエラー'));
                window.location.reload();
            }
        })
        .catch(e => {
            console.error(e);
            alert('通信エラーが発生しました');
            window.location.reload();
        });
    }
    </script>
</x-app-layout>