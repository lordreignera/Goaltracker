<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Role Management</h1>
    </x-slot>

    <style>
        .role-panel {
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

        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 10px;
        }

        .permission-option {
            border: 1px solid #e6e9ef;
            border-radius: 10px;
            background: #fbfcfd;
            padding: 10px 12px;
        }
    </style>

    @php
        $permissionLabel = fn (string $permission) => $permission === 'submit daily reports'
            ? 'Submit Reports'
            : Str::headline($permission);
    @endphp

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the form.</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="role-panel p-3 mb-3">
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-end">
            <div>
                <h2 class="h5 fw-bold mb-1">Roles & Permissions</h2>
                <div class="text-muted small">Create roles and control what each role can access.</div>
            </div>
            @if (auth()->user()->isSuperAdmin())
                <button class="btn btn-maroon" type="button" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                    Add Role
                </button>
            @endif
        </div>
    </div>

    <div class="role-panel table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Permissions</th>
                    <th>Assigned Access</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissionRoles as $role)
                    @php
                        $rolePermissionNames = $role->permissions->pluck('name');
                        $isLockedRole = $role->name === 'Super Admin';
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $role->name }}</strong>
                            <br><small class="text-muted">Guard: {{ $role->guard_name }}</small>
                        </td>
                        <td>
                            <span class="badge text-bg-light border">{{ $rolePermissionNames->count() }} assigned</span>
                        </td>
                        <td>
                            @forelse ($rolePermissionNames->take(4) as $permission)
                                <span class="badge text-bg-light border me-1 mb-1">{{ $permissionLabel($permission) }}</span>
                            @empty
                                <span class="text-muted">No permissions assigned</span>
                            @endforelse
                            @if ($rolePermissionNames->count() > 4)
                                <span class="badge text-bg-light border">+{{ $rolePermissionNames->count() - 4 }} more</span>
                            @endif
                        </td>
                        <td>
                            @if ($isLockedRole)
                                <span class="badge text-bg-light border">Locked</span>
                            @else
                                <span class="badge text-bg-success">Editable</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                Permissions
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (auth()->user()->isSuperAdmin())
        <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form method="post" action="{{ route('roles.management.store') }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="createRoleModalLabel">Add Role</h2>
                            <div class="text-muted small">Create a role and attach permissions immediately.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-semibold">Role Name</label>
                        <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="Program Manager" required>

                        <label class="form-label fw-semibold">Attach Permissions</label>
                        <div class="permission-grid">
                            @foreach ($permissions as $permission)
                                <label class="permission-option form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', []), true))>
                                    <span class="form-check-label fw-semibold">{{ $permissionLabel($permission->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-maroon">Create Role</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @foreach ($permissionRoles as $role)
        @php
            $rolePermissionNames = $role->permissions->pluck('name');
            $isLockedRole = $role->name === 'Super Admin';
        @endphp
        <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-labelledby="editRoleModalLabel{{ $role->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form method="post" action="{{ route('roles.management.permissions.update', $role) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="editRoleModalLabel{{ $role->id }}">{{ $role->name }} Permissions</h2>
                            <div class="text-muted small">{{ $isLockedRole ? 'Super Admin permissions are locked.' : 'Update what this role can access.' }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($isLockedRole)
                            <div class="alert alert-light border small">Super Admin always keeps all permissions and cannot be changed here.</div>
                        @endif

                        <div class="permission-grid">
                            @foreach ($permissions as $permission)
                                <label class="permission-option form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked($rolePermissionNames->contains($permission->name)) @disabled($isLockedRole || ! auth()->user()->isSuperAdmin())>
                                    <span class="form-check-label fw-semibold">{{ $permissionLabel($permission->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-maroon" @disabled($isLockedRole || ! auth()->user()->isSuperAdmin())>Save Permissions</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-app-layout>
