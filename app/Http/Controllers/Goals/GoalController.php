<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\Unit;
use App\Services\GoalAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $goals = Goal::visibleTo($request->user())
            ->with(['quarter', 'department', 'unit', 'objectives'])
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

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->isSupervisor(), 403);

        $data = $request->validate([
            'quarter_id' => ['required', 'exists:quarters,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'level' => ['required', 'in:department,unit,individual'],
            'objectives' => ['required', 'array', 'min:1'],
            'objectives.*.title' => ['required', 'string', 'max:255'],
            'objectives.*.description' => ['nullable', 'string'],
            'objectives.*.weight' => ['required', 'integer', 'min:1', 'max:100'],
            'objectives.*.due_at' => ['nullable', 'date'],
        ]);

        $objectiveTotal = collect($data['objectives'])->sum(fn ($objective) => (int) $objective['weight']);
        if ($objectiveTotal !== 100) {
            return back()
                ->withErrors(['objectives' => "Objective weights must equal 100%. Current total is {$objectiveTotal}%."])
                ->withInput();
        }

        $goalData = collect($data)->except('objectives')->all();
        $goal = new Goal($goalData + ['owner_id' => $request->user()->id, 'status' => 'draft']);
        abort_unless(app(GoalAccessService::class)->canUpdateGoal($request->user(), $goal), 403);

        DB::transaction(function () use ($goal, $data) {
            $goal->save();

            foreach ($data['objectives'] as $objective) {
                $goal->objectives()->create($objective + ['status' => 'pending']);
            }
        });

        return redirect()->route('goals.show', $goal)->with('status', 'Goal and objectives created.');
    }

    public function show(Request $request, Goal $goal)
    {
        abort_unless(app(GoalAccessService::class)->canViewGoal($request->user(), $goal), 403);

        return view('goals.show', [
            'goal' => $goal->load(['quarter', 'department', 'unit', 'objectives.weeklyUpdates.reviews']),
            'canUpdateGoal' => app(GoalAccessService::class)->canUpdateGoal($request->user(), $goal),
            'canReviewGoal' => app(GoalAccessService::class)->canReviewGoal($request->user(), $goal),
        ]);
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
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($user->unit_id)->orderBy('name')->get();

        return [$departments, $units];
    }
}
