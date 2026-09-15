@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1>{{ $project->name }}</h1>
        <p class="text-muted">{{ $project->description }}</p>
    </div>
    <a href="/projects/{{ $project->id }}/tasks/create" class="btn btn-primary">New task</a>
</div>

<div class="list-group mb-4">
    @forelse ($tasks as $task)
        {{-- overdue check duplicated here, in DashboardController, and in TaskController --}}
        @php
            $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'done';
        @endphp
        <div class="list-group-item {{ $isOverdue ? 'list-group-item-danger' : '' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong class="{{ $task->status === 'done' ? 'text-decoration-line-through text-muted' : '' }}">
                        {{ $task->title }}
                    </strong>
                    <span class="badge bg-secondary">{{ $task->status }}</span>
                    <span class="badge bg-info text-dark">{{ $task->priority }}</span>
                    @if ($task->due_date)
                        <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-light text-dark' }}">
                            due {{ $task->due_date->format('Y-m-d') }}
                        </span>
                    @endif
                    <p class="mb-1">{{ $task->description }}</p>
                </div>
                <div class="text-nowrap">
                    <form action="/projects/{{ $project->id }}/tasks/{{ $task->id }}/toggle" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success">
                            {{ $task->status === 'done' ? 'Reopen' : 'Complete' }}
                        </button>
                    </form>
                    <a href="/projects/{{ $project->id }}/tasks/{{ $task->id }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="/projects/{{ $project->id }}/tasks/{{ $task->id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this task?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>

            <div class="mt-3 ms-2">
                @foreach ($task->comments as $comment)
                    <div class="small mb-1">
                        <strong>{{ $comment->user->name }}:</strong> {{ $comment->body }}
                        @if ($comment->user_id === auth()->id() || $project->user_id === auth()->id())
                            <form action="/tasks/{{ $task->id }}/comments/{{ $comment->id }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm text-danger p-0">remove</button>
                            </form>
                        @endif
                    </div>
                @endforeach

                <form action="/tasks/{{ $task->id }}/comments" method="POST" class="d-flex gap-2 mt-2">
                    @csrf
                    <input type="text" name="body" class="form-control form-control-sm" placeholder="Add a comment" required>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Post</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">No tasks yet.</p>
    @endforelse
</div>
@endsection
