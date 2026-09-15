@extends('layouts.app')

@section('content')
<h1 class="mb-4">Edit task</h1>
<form method="POST" action="/projects/{{ $project->id }}/tasks/{{ $task->id }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" value="{{ old('title', $task->title) }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $task->description) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (['todo', 'in_progress', 'done'] as $status)
                <option value="{{ $status }}" @selected(old('status', $task->status) === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select">
            @foreach (['low', 'medium', 'high'] as $priority)
                <option value="{{ $priority }}" @selected(old('priority', $task->priority) === $priority)>{{ $priority }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Due date</label>
        <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
@endsection
