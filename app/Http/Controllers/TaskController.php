<?php
//変更（岡部条）
namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * ガントチャートからのドラッグ＆ドロップ/リサイズによる日付更新処理
     */
    public function updateDates(Request $request, Task $task)
    {
        // 1. バリデーション（入力チェック）
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        // 2. データの更新
        // $taskはルートモデルバインディングにより自動的に取得
        $task->update([
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
        ]);

        // 3. 成功レスポンスを返す
        return response()->json([
            'success' => true,
            'message' => 'スケジュールを更新しました',
            'task' => $task
        ]);
    }
}