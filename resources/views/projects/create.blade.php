@extends('layouts.app')

@section('content')
<h1 class="mb-4">New project</h1>
<form method="POST" action="/projects">
    @csrf
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
</form>
@endsection
