@extends('layouts.app')

@section('content')
<h1 class="mb-4">Edit project</h1>
<form method="POST" action="/projects/{{ $project->id }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" value="{{ old('name', $project->name) }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $project->description) }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
@endsection
