@extends('layouts.app')

@section('content')
<h1 class="mb-4">New task in {{ $project->name }}</h1>
<form method="POST" action="/projects/{{ $project->id }}/tasks">
    @csrf
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select">
            <option value="low">Low</option>
            <option value="medium" selected>Medium</option>
            <option value="high">High</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Due date</label>
        <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
</form>
@endsection
