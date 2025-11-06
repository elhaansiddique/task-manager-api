<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('role', 'manager')->first();
        $employee1 = User::where('email', 'employee@example.com')->first();
        $employee2 = User::where('email', 'employee2@example.com')->first();

        Task::create([
            'title' => 'Task 1',
            'description' => 'Description for task 1',
            'assigned_to' => $employee1->id,
            'created_by' => $manager->id,
        ]);

        Task::create([
            'title' => 'Task 2',
            'description' => 'Description for task 2',
            'assigned_to' => $employee2->id,
            'created_by' => $manager->id,
        ]);

        Task::create([
            'title' => 'Task 3',
            'description' => 'Description for task 3',
            'assigned_to' => $employee1->id,
            'created_by' => $manager->id,
            'status' => 'in_progress',
        ]);
    }
}
