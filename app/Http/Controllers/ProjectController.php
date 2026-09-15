<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('user_id', auth()->id())->latest()->get();

        return view('projects.index', ['projects' => $projects]);
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Mass-assigns the whole request instead of a validated DTO/Form Request.
        $data = $request->all();
        $data['user_id'] = auth()->id();

        $project = Project::create($data);

        return redirect("/projects/{$project->id}");
    }

    public function show($id)
    {
        $project = Project::find($id);

        if (! $project) {
            abort(404);
        }

        // Ownership check copy-pasted in every method below instead of a Policy.
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $tasks = $project->tasks;

        return view('projects.show', ['project' => $project, 'tasks' => $tasks]);
    }

    public function edit($id)
    {
        $project = Project::find($id);

        if (! $project) {
            abort(404);
        }

        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        return view('projects.edit', ['project' => $project]);
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (! $project) {
            abort(404);
        }

        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($request->all());

        return redirect("/projects/{$project->id}");
    }

    public function destroy($id)
    {
        $project = Project::find($id);

        if (! $project) {
            abort(404);
        }

        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $project->delete();

        return redirect('/projects');
    }
}
