<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_comment_on_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for(Project::factory()->for($user))->create();

        $this->actingAs($user)
            ->post("/tasks/{$task->id}/comments", ['body' => 'Looks good'])
            ->assertRedirect("/projects/{$task->project_id}");

        $this->assertDatabaseHas('comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Looks good',
        ]);
    }

    public function test_comment_body_is_required(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for(Project::factory()->for($user))->create();

        $this->actingAs($user)
            ->post("/tasks/{$task->id}/comments", ['body' => ''])
            ->assertSessionHasErrors('body');
    }

    public function test_other_users_cannot_comment_on_foreign_task(): void
    {
        $task = Task::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post("/tasks/{$task->id}/comments", ['body' => 'Nope'])
            ->assertNotFound();

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_owner_can_delete_comment(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for(Project::factory()->for($user))->create();
        $comment = Comment::factory()->for($task)->for($user)->create();

        $this->actingAs($user)
            ->delete("/tasks/{$task->id}/comments/{$comment->id}")
            ->assertRedirect("/projects/{$task->project_id}");

        $this->assertModelMissing($comment);
    }

    public function test_other_users_cannot_delete_comment(): void
    {
        $task = Task::factory()->create();
        $comment = Comment::factory()->for($task)->create();

        $this->actingAs(User::factory()->create())
            ->delete("/tasks/{$task->id}/comments/{$comment->id}")
            ->assertNotFound();

        $this->assertModelExists($comment);
    }
}
