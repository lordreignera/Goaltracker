<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SMART Goals Tracker') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        <style>
            :root {
                --arm-maroon: #8f171b;
                --arm-maroon-dark: #5d0d11;
                --arm-gold: #f3b23a;
                --arm-bg: #f4f6f9;
                --arm-ink: #20242a;
                --arm-muted: #6b7280;
                --arm-line: #e5e7eb;
            }

            body {
                background: var(--arm-bg);
                color: var(--arm-ink);
                font-family: Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }

            .admin-shell {
                min-height: 100vh;
                display: grid;
                grid-template-columns: 278px minmax(0, 1fr);
            }

            .admin-sidebar {
                background:
                    linear-gradient(180deg, rgba(93, 13, 17, .98), rgba(143, 23, 27, .96)),
                    url("https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80");
                background-size: cover;
                background-position: center;
                color: #fff;
                position: sticky;
                top: 0;
                height: 100vh;
                padding: 24px 18px;
                overflow-y: auto;
            }

            .brand-tile {
                width: 52px;
                height: 52px;
                border-radius: 10px;
                background: var(--arm-gold);
                color: #251b13;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.35rem;
                font-weight: 800;
            }

            .sidebar-nav {
                display: grid;
                gap: 6px;
                margin-top: 28px;
            }

            .sidebar-link {
                display: flex;
                align-items: center;
                gap: 12px;
                min-height: 44px;
                padding: 10px 12px;
                border-radius: 8px;
                color: rgba(255, 255, 255, .78);
                text-decoration: none;
                font-weight: 700;
                font-size: .94rem;
            }

            .sidebar-link:hover,
            .sidebar-link.active {
                color: #fff;
                background: rgba(255, 255, 255, .14);
            }

            .sidebar-icon {
                width: 25px;
                height: 25px;
                border-radius: 7px;
                background: rgba(255, 255, 255, .14);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: .8rem;
            }

            .sidebar-section {
                color: rgba(255, 255, 255, .5);
                font-size: .72rem;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: .08em;
                margin: 24px 12px 8px;
            }

            .admin-main {
                min-width: 0;
            }

            .sidebar-backdrop,
            .mobile-nav-toggle,
            .mobile-sidebar-close {
                display: none;
            }

            .admin-topbar {
                min-height: 74px;
                background: rgba(255, 255, 255, .92);
                border-bottom: 1px solid var(--arm-line);
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                padding: 0 34px;
                position: sticky;
                top: 0;
                z-index: 20;
                backdrop-filter: blur(10px);
            }

            .page-title {
                font-size: 1.25rem;
                font-weight: 800;
                margin: 0;
            }

            .user-pill {
                border: 1px solid var(--arm-line);
                border-radius: 999px;
                background: #fff;
                padding: 8px 12px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .avatar-dot {
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background: var(--arm-maroon);
                color: #fff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 800;
            }

            .admin-content {
                padding: 28px 34px 48px;
            }

            .content-container {
                max-width: 1240px;
                margin: 0 auto;
            }

            .logout-button {
                border: 0;
                background: transparent;
                color: var(--arm-maroon);
                font-weight: 800;
                padding: 0;
            }

            .mobile-menu {
                display: none;
            }

            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }

            .admin-content .table-responsive {
                border: 1px solid var(--arm-line);
                border-radius: 12px;
                background: #fff;
                overflow: auto;
            }

            .table {
                min-width: 760px;
                margin-bottom: 0;
                border-color: var(--arm-line);
                color: var(--arm-ink);
            }

            .admin-content .table > :not(caption) > * > * {
                padding: 15px 16px;
                border-bottom-color: var(--arm-line);
                background: transparent;
            }

            .admin-content .table > :not(caption) > * > *:not(:last-child) {
                border-right: 1px solid #f0f2f5;
            }

            .admin-content .table thead th {
                background: #fff;
                color: #1f2329;
                font-size: .86rem;
                font-weight: 800;
                letter-spacing: 0;
                white-space: nowrap;
                border-bottom: 1px solid var(--arm-line);
            }

            .admin-content .table tbody tr {
                background: #fff;
                transition: background-color .16s ease;
            }

            .admin-content .table tbody tr:hover {
                background: #fafbfc;
            }

            .admin-content .table tbody tr:last-child > * {
                border-bottom: 0;
            }

            .table td,
            .table th {
                vertical-align: middle;
            }

            .table td:last-child {
                white-space: nowrap;
            }

            .admin-content .badge {
                border-radius: 999px;
                font-weight: 800;
                padding: .42rem .62rem;
            }

            .admin-content .btn-sm {
                border-radius: 9px;
                font-weight: 800;
                padding-inline: .75rem;
                box-shadow: 0 4px 12px rgba(17, 24, 39, .05);
            }

            .admin-content .btn-outline-secondary,
            .admin-content .btn-outline-dark,
            .admin-content .btn-outline-danger {
                background: #fff;
            }

            .pagination {
                flex-wrap: wrap;
                gap: 4px;
            }

            @media (max-width: 991.98px) {
                .admin-shell {
                    display: block;
                }

                .admin-sidebar {
                    position: fixed;
                    inset: 0 auto 0 0;
                    width: min(86vw, 320px);
                    height: 100vh;
                    border-radius: 0;
                    z-index: 1040;
                    transform: translateX(-105%);
                    transition: transform .22s ease;
                    box-shadow: 24px 0 60px rgba(17, 24, 39, .24);
                }

                body.sidebar-open .admin-sidebar {
                    transform: translateX(0);
                }

                .sidebar-backdrop {
                    display: block;
                    position: fixed;
                    inset: 0;
                    background: rgba(17, 24, 39, .5);
                    z-index: 1035;
                    opacity: 0;
                    pointer-events: none;
                    transition: opacity .22s ease;
                }

                body.sidebar-open .sidebar-backdrop {
                    opacity: 1;
                    pointer-events: auto;
                }

                .mobile-sidebar-close {
                    display: inline-flex;
                    width: 38px;
                    height: 38px;
                    align-items: center;
                    justify-content: center;
                    border: 1px solid rgba(255, 255, 255, .22);
                    border-radius: 8px;
                    color: #fff;
                    background: rgba(255, 255, 255, .12);
                    font-weight: 900;
                    margin-left: auto;
                }

                .mobile-nav-toggle {
                    display: inline-flex;
                    min-width: 58px;
                    height: 42px;
                    align-items: center;
                    justify-content: center;
                    border: 1px solid var(--arm-line);
                    border-radius: 9px;
                    background: #fff;
                    color: var(--arm-maroon);
                    font-weight: 900;
                }

                .admin-topbar {
                    min-height: auto;
                    padding: 14px 16px;
                    align-items: stretch;
                    flex-direction: column;
                    gap: 14px;
                }

                .admin-content {
                    padding: 22px 16px 36px;
                }

                .topbar-title-row {
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                }

                .topbar-title-row > div {
                    min-width: 0;
                }

                .page-title {
                    font-size: 1.1rem;
                    overflow-wrap: anywhere;
                }

                .topbar-actions {
                    justify-content: space-between;
                    flex-wrap: wrap;
                    gap: 10px !important;
                }

                .user-pill {
                    min-width: 0;
                    flex: 1 1 220px;
                }

                .user-pill > div {
                    min-width: 0;
                }

                .user-pill .fw-bold,
                .user-pill .text-muted {
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
            }

            @media (max-width: 575.98px) {
                .admin-sidebar {
                    width: min(92vw, 310px);
                    padding: 18px 14px;
                }

                .brand-tile {
                    width: 46px;
                    height: 46px;
                    font-size: 1.1rem;
                }

                .sidebar-link {
                    min-height: 42px;
                    font-size: .9rem;
                }

                .admin-content {
                    padding: 16px 10px 30px;
                }

                .content-container {
                    max-width: 100%;
                }

                .table {
                    min-width: 680px;
                }

                .btn,
                .form-control,
                .form-select {
                    min-height: 42px;
                }

                .btn-sm {
                    min-height: 34px;
                }

                .row {
                    --bs-gutter-x: .75rem;
                }

                .p-4 {
                    padding: 1rem !important;
                }
            }
        </style>
    </head>
    <body>
        <x-banner />

        @php
            $currentUser = Auth::user();
            $canManageAdministration = $currentUser->isAdmin();
        @endphp

        <div class="sidebar-backdrop" data-sidebar-close></div>

        <div class="admin-shell">
            <aside class="admin-sidebar">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-tile">90</div>
                    <div>
                        <div class="fw-bold fs-5 lh-sm">Africa Renewal</div>
                        <div class="opacity-75 small">SMART Goals Tracker</div>
                    </div>
                    <button class="mobile-sidebar-close" type="button" aria-label="Close menu" data-sidebar-close>&times;</button>
                </div>

                <div class="sidebar-section">Workspace</div>
                <nav class="sidebar-nav">
                    <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="sidebar-icon">D</span> Dashboard
                    </a>
                    <a class="sidebar-link {{ request()->routeIs('goals.*') ? 'active' : '' }}" href="{{ route('goals.index') }}">
                        <span class="sidebar-icon">G</span> Goals
                    </a>
                    @if ($canManageAdministration)
                        <a class="sidebar-link {{ request()->routeIs('users.management.*') || request()->routeIs('users.approvals.*') ? 'active' : '' }}" href="{{ route('users.management.index') }}">
                            <span class="sidebar-icon">UM</span> User Management
                        </a>
                         <a class="sidebar-link {{ request()->routeIs('roles.management.*') ? 'active' : '' }}" href="{{ route('roles.management.index') }}">
                            <span class="sidebar-icon">R</span> Roles & Permissions
                        </a>
                    @endif
                </nav>

                @if ($canManageAdministration)
                    <div class="sidebar-section">Administration</div>
                    <nav class="sidebar-nav">
                        <a class="sidebar-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">
                            <span class="sidebar-icon">DP</span> Departments
                        </a>
                        <a class="sidebar-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}">
                            <span class="sidebar-icon">U</span> Units
                        </a>
                        <a class="sidebar-link {{ request()->routeIs('quarters.*') ? 'active' : '' }}" href="{{ route('quarters.index') }}">
                            <span class="sidebar-icon">Q</span> Quarters
                        </a>
                    </nav>
                @endif

                <div class="mt-5 p-3 rounded-3" style="background: rgba(255,255,255,.12);">
                    <div class="small opacity-75">Current focus</div>
                    <div class="fw-bold mt-1">90-day accountability cycle</div>
                </div>
            </aside>

            <div class="admin-main">
                <header class="admin-topbar">
                    <div class="topbar-title-row">
                        <button class="mobile-nav-toggle" type="button" aria-label="Open menu" data-sidebar-open>Menu</button>
                        <div>
                            @if (isset($header))
                                {{ $header }}
                            @else
                                <h1 class="page-title">Dashboard</h1>
                            @endif
                            <div class="text-muted small mt-1">Plan, review, approve, and report on measurable goals.</div>
                        </div>
                    </div>

                    <div class="topbar-actions d-flex align-items-center gap-3">
                        <a class="text-decoration-none fw-bold" style="color: var(--arm-maroon);" href="{{ route('profile.show') }}">Profile</a>
                        <div class="user-pill">
                            <span class="avatar-dot">{{ strtoupper(substr($currentUser->name, 0, 1)) }}</span>
                            <div>
                                <div class="fw-bold lh-sm">{{ $currentUser->name }}</div>
                                <div class="text-muted small">{{ $currentUser->getRoleNames()->first() ?? ucfirst($currentUser->role) }}</div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="logout-button" type="submit">Logout</button>
                        </form>
                    </div>
                </header>

                <main class="admin-content">
                    <div class="content-container">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @stack('modals')

        @livewireScripts

        <script>
            document.querySelectorAll('[data-sidebar-open]').forEach((button) => {
                button.addEventListener('click', () => document.body.classList.add('sidebar-open'));
            });

            document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
                button.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    document.body.classList.remove('sidebar-open');
                }
            });
        </script>
    </body>
</html>
