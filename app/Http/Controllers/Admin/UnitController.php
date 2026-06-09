<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('units.index', [
            'departments' => Department::orderBy('name')->get(),
            'units' => Unit::with('department')
                ->withCount(['users', 'goals'])
                ->when($request->filled('search'), function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('code', 'like', '%'.$request->search.'%')
                            ->orWhere('description', 'like', '%'.$request->search.'%');
                    });
                })
                ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->department_id))
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        Unit::create($request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Unit created.');
    }

    public function update(Request $request, Unit $unit)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')
                    ->where(fn ($query) => $query->where('department_id', $request->department_id))
                    ->ignore($unit->id),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $unit->update($data);

        return back()->with('status', 'Unit updated.');
    }

    public function destroy(Request $request, Unit $unit)
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($unit->users()->exists() || $unit->goals()->exists()) {
            return back()->withErrors(['unit' => 'This unit has users or goals and cannot be deleted.']);
        }

        $unit->delete();

        return back()->with('status', 'Unit deleted.');
    }
}
