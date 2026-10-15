<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Audit Logs</h1>
    </x-slot>

    <div class="mb-3">
        <h2 class="h5 fw-bold mb-1">Activity and Security History</h2>
        <p class="text-muted mb-0">Review authentication, administration, goal, reporting, and permission events.</p>
    </div>

    <form method="get" class="row g-2 align-items-end border-top border-bottom py-3 mb-3">
        <div class="col-lg-4">
            <label class="form-label small fw-semibold" for="audit-search">Search</label>
            <input class="form-control" id="audit-search" type="search" name="search" value="{{ request('search') }}" placeholder="Actor, event, request ID, or IP">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small fw-semibold" for="audit-category">Category</label>
            <select class="form-select" id="audit-category" name="category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ Str::headline($category) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small fw-semibold" for="audit-action">Event</label>
            <select class="form-select" id="audit-action" name="action">
                <option value="">All events</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ Str::headline(str_replace('.', ' ', $action)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small fw-semibold" for="audit-from">From</label>
            <input class="form-control" id="audit-from" type="date" name="date_from" value="{{ request('date_from') }}">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small fw-semibold" for="audit-to">To</label>
            <input class="form-control" id="audit-to" type="date" name="date_to" value="{{ request('date_to') }}">
        </div>
        <div class="col-12 d-flex gap-2 justify-content-end">
            <a class="btn btn-outline-secondary" href="{{ route('audit-logs.index') }}">Reset</a>
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="table-responsive border">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>Event</th>
                    <th>Actor</th>
                    <th>Subject</th>
                    <th>Request</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-nowrap">
                            <div class="fw-semibold">{{ $log->created_at?->format('M d, Y') }}</div>
                            <div class="text-muted small">{{ $log->created_at?->format('H:i:s') }}</div>
                        </td>
                        <td>
                            <span class="badge text-bg-light border">{{ Str::headline($log->category) }}</span>
                            <div class="fw-semibold mt-1">{{ Str::headline(str_replace('.', ' ', $log->action)) }}</div>
                            <div class="text-muted small">{{ $log->description }}</div>
                        </td>
                        <td>
                            <div>{{ $log->actor?->name ?? 'System / unknown' }}</div>
                            <div class="text-muted small">{{ $log->actor?->email }}</div>
                        </td>
                        <td>
                            @if ($log->subject_type)
                                <div>{{ class_basename($log->subject_type) }}</div>
                                <div class="text-muted small">ID {{ $log->subject_id }}</div>
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">{{ $log->ip_address ?: 'No IP' }}</div>
                            <div class="text-muted small font-monospace">{{ Str::limit($log->request_id, 13) }}</div>
                        </td>
                        <td>
                            @if ($log->old_values || $log->new_values || $log->metadata)
                                <details>
                                    <summary class="btn btn-sm btn-outline-secondary">View</summary>
                                    <div class="mt-2" style="min-width: 280px; max-width: 520px;">
                                        @if ($log->old_values)
                                            <div class="small fw-semibold">Before</div>
                                            <pre class="small bg-body-tertiary border p-2 text-wrap">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @endif
                                        @if ($log->new_values)
                                            <div class="small fw-semibold">After</div>
                                            <pre class="small bg-body-tertiary border p-2 text-wrap">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @endif
                                        @if ($log->metadata)
                                            <div class="small fw-semibold">Context</div>
                                            <pre class="small bg-body-tertiary border p-2 text-wrap mb-0">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @endif
                                    </div>
                                </details>
                            @else
                                <span class="text-muted">No field changes</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-muted">No audit events match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $logs->links() }}</div>
</x-app-layout>
