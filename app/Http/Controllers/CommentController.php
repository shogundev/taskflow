<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, $taskId)
    {
        $task = Task::find($taskId);

        if (! $task || $task->project->user_id !== auth()->id()) {
            abort(404);
        }

        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        Comment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        return redirect("/projects/{$task->project_id}");
    }

    public function destroy($taskId, $commentId)
    {
        $task = Task::find($taskId);

        if (! $task || $task->project->user_id !== auth()->id()) {
            abort(404);
        }

        $comment = Comment::find($commentId);

        if (! $comment || $comment->task_id !== $task->id) {
            abort(404);
        }

        // Only the project owner can delete, matching the checks above -
        // duplicated instead of centralized in a Policy.
        $comment->delete();

        return redirect("/projects/{$task->project_id}");
    }
}
