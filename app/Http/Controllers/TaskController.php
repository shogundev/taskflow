<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create($projectId)
    {
        $project = Project::find($projectId);

        if (! $project || $project->user_id !== auth()->id()) {
            abort(404);
        }

        return view('tasks.create', ['project' => $project]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::find($projectId);

        if (! $project || $project->user_id !== auth()->id()) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $data = $request->all();
        $data['project_id'] = $project->id;

        Task::create($data);

        return redirect("/projects/{$project->id}");
    }

    public function edit($projectId, $taskId)
    {
        $project = Project::find($projectId);

        if (! $project || $project->user_id !== auth()->id()) {
            abort(404);
        }

        $task = Task::find($taskId);

        if (! $task || $task->project_id !== $project->id) {
            abort(404);
        }

        return view('tasks.edit', ['project' => $project, 'task' => $task]);
    }

    public function update(Request $request, $projectId, $taskId)
    {
        $project = Project::find($projectId);

        if (! $project || $project->user_id !== auth()->id()) {
            abort(404);
        }

        $task = Task::find($taskId);

        if (! $task || $task->project_id !== $project->id) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $task->update($request->all());

        return redirect("/projects/{$project->id}");
    }

    public function destroy($projectId, $taskId)
    {
        $project = Project::find($projectId);

        if (! $project || $project->user_id !== auth()->id()) {
            abort(404);
        }

        $task = Task::find($taskId);

        if (! $task || $task->project_id !== $project->id) {
            abort(404);
        }

        $task->delete();

        return redirect("/projects/{$project->id}");
    }

    public function toggle($projectId, $taskId)
    {
        $project = Project::find($projectId);

        if (! $project || $project->user_id !== auth()->id()) {
            abort(404);
        }

        $task = Task::find($taskId);

        if (! $task || $task->project_id !== $project->id) {
            abort(404);
        }

        $task->status = $task->status === 'done' ? 'todo' : 'done';
        $task->save();

        return redirect("/projects/{$project->id}");
    }
}
