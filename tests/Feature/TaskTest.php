<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->for($this->user)->create();
    }

    public function test_user_can_create_task(): void
    {
        $this->actingAs($this->user)
            ->post("/projects/{$this->project->id}/tasks", [
                'title' => 'Write tests',
                'priority' => 'high',
                'due_date' => '2030-01-01',
            ])
            ->assertRedirect("/projects/{$this->project->id}");

        $this->assertDatabaseHas('tasks', [
            'project_id' => $this->project->id,
            'title' => 'Write tests',
            'priority' => 'high',
            'status' => 'todo',
        ]);
    }

    public function test_task_title_is_required(): void
    {
        $this->actingAs($this->user)
            ->post("/projects/{$this->project->id}/tasks", ['title' => ''])
            ->assertSessionHasErrors('title');
    }

    public function test_due_date_must_be_a_date(): void
    {
        $this->actingAs($this->user)
            ->post("/projects/{$this->project->id}/tasks", ['title' => 'x', 'due_date' => 'not-a-date'])
            ->assertSessionHasErrors('due_date');
    }

    public function test_user_can_update_task(): void
    {
        $task = Task::factory()->for($this->project)->create(['status' => 'todo']);

        $this->actingAs($this->user)
            ->put("/projects/{$this->project->id}/tasks/{$task->id}", [
                'title' => 'Updated',
                'status' => 'in_progress',
            ])
            ->assertRedirect("/projects/{$this->project->id}");

        $task->refresh();
        $this->assertSame('Updated', $task->title);
        $this->assertSame('in_progress', $task->status);
    }

    public function test_toggle_flips_between_todo_and_done(): void
    {
        $task = Task::factory()->for($this->project)->create(['status' => 'todo']);

        $this->actingAs($this->user)->post("/projects/{$this->project->id}/tasks/{$task->id}/toggle");
        $this->assertSame('done', $task->fresh()->status);

        $this->actingAs($this->user)->post("/projects/{$this->project->id}/tasks/{$task->id}/toggle");
        $this->assertSame('todo', $task->fresh()->status);
    }

    public function test_user_can_delete_task(): void
    {
        $task = Task::factory()->for($this->project)->create();

        $this->actingAs($this->user)
            ->delete("/projects/{$this->project->id}/tasks/{$task->id}")
            ->assertRedirect("/projects/{$this->project->id}");

        $this->assertModelMissing($task);
    }

    public function test_other_users_cannot_manage_tasks_in_foreign_project(): void
    {
        $intruder = User::factory()->create();
        $task = Task::factory()->for($this->project)->create(['title' => 'Original']);
        $base = "/projects/{$this->project->id}/tasks";

        $this->actingAs($intruder)->get("{$base}/create")->assertNotFound();
        $this->actingAs($intruder)->post($base, ['title' => 'Injected'])->assertNotFound();
        $this->actingAs($intruder)->put("{$base}/{$task->id}", ['title' => 'Hacked'])->assertNotFound();
        $this->actingAs($intruder)->post("{$base}/{$task->id}/toggle")->assertNotFound();
        $this->actingAs($intruder)->delete("{$base}/{$task->id}")->assertNotFound();

        $this->assertSame('Original', $task->fresh()->title);
        $this->assertDatabaseMissing('tasks', ['title' => 'Injected']);
    }

    public function test_task_cannot_be_accessed_through_a_different_project(): void
    {
        $otherProject = Project::factory()->for($this->user)->create();
        $task = Task::factory()->for($otherProject)->create();

        $this->actingAs($this->user)
            ->get("/projects/{$this->project->id}/tasks/{$task->id}/edit")
            ->assertNotFound();
    }

    public function test_overdue_tasks_are_highlighted_on_project_page(): void
    {
        Task::factory()->for($this->project)->create([
            'title' => 'Late one',
            'status' => 'todo',
            'due_date' => now()->subDays(3),
        ]);

        $this->actingAs($this->user)
            ->get("/projects/{$this->project->id}")
            ->assertOk()
            ->assertSee('list-group-item-danger', false);
    }
}
