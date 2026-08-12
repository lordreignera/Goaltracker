<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goals\StoreGoalRequest;
use App\Http\Requests\Goals\UpdateGoalRequest;
use App\Models\Department;
use App\Models\Goal;
use App\Models\GoalPillar;
use App\Models\Quarter;
use App\Models\Section;
use App\Models\Unit;
use App\Services\GoalAccessService;
use App\Services\GoalManagementService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $goals = Goal::visibleTo($user)
            ->with(['pillar', 'quarter', 'assignedDepartments', 'assignedSections', 'assignedUnits', 'objectives'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('objectives', function ($query) use ($search) {
                            $query->where('title', 'like', '%' . $search . '%')
                                ->orWhere('key_activities', 'like', '%' . $search . '%')
                                ->orWhere('specific_output', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($request->filled('quarter_id'), fn ($query) => $query->where('quarter_id', $request->quarter_id))
            ->when($request->filled('goal_pillar_id'), fn ($query) => $query->where('goal_pillar_id', $request->goal_pillar_id))
            ->when($request->filled('department_id'), fn ($query) => $query->whereHas('assignedDepartments', fn ($query) => $query->whereKey($request->department_id)))
            ->when($request->filled('section_id'), fn ($query) => $query->whereHas('assignedSections', fn ($query) => $query->whereKey($request->section_id)))
            ->when($request->filled('unit_id'), fn ($query) => $query->whereHas('assignedUnits', fn ($query) => $query->whereKey($request->unit_id)))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        [$departments, $sections, $units] = $this->organizationOptions($user);

        return view('goals.index', [
            'goals' => $goals,
            'quarters' => Quarter::orderByDesc('starts_at')->get(),
            'goalPillars' => GoalPillar::orderBy('sort_order')->orderBy('name')->get(),
            'departments' => $departments,
            'sections' => $sections,
            'units' => $units,
            'canCreateGoals' => $user->canManageGoals(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->canManageGoals(), 403);

        [$departments, $sections, $units] = $this->organizationOptions($request->user());
        $goalPillars = GoalPillar::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $goalsByPillar = Goal::visibleTo($request->user())
            ->with(['quarter', 'assignedDepartments', 'assignedSections', 'assignedUnits', 'objectives'])
            ->whereNotNull('goal_pillar_id')
            ->latest()
            ->get()
            ->groupBy('goal_pillar_id');

        return view('goals.create', [
            'quarters' => Quarter::orderByDesc('starts_at')->get(),
            'goalPillars' => $goalPillars,
            'goalsByPillar' => $goalsByPillar,
            'departments' => $departments,
            'sections' => $sections,
            'units' => $units,
        ]);
    }

    public function store(StoreGoalRequest $request, GoalManagementService $goals)
    {
        $goal = $goals->createGoal($request->user(), $request->validated());

        return redirect()
            ->route('goals.show', $goal)
            ->with('status', 'Goal set and strategic goals/objectives created.');
    }

    public function show(Request $request, Goal $goal)
    {
        $access = app(GoalAccessService::class);

        abort_unless($access->canViewGoal($request->user(), $goal), 403);

        return view('goals.show', [
            'goal' => $goal->load([
                'quarter',
                'pillar',
                'assignedDepartments',
                'assignedSections',
                'assignedUnits',
                'objectives.weeklyUpdates.reviews',
            ]),
            'canUpdateGoal' => $access->canUpdateGoal($request->user(), $goal),
            'canReviewGoal' => $access->canReviewGoal($request->user(), $goal),
        ]);
    }

    public function edit(Request $request, Goal $goal)
    {
        abort_unless(app(GoalAccessService::class)->canUpdateGoal($request->user(), $goal), 403);

        [$departments, $sections, $units] = $this->organizationOptions($request->user());

        return view('goals.edit', [
            'goal' => $goal->load(['quarter', 'assignedDepartments', 'assignedSections', 'assignedUnits', 'objectives']),
            'quarters' => Quarter::orderByDesc('starts_at')->get(),
            'goalPillars' => GoalPillar::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'departments' => $departments,
            'sections' => $sections,
            'units' => $units,
        ]);
    }

    public function update(UpdateGoalRequest $request, Goal $goal, GoalManagementService $goals)
    {
        $goals->updateGoal($goal, $request->validated());

        return redirect()
            ->route('goals.show', $goal)
            ->with('status', 'Goal set and strategic goals/objectives updated.');
    }

    public function submit(Request $request, Goal $goal)
    {
        abort_unless(app(GoalAccessService::class)->canUpdateGoal($request->user(), $goal), 403);

        $weightTotal = $goal->objectives()->sum('weight');

        if ($weightTotal !== 100) {
            return back()->withErrors([
                'objectives' => "Objective weights must equal 100%. Current total is {$weightTotal}%.",
            ]);
        }

        $goal->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return back()->with('status', 'Goal submitted for review.');
    }

    private function organizationOptions($user): array
    {
        if ($user->isAdmin()) {
            return [
                Department::orderBy('name')->get(),
                Section::with('department')->orderBy('name')->get(),
                Unit::with(['department', 'section'])->orderBy('name')->get(),
            ];
        }

        $departments = Department::whereKey($user->department_id)
            ->orderBy('name')
            ->get();

        $sections = Section::with('department')
            ->where('department_id', $user->department_id)
            ->when($user->section_id, fn ($query) => $query->whereKey($user->section_id))
            ->orderBy('name')
            ->get();

        $units = Unit::with(['department', 'section'])
            ->where('department_id', $user->department_id)
            ->when($user->section_id, fn ($query) => $query->where('section_id', $user->section_id))
            ->when($user->unit_id, fn ($query) => $query->whereKey($user->unit_id))
            ->orderBy('name')
            ->get();

        return [$departments, $sections, $units];
    }
}
