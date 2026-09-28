<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage positions'), 403);

        return view('positions.index', [
            'departments' => Department::with('sections')->orderBy('name')->get(),
            'sections' => Section::with('department')->orderBy('name')->get(),
            'positions' => Position::with(['department', 'section'])
                ->withCount('users')
                ->when($request->filled('search'), function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        $query->where('title', 'like', '%'.$request->search.'%')
                            ->orWhere('code', 'like', '%'.$request->search.'%')
                            ->orWhere('description', 'like', '%'.$request->search.'%');
                    });
                })
                ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->department_id))
                ->when($request->filled('section_id'), fn ($query) => $query->where('section_id', $request->section_id))
                ->orderBy('title')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage positions'), 403);

        Position::create($this->validatedData($request));

        return back()->with('status', 'Position created.');
    }

    public function update(Request $request, Position $position)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage positions'), 403);

        $position->update($this->validatedData($request, $position));

        return back()->with('status', 'Position updated.');
    }

    public function destroy(Request $request, Position $position)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage positions'), 403);

        if ($position->users()->exists()) {
            return back()->withErrors(['position' => 'This position is assigned to users and cannot be deleted.']);
        }

        $position->delete();

        return back()->with('status', 'Position deleted.');
    }

    private function validatedData(Request $request, ?Position $position = null): array
    {
        return $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'section_id' => [
                'nullable',
                Rule::exists('sections', 'id')
                    ->where(fn ($query) => $query->where('department_id', $request->department_id)),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions', 'title')
                    ->where(fn ($query) => $query
                        ->where('department_id', $request->department_id)
                        ->when(
                            $request->filled('section_id'),
                            fn ($query) => $query->where('section_id', $request->section_id),
                            fn ($query) => $query->whereNull('section_id')
                        ))
                    ->ignore($position?->id),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
