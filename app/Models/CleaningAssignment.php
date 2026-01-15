<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CleaningAssignment extends Model
{
    protected $fillable = ['cleaning_task_id', 'user_id', 'assigned_date', 'status'];

    protected $casts = [
        'assigned_date' => 'date',
    ];

    public function cleaningTask()
    {
        return $this->belongsTo(CleaningTask::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
