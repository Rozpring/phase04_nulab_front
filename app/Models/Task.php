<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // 変更しても良い項目をリストアップ
    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'status',
    ];
}