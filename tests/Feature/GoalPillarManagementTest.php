<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\GoalPillar;
use App\Models\Quarter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalPillarManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_official_goal_pillars(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('goal_pillars', [
            'name' => 'Operational Excellence',
            'annual_goal' => 'Both teams operate with clear systems, shared accountability, and healthy internal cultures by year-end.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('goal_pillars', [
            'name' => 'Church-First Identity',
            'annual_goal' => "Define, communicate, and operationalize ARM's identity as a church-first organization internally and externally.",
            'sort_order' => 2,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('goal_pillars', [
            'name' => 'Financial Health & Sustainability',
            'annual_goal' => 'Close the program funding gap, diversify income, and build mutual trust through transparent, program-level financial reporting.',
            'sort_order' => 3,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('goal_pillars', [
            'name' => 'US-Uganda Alignment Framework',
            'annual_goal' => 'How the two teams stay connected, accountable, and moving in the same direction.',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        $this->assertSame(4, GoalPillar::count());
    }

    public function test_database_seeder_creates_july_to_june_strategic_quarters(): void
    {
        $this->seed(DatabaseSeeder::class);

        $expectedQuarters = [
            'Q1 2026/2027' => ['2026-07-01', '2026-09-30'],
            'Q2 2026/2027' => ['2026-10-01', '2026-12-31'],
            'Q3 2026/2027' => ['2027-01-01', '2027-03-31'],
            'Q4 2026/2027' => ['2027-04-01', '2027-06-30'],
        ];

        foreach ($expectedQuarters as $name => [$startsAt, $endsAt]) {
            $quarter = Quarter::where('name', $name)->firstOrFail();

            $this->assertSame($startsAt, $quarter->starts_at->toDateString());
            $this->assertSame($endsAt, $quarter->ends_at->toDateString());
        }

        $this->assertSame(4, Quarter::count());
    }

    public function test_admin_can_manage_goal_pillars(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('goal-pillars.index'))
            ->assertOk()
            ->assertSee('Goal Pillars');

        $this->actingAs($admin)->post(route('goal-pillars.store'), [
            'name' => 'Leadership Development',
            'annual_goal' => 'Build healthy leadership capacity across teams.',
            'description' => 'Leadership training and team health.',
            'sort_order' => 4,
            'is_active' => '1',
        ])->assertRedirect();

        $pillar = GoalPillar::where('name', 'Leadership Development')->firstOrFail();

        $this->actingAs($admin)->put(route('goal-pillars.update', $pillar), [
            'name' => 'Leadership Development Updated',
            'annual_goal' => 'Build healthy leadership capacity across all teams.',
            'description' => 'Leadership training and team health.',
            'sort_order' => 5,
        ])->assertRedirect();

        $this->assertDatabaseHas('goal_pillars', [
            'name' => 'Leadership Development Updated',
            'is_active' => false,
            'sort_order' => 5,
        ]);
    }

    public function test_goal_pillar_with_goals_cannot_be_deleted(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $department = Department::create(['name' => 'ICT Department']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $pillar = GoalPillar::create([
            'name' => 'Operational Excellence',
            'annual_goal' => 'Both teams operate with clear systems, shared accountability, and healthy internal cultures by year-end.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'title' => 'Improve ICT Service Delivery',
            'level' => 'department',
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);

        $this->actingAs($admin)->delete(route('goal-pillars.destroy', $pillar))
            ->assertSessionHasErrors('goal_pillar');

        $this->assertDatabaseHas('goal_pillars', ['id' => $pillar->id]);
    }
}
