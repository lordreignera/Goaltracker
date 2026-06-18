<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">User Management</h1>
    </x-slot>

    <style>
        .user-panel {
            border: 1px solid #e6e9ef;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(20, 24, 31, .04);
        }

        .btn-maroon {
            background: var(--arm-maroon);
            border-color: var(--arm-maroon);
            color: #fff;
            font-weight: 800;
        }

        .btn-maroon:hover {
            background: var(--arm-maroon-dark);
            border-color: var(--arm-maroon-dark);
            color: #fff;
        }

    </style>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @php
        $listQuery = request()->except('edit_user');
        $allUsersQuery = array_merge($listQuery, ['status' => 'all', 'page' => null]);
    @endphp

    <form method="get" action="{{ route('users.management.index') }}" class="user-panel p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4">
                <label class="form-label small fw-semibold">Search</label>
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Name, email, or phone">
            </div>
            <div class="col-lg-3">
                <label class="form-label small fw-semibold">Status</label>
                <select class="form-select" name="status">
                    @foreach (['pending' => 'Waiting Approval', 'approved' => 'Approved Users', 'rejected' => 'Rejected', 'all' => 'All Users'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label small fw-semibold">Department</label>
                <select class="form-select" name="department_id">
                    <option value="">All departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-grid">
                <button class="btn btn-maroon">Filter</button>
            </div>
        </div>
    </form>

    @if ($editUser)
        <div class="user-panel p-4 mb-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Edit User</h2>
                    <div class="text-muted small">Editing {{ $editUser->name }}. Passwords are not changed from this panel.</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.management.index', $listQuery) }}">Back to current list</a>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.management.index', array_filter($allUsersQuery, function ($value) { return filled($value); })) }}">All users</a>
                </div>
            </div>

            <form method="post" action="{{ route('users.management.update', $editUser) }}">
                @csrf
                @method('PUT')
                @php
                    $selectedDepartmentIds = collect(old('department_ids', $editUser->accessibleDepartments->pluck('id')->all()))
                        ->push($editUser->department_id)
                        ->filter()
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->all();
                @endphp
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">First Name</label>
                        <input class="form-control" name="first_name" value="{{ old('first_name', $editUser->first_name ?: Str::before($editUser->name, ' ')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Second Name</label>
                        <input class="form-control" name="second_name" value="{{ old('second_name', $editUser->second_name ?: Str::after($editUser->name, ' ')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email', $editUser->email) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input class="form-control" name="phone_number" value="{{ old('phone_number', $editUser->phone_number) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Primary Department</label>
                        <select class="form-select" name="department_id" required>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected($editUser->department_id === $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Section</label>
                        <select class="form-select" name="section_id">
                            <option value="">No section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" @selected($editUser->section_id === $section->id)>{{ $section->department->name ?? 'Department' }} - {{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Unit</label>
                        <select class="form-select" name="unit_id">
                            <option value="">No unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected($editUser->unit_id === $unit->id)>{{ $unit->department->name ?? 'Department' }} - {{ $unit->section->name ?? 'Section' }} - {{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Role</label>
                        <select class="form-select" name="requested_role" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected($editUser->requested_role === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <label class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($editUser->is_active)>
                            <span class="form-check-label">Active user</span>
                        </label>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Departments This User Can Access</label>
                        <select class="form-select" name="department_ids[]" multiple size="6">
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(in_array($department->id, $selectedDepartmentIds, true))>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">The primary department is always included. Hold Ctrl to grant more departments.</small>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-maroon">Save User Changes</button>
                        <a class="btn btn-outline-secondary ms-sm-2 mt-2 mt-sm-0" href="{{ route('users.management.index', $listQuery) }}">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <div class="user-panel overflow-hidden">
        <div class="p-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Users</h2>
                <div class="text-muted small">Edit department, unit, role, and active state before or after approval. Passwords are not editable here.</div>
            </div>
            @if (request('status') !== 'all')
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.management.index', array_filter($allUsersQuery, function ($value) { return filled($value); })) }}">View all users</a>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Org Placement</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th style="width: 310px"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $user->name }}</div>
                            <div class="text-muted small">{{ $user->email }}</div>
                            <div class="text-muted small">{{ $user->phone_number }}</div>
                        </td>
                        <td>
                            <div>{{ $user->department?->name ?? 'Not selected' }}</div>
                            <small class="text-muted d-block">{{ $user->section?->name ?? 'No section' }} / {{ $user->unit?->name ?? 'No unit' }}</small>
                            @if ($user->accessibleDepartments->isNotEmpty())
                                <small class="text-muted d-block">Access: {{ $user->accessibleDepartments->pluck('name')->join(', ') }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge text-bg-light border">{{ $user->requested_role }}</span>
                            @if ($user->roles->isNotEmpty())
                                <div class="small text-muted mt-1">Assigned: {{ $user->roles->pluck('name')->join(', ') }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $user->approval_status === 'approved' ? 'success' : ($user->approval_status === 'pending' ? 'warning' : 'secondary') }}">
                                {{ str_replace('_', ' ', ucfirst($user->approval_status)) }}
                            </span>
                            <div class="small {{ $user->is_active ? 'text-success' : 'text-danger' }} mt-1">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </div>
                        </td>
                        <td>{{ $user->last_login_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.management.index', array_merge($listQuery, ['edit_user' => $user->id])) }}">
                                Edit
                            </a>
                            @if ($user->approval_status !== 'approved')
                                <form method="post" action="{{ route('users.management.approve', $user) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Approve</button>
                                </form>
                            @endif
                            @if ($user->approval_status === 'pending')
                                <form method="post" action="{{ route('users.management.reject', $user) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            @endif
                            @unless (auth()->id() === $user->id)
                                <form method="post" action="{{ route('users.management.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user request/account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-dark">Delete</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-muted">No users match your filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>

</x-app-layout>
