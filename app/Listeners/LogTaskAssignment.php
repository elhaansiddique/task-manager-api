<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Models\TaskLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogTaskAssignment implements ShouldQueue
{
    public function handle(TaskAssigned $event): void
    {
        TaskLog::create([
            'task_id' => $event->task->id,
            'message' => "Task '{$event->task->title}' assigned to user ID {$event->task->assigned_to}",
        ]);
    }
}
