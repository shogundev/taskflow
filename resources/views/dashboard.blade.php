@extends('layouts.app')

@section('content')
<h1 class="mb-4">Dashboard</h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Total tasks</h5>
                <p class="display-6">{{ $totalTasks }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Done</h5>
                <p class="display-6">{{ $doneTasks }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-danger">
            <div class="card-body">
                <h5 class="card-title text-danger">Overdue</h5>
                <p class="display-6 text-danger">{{ $overdueTasks }}</p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Your projects</h2>
    <a href="/projects/create" class="btn btn-primary">New project</a>
</div>

<div class="list-group">
    @forelse ($projects as $project)
        <a href="/projects/{{ $project->id }}" class="list-group-item list-group-item-action">
            {{ $project->name }}
            <span class="text-muted">({{ $project->tasks->count() }} tasks)</span>
        </a>
    @empty
        <p class="text-muted">No projects yet.</p>
    @endforelse
</div>
@endsection
