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
            background: #8f171b;
            border-color: #8f171b;
            color: #fff;
            font-weight: 800;
        }

        .btn-maroon:hover {
            background: #721216;
            border-color: #721216;
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

        .role-create-panel {
            border: 1px dashed #d8dde5;
            border-radius: 12px;
            background: #fbfcfd;
        }
    </style>

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

    <div class="role-panel p-4 mb-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Add New Role</h2>
                <div class="text-muted small">Create a role and attach permissions immediately.</div>
            </div>
            <span class="badge text-bg-light border">Spatie permissions</span>
        </div>

        <form method="post" action="{{ route('roles.management.store') }}" class="role-create-panel p-3">
            @csrf
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold">Role Name</label>
                    <input class="form-control" name="name" value="{{ old('name') }}" placeholder="Program Manager" required>
                </div>
                <div class="col-lg-8">
                    <label class="form-label small fw-semibold">Attach Permissions</label>
                    <div class="permission-grid">
                        @foreach ($permissions as $permission)
                            <label class="permission-option form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', []), true))>
                                <span class="form-check-label fw-semibold">{{ Str::headline($permission->name) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-maroon">Create Role</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3">
        @foreach ($permissionRoles as $role)
            @php
                $rolePermissionNames = $role->permissions->pluck('name');
                $isLockedRole = $role->name === 'Super Admin';
            @endphp
            <div class="col-12">
                <div class="role-panel p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">{{ $role->name }}</h2>
                            <div class="text-muted small">{{ $rolePermissionNames->count() }} permissions assigned</div>
                        </div>
                        @if ($isLockedRole)
                            <span class="badge text-bg-light border">Locked</span>
                        @endif
                    </div>

                    @if ($isLockedRole)
                        <div class="alert alert-light border small mb-3">Super Admin always keeps all permissions and cannot be changed here.</div>
                    @endif

                    <form method="post" action="{{ route('roles.management.permissions.update', $role) }}">
                        @csrf
                        @method('PUT')
                        <div class="permission-grid">
                            @foreach ($permissions as $permission)
                                <label class="permission-option form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked($rolePermissionNames->contains($permission->name)) @disabled($isLockedRole)>
                                    <span class="form-check-label fw-semibold">{{ Str::headline($permission->name) }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <button class="btn btn-maroon" @disabled($isLockedRole)>Save {{ $role->name }} Permissions</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
