<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_relations_are_wired_together(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $task = Task::factory()->for($project)->create();
        $comment = Comment::factory()->for($task)->for($user)->create();

        $this->assertTrue($user->projects->contains($project));
        $this->assertTrue($project->tasks->contains($task));
        $this->assertTrue($task->comments->contains($comment));
        $this->assertTrue($comment->user->is($user));
        $this->assertTrue($task->project->is($project));
    }

    public function test_deleting_a_project_cascades_to_tasks_and_comments(): void
    {
        $task = Task::factory()->create();
        $comment = Comment::factory()->for($task)->create();

        $task->project->delete();

        $this->assertModelMissing($task);
        $this->assertModelMissing($comment);
    }
}
