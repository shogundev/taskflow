<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_own_projects(): void
    {
        $user = User::factory()->create();
        Project::factory()->for($user)->create(['name' => 'Mine']);
        Project::factory()->create(['name' => 'Someone Elses']);

        $this->actingAs($user)->get('/projects')
            ->assertOk()
            ->assertSee('Mine')
            ->assertDontSee('Someone Elses');
    }

    public function test_user_can_create_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/projects', [
            'name' => 'New project',
            'description' => 'Hello',
        ]);

        $project = Project::firstWhere('name', 'New project');
        $this->assertNotNull($project);
        $this->assertSame($user->id, $project->user_id);
        $response->assertRedirect("/projects/{$project->id}");
    }

    public function test_project_name_is_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/projects', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_owner_can_view_edit_and_update_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        $this->actingAs($user)->get("/projects/{$project->id}")->assertOk()->assertSee($project->name);
        $this->actingAs($user)->get("/projects/{$project->id}/edit")->assertOk();

        $this->actingAs($user)
            ->put("/projects/{$project->id}", ['name' => 'Renamed'])
            ->assertRedirect("/projects/{$project->id}");

        $this->assertSame('Renamed', $project->fresh()->name);
    }

    public function test_owner_can_delete_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        $this->actingAs($user)->delete("/projects/{$project->id}")->assertRedirect('/projects');

        $this->assertModelMissing($project);
    }

    public function test_other_users_cannot_access_project(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $project = Project::factory()->for($owner)->create(['name' => 'Original']);

        $this->actingAs($intruder)->get("/projects/{$project->id}")->assertForbidden();
        $this->actingAs($intruder)->get("/projects/{$project->id}/edit")->assertForbidden();
        $this->actingAs($intruder)->put("/projects/{$project->id}", ['name' => 'Hacked'])->assertForbidden();
        $this->actingAs($intruder)->delete("/projects/{$project->id}")->assertForbidden();

        $this->assertSame('Original', $project->fresh()->name);
    }

    public function test_missing_project_returns_404(): void
    {
        $this->actingAs(User::factory()->create())->get('/projects/9999')->assertNotFound();
    }
}
