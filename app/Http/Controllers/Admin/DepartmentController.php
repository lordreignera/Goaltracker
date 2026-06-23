<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage departments'), 403);

        return view('departments.index', [
            'nextDepartmentCode' => $this->generateDepartmentCode(),
            'departments' => Department::withCount(['sections', 'users', 'goals'])
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
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage departments'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $data['code'] = $this->generateDepartmentCode();

        Department::create($data);

        return back()->with('status', 'Department created.');
    }

    public function update(Request $request, Department $department)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage departments'), 403);

        $department->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Department updated.');
    }

    public function destroy(Request $request, Department $department)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage departments'), 403);

        if ($department->sections()->exists() || $department->users()->exists() || $department->goals()->exists()) {
            return back()->withErrors(['department' => 'This department has sections, users, or goals and cannot be deleted.']);
        }

        $department->delete();

        return back()->with('status', 'Department deleted.');
    }

    private function generateDepartmentCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Department::where('code', $code)->exists());

        return $code;
    }
}
