<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-1">Notifications</h1>
                <p class="text-muted mb-0">Submissions, review decisions, and reporting reminders.</p>
            </div>
            @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="btn btn-outline-primary" type="submit">Mark all as read</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="panel-card overflow-hidden">
        @forelse ($notifications as $notification)
            <a class="notification-item d-block text-decoration-none {{ $notification->read_at ? '' : 'bg-light' }}"
               href="{{ route('notifications.read', $notification) }}">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="fw-bold text-dark">{{ $notification->data['title'] ?? 'Notification' }}</div>
                        <div class="text-muted mt-1">{{ $notification->data['message'] ?? '' }}</div>
                    </div>
                    @unless ($notification->read_at)
                        <span class="badge text-bg-primary align-self-start">New</span>
                    @endunless
                </div>
                <div class="text-muted small mt-2">{{ $notification->created_at->diffForHumans() }}</div>
            </a>
        @empty
            <div class="p-5 text-center text-muted">No notifications yet.</div>
        @endforelse
    </div>

    <div class="mt-3">{{ $notifications->links() }}</div>
</x-app-layout>
