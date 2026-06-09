<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('departments.index', [
            'departments' => Department::withCount(['units', 'users', 'goals'])
                ->when($request->filled('search'), function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('code', 'like', '%'.$request->search.'%')
                            ->orWhere('description', 'like', '%'.$request->search.'%');
                    });
                })
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        Department::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Department created.');
    }

    public function update(Request $request, Department $department)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $department->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('departments', 'code')->ignore($department->id)],
            'description' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Department updated.');
    }

    public function destroy(Request $request, Department $department)
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($department->units()->exists() || $department->users()->exists() || $department->goals()->exists()) {
            return back()->withErrors(['department' => 'This department has units, users, or goals and cannot be deleted.']);
        }

        $department->delete();

        return back()->with('status', 'Department deleted.');
    }
}
