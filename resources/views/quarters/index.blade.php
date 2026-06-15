<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Quarters</h1>
    </x-slot>

    <style>
        .admin-panel {
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

    <div class="admin-panel p-3 mb-3">
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-end">
            <div>
                <h2 class="h5 fw-bold mb-1">Quarter List</h2>
                <div class="text-muted small">Each quarter is calculated as a 90-day planning period.</div>
            </div>
            <button class="btn btn-maroon" type="button" data-bs-toggle="modal" data-bs-target="#createQuarterModal">
                Add Quarter
            </button>
        </div>
    </div>

    <div class="admin-panel table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($quarters as $quarter)
                <tr>
                    <td class="fw-bold">{{ $quarter->name }}</td>
                    <td>{{ $quarter->starts_at->format('d M Y') }} - {{ $quarter->ends_at->format('d M Y') }}</td>
                    <td>
                        @if ($quarter->is_active)
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-light border">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#editQuarterModal{{ $quarter->id }}">
                            Edit
                        </button>
                        <form method="post" action="{{ route('quarters.destroy', $quarter) }}" class="d-inline" onsubmit="return confirm('Delete this quarter?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-4 text-muted">No quarters have been added yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $quarters->links() }}</div>

    <div class="modal fade" id="createQuarterModal" tabindex="-1" aria-labelledby="createQuarterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="{{ route('quarters.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 fw-bold" id="createQuarterModalLabel">Add Quarter</h2>
                        <div class="text-muted small">Create a 90-day planning period.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Quarter Name</label>
                    <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="Q1 2026" required>

                    <label class="form-label fw-semibold">Start Date</label>
                    <input class="form-control mb-3" type="date" name="starts_at" value="{{ old('starts_at') }}" data-quarter-start required>

                    <label class="form-label fw-semibold">End Date</label>
                    <input class="form-control mb-1" type="date" value="" data-quarter-end readonly>
                    <div class="text-muted small mb-3">Automatically calculated as 90 days from the start date.</div>

                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active'))>
                        <span class="form-check-label">Set active</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-maroon">Save Quarter</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($quarters as $quarter)
        <div class="modal fade" id="editQuarterModal{{ $quarter->id }}" tabindex="-1" aria-labelledby="editQuarterModalLabel{{ $quarter->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" action="{{ route('quarters.update', $quarter) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="editQuarterModalLabel{{ $quarter->id }}">Edit Quarter</h2>
                            <div class="text-muted small">Changing the start date recalculates the end date.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-semibold">Quarter Name</label>
                        <input class="form-control mb-3" name="name" value="{{ old('name', $quarter->name) }}" required>

                        <label class="form-label fw-semibold">Start Date</label>
                        <input class="form-control mb-3" type="date" name="starts_at" value="{{ old('starts_at', $quarter->starts_at->toDateString()) }}" data-quarter-start required>

                        <label class="form-label fw-semibold">End Date</label>
                        <input class="form-control mb-1" type="date" value="{{ $quarter->ends_at->toDateString() }}" data-quarter-end readonly>
                        <div class="text-muted small mb-3">Automatically calculated as 90 days from the start date.</div>

                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $quarter->is_active))>
                            <span class="form-check-label">Set active</span>
                        </label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-maroon">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script>
        function calculateQuarterEndDate(startInput) {
            const form = startInput.closest('form');
            const endInput = form?.querySelector('[data-quarter-end]');

            if (! endInput) {
                return;
            }

            if (! startInput.value) {
                endInput.value = '';
                return;
            }

            const date = new Date(`${startInput.value}T00:00:00`);
            date.setDate(date.getDate() + 89);
            endInput.value = date.toISOString().slice(0, 10);
        }

        document.querySelectorAll('[data-quarter-start]').forEach((input) => {
            input.addEventListener('change', () => calculateQuarterEndDate(input));
            calculateQuarterEndDate(input);
        });
    </script>
</x-app-layout>
