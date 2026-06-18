<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage sections'), 403);

        return view('sections.index', [
            'departments' => Department::orderBy('name')->get(),
            'sections' => Section::with('department')
                ->withCount(['units', 'users', 'goals'])
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
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage sections'), 403);

        Section::create($request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Section created.');
    }

    public function update(Request $request, Section $section)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage sections'), 403);

        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections', 'name')
                    ->where(fn ($query) => $query->where('department_id', $request->department_id))
                    ->ignore($section->id),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $section->update($data);

        return back()->with('status', 'Section updated.');
    }

    public function destroy(Request $request, Section $section)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage sections'), 403);

        if ($section->units()->exists() || $section->users()->exists() || $section->goals()->exists()) {
            return back()->withErrors(['section' => 'This section has units, users, or goals and cannot be deleted.']);
        }

        $section->delete();

        return back()->with('status', 'Section deleted.');
    }
}
