<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoalPillar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GoalPillarController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePillarManagement($request);

        return view('goal-pillars.index', [
            'goalPillars' => GoalPillar::withCount('goals')
                ->when($request->filled('search'), function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('annual_goal', 'like', '%'.$request->search.'%')
                            ->orWhere('description', 'like', '%'.$request->search.'%');
                    });
                })
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePillarManagement($request);

        GoalPillar::create($this->validatedData($request));

        return back()->with('status', 'Goal pillar created.');
    }

    public function update(Request $request, GoalPillar $goalPillar)
    {
        $this->authorizePillarManagement($request);

        $goalPillar->update($this->validatedData($request, $goalPillar));

        return back()->with('status', 'Goal pillar updated.');
    }

    public function destroy(Request $request, GoalPillar $goalPillar)
    {
        $this->authorizePillarManagement($request);

        if ($goalPillar->goals()->exists()) {
            return back()->withErrors(['goal_pillar' => 'This goal pillar has goals and cannot be deleted.']);
        }

        $goalPillar->delete();

        return back()->with('status', 'Goal pillar deleted.');
    }

    private function authorizePillarManagement(Request $request): void
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage goal pillars'), 403);
    }

    private function validatedData(Request $request, ?GoalPillar $goalPillar = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('goal_pillars', 'name')->ignore($goalPillar),
            ],
            'annual_goal' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
