<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_total_done_and_overdue_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        Task::factory()->for($project)->create(['status' => 'done', 'due_date' => now()->subDay()]);
        Task::factory()->for($project)->create(['status' => 'todo', 'due_date' => now()->subDay()]);
        Task::factory()->for($project)->create(['status' => 'in_progress', 'due_date' => now()->addWeek()]);
        Task::factory()->for($project)->create(['status' => 'todo', 'due_date' => null]);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('totalTasks', 4)
            ->assertViewHas('doneTasks', 1)
            ->assertViewHas('overdueTasks', 1);
    }

    public function test_dashboard_ignores_other_users_data(): void
    {
        Task::factory()->count(3)->create();

        $this->actingAs(User::factory()->create())->get('/dashboard')
            ->assertOk()
            ->assertViewHas('totalTasks', 0);
    }
}
