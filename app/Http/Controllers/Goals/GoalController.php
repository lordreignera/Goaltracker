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

        $goals = Goal::visibleTo($user)
            ->with(['quarter', 'assignedDepartments', 'assignedUnits', 'objectives'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('specific', 'like', '%' . $search . '%')
                        ->orWhere('measurable', 'like', '%' . $search . '%')
                        ->orWhere('primary_metric', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('quarter_id'), fn ($query) => $query->where('quarter_id', $request->quarter_id))
            ->when($request->filled('department_id'), fn ($query) => $query->whereHas('assignedDepartments', fn ($query) => $query->whereKey($request->department_id)))
            ->when($request->filled('unit_id'), fn ($query) => $query->whereHas('assignedUnits', fn ($query) => $query->whereKey($request->unit_id)))
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
            'canCreateGoals' => $user->canManageGoals(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->canManageGoals(), 403);

        [$departments, $units] = $this->organizationOptions($request->user());

        return view('goals.create', [
            'quarters' => Quarter::orderByDesc('starts_at')->get(),
            'departments' => $departments,
            'units' => $units,
        ]);
    }

    public function store(StoreGoalRequest $request, GoalManagementService $goals)
    {
        $data = $this->prepareGoalData($request->validated());

        $goal = $goals->createGoal($request->user(), $data);

        return redirect()
            ->route('goals.show', $goal)
            ->with('status', 'Goal and objectives created.');
    }

    public function show(Request $request, Goal $goal)
    {
        $access = app(GoalAccessService::class);

        abort_unless($access->canViewGoal($request->user(), $goal), 403);

        return view('goals.show', [
            'goal' => $goal->load([
                'quarter',
                'assignedDepartments',
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
        $data = $this->prepareGoalData($request->validated());

        $goals->updateGoal($goal, $data);

        return redirect()
            ->route('goals.show', $goal)
            ->with('status', 'Goal and objectives updated.');
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

    private function prepareGoalData(array $data): array
    {
        $raw = $data['key_action_steps'] ?? [];

        if (is_string($raw)) {
            $parts = preg_split('/\r\n|\r|\n|,/', $raw);
            $raw = $parts ?: [];
        }

        if (! is_array($raw)) {
            $raw = [];
        }

        $data['key_action_steps'] = array_values(array_filter(array_map(function ($step) {
            return trim((string) $step);
        }, $raw), function ($step) {
            return $step !== '';
        }));

        return $data;
    }

    private function organizationOptions($user): array
    {
        if ($user->isAdmin()) {
            return [
                Department::orderBy('name')->get(),
                Unit::with('department')->orderBy('name')->get(),
            ];
        }

        $departments = Department::whereKey($user->department_id)
            ->orderBy('name')
            ->get();

        $units = Unit::with('department')
            ->where('department_id', $user->department_id)
            ->when($user->unit_id, fn ($query) => $query->whereKey($user->unit_id))
            ->orderBy('name')
            ->get();

        return [$departments, $units];
    }
}