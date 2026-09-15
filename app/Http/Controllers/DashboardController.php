<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = auth()->user()->projects()->get();

        // N+1 by design: tasks are lazy-loaded per project instead of eager-loaded.
        // Overdue/done math is duplicated here and in TaskController/blade views
        // instead of living in one place (model scope, accessor, or service).
        $totalTasks = 0;
        $doneTasks = 0;
        $overdueTasks = 0;

        foreach ($projects as $project) {
            foreach ($project->tasks as $task) {
                $totalTasks++;

                if ($task->status === 'done') {
                    $doneTasks++;
                }

                if ($task->due_date && $task->due_date->isPast() && $task->status !== 'done') {
                    $overdueTasks++;
                }
            }
        }

        return view('dashboard', [
            'projects' => $projects,
            'totalTasks' => $totalTasks,
            'doneTasks' => $doneTasks,
            'overdueTasks' => $overdueTasks,
        ]);
    }
}
