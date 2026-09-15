@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Projects</h1>
    <a href="/projects/create" class="btn btn-primary">New project</a>
</div>

<div class="list-group">
    @forelse ($projects as $project)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <a href="/projects/{{ $project->id }}">{{ $project->name }}</a>
            <div>
                <a href="/projects/{{ $project->id }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form action="/projects/{{ $project->id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this project?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">No projects yet.</p>
    @endforelse
</div>
@endsection
