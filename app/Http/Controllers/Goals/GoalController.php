<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goals\StoreGoalRequest;
use App\Http\Requests\Goals\UpdateGoalRequest;
use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\Unit;
use App\Services\GoalAccessService;
use App\Services\GoalManagementService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $goals = Goal::visibleTo($request->user())
            ->with(['quarter', 'department', 'unit', 'assignedDepartments', 'assignedUnits', 'objectives'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('title', 'like', '%'.$request->search.'%')
                        ->orWhere('description', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->filled('quarter_id'), fn ($query) => $query->where('quarter_id', $request->quarter_id))
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->department_id))
            ->when($request->filled('unit_id'), fn ($query) => $query->where('unit_id', $request->unit_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        [$departments, $units] = $this->organizationOptions($user);

        return view('goals.index', [
            'goals' => $goals,
            'quarters' => Quarter::orderByDesc('starts_at')->get(),
            'departments' => $departments,
            'units' => $units,
            'canCreateGoals' => $user->isAdmin() || $user->isSupervisor(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->isSupervisor(), 403);

        [$departments, $units] = $this->organizationOptions($request->user());

        return view('goals.create', [
            'quarters' => Quarter::orderByDesc('starts_at')->get(),
            'departments' => $departments,
            'units' => $units,
        ]);
    }

    public function store(StoreGoalRequest $request, GoalManagementService $goals)
    {
        $goal = $goals->createGoal($request->user(), $request->validated());

        return redirect()->route('goals.show', $goal)->with('status', 'Goal and objectives created.');
    }

    public function show(Request $request, Goal $goal)
    {
        abort_unless(app(GoalAccessService::class)->canViewGoal($request->user(), $goal), 403);

        return view('goals.show', [
            'goal' => $goal->load(['quarter', 'department', 'unit', 'assignedDepartments', 'assignedUnits', 'objectives.weeklyUpdates.reviews']),
            'canUpdateGoal' => app(GoalAccessService::class)->canUpdateGoal($request->user(), $goal),
            'canReviewGoal' => app(GoalAccessService::class)->canReviewGoal($request->user(), $goal),
        ]);
    }

    public function edit(Request $request, Goal $goal)
    {
        abort_unless(app(GoalAccessService::class)->canUpdateGoal($request->user(), $goal), 403);

        [$departments, $units] = $this->organizationOptions($request->user());

        return view('goals.edit', [
            'goal' => $goal->load(['quarter', 'assignedDepartments', 'assignedUnits', 'objectives']),
            'quarters' => Quarter::orderByDesc('starts_at')->get(),
            'departments' => $departments,
            'units' => $units,
        ]);
    }

    public function update(UpdateGoalRequest $request, Goal $goal, GoalManagementService $goals)
    {
        $goals->updateGoal($goal, $request->validated());

        return redirect()->route('goals.show', $goal)->with('status', 'Goal and objectives updated.');
    }

    public function submit(Request $request, Goal $goal)
    {
        abort_unless(app(GoalAccessService::class)->canUpdateGoal($request->user(), $goal), 403);

        $weightTotal = $goal->objectives()->sum('weight');
        if ($weightTotal !== 100) {
            return back()->withErrors(['objectives' => "Objective weights must equal 100%. Current total is {$weightTotal}%."]);
        }

        $goal->update(['status' => 'submitted']);

        return back()->with('status', 'Goal submitted for review.');
    }

    private function organizationOptions($user): array
    {
        $departments = $user->isAdmin() || $user->isSupervisor()
            ? Department::orderBy('name')->get()
            : Department::whereKey($user->department_id)->orderBy('name')->get();

        $units = $user->isAdmin() || $user->isSupervisor()
            ? Unit::with('department')->orderBy('name')->get()
            : Unit::with('department')->whereKey($user->unit_id)->orderBy('name')->get();

        return [$departments, $units];
    }
}
