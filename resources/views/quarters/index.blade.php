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

    <div class="row g-4">
        <div class="col-xl-4">
            <form method="post" action="{{ route('quarters.store') }}" class="admin-panel p-4">
                @csrf
                <h2 class="h5 fw-bold mb-1">Add Quarter</h2>
                <p class="text-muted small mb-3">Create a 90-day planning period.</p>

                <label class="form-label fw-semibold">Quarter Name</label>
                <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="Q1 2026" required>

                <label class="form-label fw-semibold">Start Date</label>
                <input id="quarter-start-date" class="form-control mb-3" type="date" name="starts_at" value="{{ old('starts_at') }}" required>

                <label class="form-label fw-semibold">End Date</label>
                <input id="quarter-end-date" class="form-control mb-1" type="date" name="ends_at_preview" value="{{ old('starts_at') ? \Illuminate\Support\Carbon::parse(old('starts_at'))->addDays(89)->toDateString() : '' }}" readonly>
                <div class="text-muted small mb-3">Automatically calculated as a 90-day period.</div>

                <label class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1">
                    <span class="form-check-label">Set active</span>
                </label>

                <button class="btn btn-maroon w-100">Save Quarter</button>
            </form>
        </div>

        <div class="col-xl-8">
            <div class="admin-panel table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Dates</th>
                            <th>Status</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-4 text-muted">No quarters have been added yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $quarters->links() }}</div>
        </div>
    </div>

    <script>
        const startDateInput = document.getElementById('quarter-start-date');
        const endDateInput = document.getElementById('quarter-end-date');

        function calculateQuarterEndDate() {
            if (! startDateInput.value) {
                endDateInput.value = '';
                return;
            }

            const date = new Date(`${startDateInput.value}T00:00:00`);
            date.setDate(date.getDate() + 89);
            endDateInput.value = date.toISOString().slice(0, 10);
        }

        startDateInput?.addEventListener('change', calculateQuarterEndDate);
        calculateQuarterEndDate();
    </script>
</x-app-layout>
