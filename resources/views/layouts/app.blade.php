<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
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
                --arm-maroon: #111827;
                --arm-maroon-dark: #000000;
                --arm-maroon-soft: #e5e7eb;
                --arm-gold: #f3f4f6;
                --arm-bg: #f6f7f9;
                --arm-ink: #171a1f;
                --arm-muted: #6b7280;
                --arm-line: #e5e7eb;
                --arm-surface: #ffffff;
                --arm-surface-soft: #fafbfc;
                --arm-topbar: rgba(255, 255, 255, .92);
                --arm-field: #ffffff;
                --arm-table-divider: #f0f2f5;
            }

            html[data-theme="dark"] {
                --arm-bg: #0f1217;
                --arm-ink: #f3f4f6;
                --arm-muted: #a4adbb;
                --arm-line: #29313d;
                --arm-surface: #171b22;
                --arm-surface-soft: #1e242d;
                --arm-topbar: rgba(23, 27, 34, .92);
                --arm-field: #11161d;
                --arm-table-divider: #26303c;
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
                    linear-gradient(180deg, #ffffff, #f3f4f6 58%, #e5e7eb),
                    linear-gradient(180deg, rgba(0,0,0,.02), rgba(0,0,0,.01));
                background-size: auto;
                background-position: center;
                color: #111827;
                position: sticky;
                top: 0;
                height: 100vh;
                padding: 24px 18px;
                overflow-y: auto;
                backdrop-filter: none;
                border-right: 1px solid #d1d5db;
            }

            .brand-tile {
                width: 52px;
                height: 52px;
                border-radius: 10px;
                background: #fff;
                color: #111827;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.35rem;
                font-weight: 800;
                box-shadow: inset 0 0 0 1px rgba(17, 24, 39, .08);
            }

            .brand-logo {
                width: 52px;
                height: 52px;
                border-radius: 10px;
                object-fit: contain;
                background: #fff;
                padding: 5px;
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
                color: rgba(17, 24, 39, .78);
                text-decoration: none;
                font-weight: 700;
                font-size: .94rem;
            }

            .sidebar-link:hover,
            .sidebar-link.active {
                color: #000;
                background: rgba(255, 255, 255, .78);
            }

            .sidebar-icon {
                width: 25px;
                height: 25px;
                border-radius: 7px;
                background: rgba(17, 24, 39, .08);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: .8rem;
            }

            .sidebar-link:hover .sidebar-icon,
            .sidebar-link.active .sidebar-icon {
                background: #111827;
                color: #fff;
            }

            .sidebar-section {
                color: rgba(17, 24, 39, .58);
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
                background: var(--arm-topbar);
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
                background: var(--arm-surface);
                padding: 8px 12px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .avatar-dot {
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background: #111827;
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
                color: var(--arm-ink);
                font-weight: 800;
                padding: 0;
            }

            .theme-toggle {
                border: 1px solid var(--arm-line);
                border-radius: 999px;
                background: var(--arm-surface);
                color: var(--arm-ink);
                min-height: 38px;
                padding: 7px 12px;
                font-weight: 800;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .theme-toggle:hover,
            .theme-toggle:focus {
                background: var(--arm-surface-soft);
            }

            .notification-button {
                width: 40px;
                height: 40px;
                border: 1px solid var(--arm-line);
                border-radius: 999px;
                background: var(--arm-surface);
                color: var(--arm-ink);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
                font-weight: 900;
                position: relative;
            }

            .notification-button:hover,
            .notification-button:focus {
                background: var(--arm-surface-soft);
            }

            .notification-count {
                position: absolute;
                top: -5px;
                right: -5px;
                min-width: 19px;
                height: 19px;
                border-radius: 999px;
                background: #dc3545;
                color: #fff;
                border: 2px solid var(--arm-topbar);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: .68rem;
                line-height: 1;
                font-weight: 900;
                padding: 0 5px;
            }

            .notification-menu {
                width: min(92vw, 360px);
                padding: 0;
                border-color: var(--arm-line);
                background: var(--arm-surface);
                color: var(--arm-ink);
                box-shadow: 0 18px 48px rgba(17, 24, 39, .14);
            }

            .notification-item {
                display: block;
                padding: 12px 14px;
                color: var(--arm-ink);
                text-decoration: none;
                border-top: 1px solid var(--arm-line);
            }

            .notification-item:hover,
            .notification-item:focus {
                background: var(--arm-surface-soft);
                color: var(--arm-ink);
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
                background: var(--arm-surface);
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
                border-right: 1px solid var(--arm-table-divider);
            }

            .admin-content .table thead th {
                background: var(--arm-surface);
                color: var(--arm-ink);
                font-size: .86rem;
                font-weight: 800;
                letter-spacing: 0;
                white-space: nowrap;
                border-bottom: 1px solid var(--arm-line);
            }

            .admin-content .table tbody tr {
                background: var(--arm-surface);
                transition: background-color .16s ease;
            }

            .admin-content .table tbody tr:hover {
                background: var(--arm-surface-soft);
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

            .admin-content .btn,
            .admin-content summary.btn {
                transition: color .16s ease, background-color .16s ease, border-color .16s ease, box-shadow .16s ease;
            }

            .admin-content .btn:focus-visible,
            .admin-content summary.btn:focus-visible {
                outline: 3px solid rgba(243, 178, 58, .45);
                outline-offset: 2px;
                box-shadow: 0 0 0 .18rem rgba(17, 24, 39, .12);
            }

            .admin-content .btn-maroon,
            .admin-content .btn[style*="#8f171b"],
            .admin-content .btn[style*="#c43b3f"] {
                background: var(--arm-maroon) !important;
                border-color: var(--arm-maroon) !important;
                color: #fff !important;
            }

            .admin-content .btn-maroon:hover,
            .admin-content .btn-maroon:focus,
            .admin-content .btn-maroon:active,
            .admin-content .btn[style*="#8f171b"]:hover,
            .admin-content .btn[style*="#8f171b"]:focus,
            .admin-content .btn[style*="#8f171b"]:active,
            .admin-content .btn[style*="#c43b3f"]:hover,
            .admin-content .btn[style*="#c43b3f"]:focus,
            .admin-content .btn[style*="#c43b3f"]:active {
                background: var(--arm-maroon-dark) !important;
                border-color: var(--arm-maroon-dark) !important;
                color: #fff !important;
            }

            .admin-content .btn-outline-secondary,
            .admin-content .btn-outline-dark,
            .admin-content .btn-outline-danger {
                background: var(--arm-surface);
            }

            .admin-content .btn-outline-secondary {
                color: #4b5563;
                border-color: #9ca3af;
            }

            .admin-content .btn-outline-secondary:hover,
            .admin-content .btn-outline-secondary:focus,
            .admin-content .btn-outline-secondary:active,
            .admin-content .btn-outline-secondary.show,
            .admin-content details[open] > summary.btn-outline-secondary {
                background: #4b5563 !important;
                border-color: #4b5563 !important;
                color: #fff !important;
            }

            .admin-content .btn-outline-success {
                color: #147a48;
                border-color: #198754;
                background: var(--arm-surface);
            }

            .admin-content .form-control,
            .admin-content .form-select,
            .admin-content textarea.form-control {
                background-color: var(--arm-field);
                border-color: var(--arm-line);
                color: var(--arm-ink);
            }

            .admin-content .form-control:focus,
            .admin-content .form-select:focus,
            .admin-content textarea.form-control:focus {
                background-color: var(--arm-field);
                border-color: #6b7280;
                color: var(--arm-ink);
                box-shadow: 0 0 0 .2rem rgba(107, 114, 128, .18);
            }

            .admin-content .form-control::placeholder,
            .admin-content textarea.form-control::placeholder {
                color: var(--arm-muted);
            }

            html[data-theme="dark"] .admin-content .card,
            html[data-theme="dark"] .admin-content .modal-content,
            html[data-theme="dark"] .admin-content .dropdown-menu,
            html[data-theme="dark"] .admin-content .dashboard-card,
            html[data-theme="dark"] .admin-content .stat-card,
            html[data-theme="dark"] .admin-content .admin-panel,
            html[data-theme="dark"] .admin-content .user-panel,
            html[data-theme="dark"] .admin-content .goal-panel,
            html[data-theme="dark"] .admin-content .goal-filter {
                background: var(--arm-surface) !important;
                border-color: var(--arm-line) !important;
                color: var(--arm-ink) !important;
                box-shadow: none !important;
            }

            html[data-theme="dark"] .admin-content .text-muted,
            html[data-theme="dark"] .admin-topbar .text-muted {
                color: var(--arm-muted) !important;
            }

            html[data-theme="dark"] .admin-content .table-light,
            html[data-theme="dark"] .admin-content .table-light > * > * {
                background: var(--arm-surface-soft) !important;
                color: var(--arm-ink) !important;
            }

            html[data-theme="dark"] .admin-content .text-bg-light {
                background-color: var(--arm-surface-soft) !important;
                color: var(--arm-ink) !important;
                border-color: var(--arm-line) !important;
            }

            html[data-theme="dark"] .admin-content .border,
            html[data-theme="dark"] .admin-content .border-bottom {
                border-color: var(--arm-line) !important;
            }

            html[data-theme="dark"] .admin-content .progress {
                background-color: #2a3340;
            }

            .admin-content .btn-outline-success:hover,
            .admin-content .btn-outline-success:focus,
            .admin-content .btn-outline-success:active {
                background: #147a48 !important;
                border-color: #147a48 !important;
                color: #fff !important;
            }

            .admin-content .btn-outline-danger {
                color: #b42318;
                border-color: #dc3545;
            }

            .admin-content .btn-outline-danger:hover,
            .admin-content .btn-outline-danger:focus,
            .admin-content .btn-outline-danger:active {
                background: #b42318 !important;
                border-color: #b42318 !important;
                color: #fff !important;
            }

            .admin-content .btn-outline-dark {
                color: #1f2937;
                border-color: #1f2937;
            }

            .admin-content .btn-outline-dark:hover,
            .admin-content .btn-outline-dark:focus,
            .admin-content .btn-outline-dark:active {
                background: #1f2937 !important;
                border-color: #1f2937 !important;
                color: #fff !important;
            }

            .admin-content .btn-primary {
                background: #0d6efd;
                border-color: #0d6efd;
                color: #fff;
            }

            .admin-content .btn-primary:hover,
            .admin-content .btn-primary:focus,
            .admin-content .btn-primary:active {
                background: #084298 !important;
                border-color: #084298 !important;
                color: #fff !important;
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
                    border: 1px solid rgba(255, 255, 255, .18);
                    border-radius: 8px;
                    color: #fff;
                    background: rgba(255, 255, 255, .08);
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
                    color: #111318;
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

                .brand-logo {
                    width: 46px;
                    height: 46px;
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
            $companySettings = \App\Models\CompanySetting::current();
            $currentUser = Auth::user();
            $canManageDepartments = $currentUser->isAdmin() || $currentUser->can('manage departments');
            $canManageSections = $currentUser->isAdmin() || $currentUser->can('manage sections');
            $canManageUnits = $currentUser->isAdmin() || $currentUser->can('manage units');
            $canManageUsers = $currentUser->isAdmin() || $currentUser->can('manage users');
            $canManageQuarters = $currentUser->isAdmin() || $currentUser->can('manage quarters');
            $canManageRoles = $currentUser->isAdmin();
            $canManageSettings = $currentUser->isAdmin();
            $canManageAdministration = $currentUser->canManageAdministration();
            $pendingRegistrationCount = $canManageUsers
                ? \App\Models\User::where('approval_status', 'pending')->count()
                : 0;
            $recentPendingUsers = $canManageUsers
                ? \App\Models\User::with('department')
                    ->where('approval_status', 'pending')
                    ->latest()
                    ->take(5)
                    ->get()
                : collect();
        @endphp

        <div class="sidebar-backdrop" data-sidebar-close></div>

        <div class="admin-shell">
            <aside class="admin-sidebar">
                <div class="d-flex align-items-center gap-3">
                    @if ($companySettings->logoUrl())
                        <img class="brand-logo" src="{{ $companySettings->logoUrl() }}" alt="{{ $companySettings->company_name }} logo">
                    @else
                        <div class="brand-tile">{{ $companySettings->brand_mark }}</div>
                    @endif
                    <div>
                        <div class="fw-bold fs-5 lh-sm">{{ $companySettings->company_short_name }}</div>
                        <div class="opacity-75 small">{{ $companySettings->product_name }}</div>
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
                    <a class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.quarterly.index') }}">
                        <span class="sidebar-icon">RP</span> Reports
                    </a>
                    @if ($canManageUsers)
                        <a class="sidebar-link {{ request()->routeIs('users.management.*') || request()->routeIs('users.approvals.*') ? 'active' : '' }}" href="{{ route('users.management.index') }}">
                            <span class="sidebar-icon">UM</span> User Management
                        </a>
                    @endif
                    @if ($canManageRoles)
                         <a class="sidebar-link {{ request()->routeIs('roles.management.*') ? 'active' : '' }}" href="{{ route('roles.management.index') }}">
                            <span class="sidebar-icon">R</span> Roles & Permissions
                        </a>
                    @endif
                </nav>

                @if ($canManageAdministration)
                    <div class="sidebar-section">Administration</div>
                    <nav class="sidebar-nav">
                        @if ($canManageDepartments)
                            <a class="sidebar-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">
                                <span class="sidebar-icon">DP</span> Departments
                            </a>
                        @endif
                        @if ($canManageSections)
                            <a class="sidebar-link {{ request()->routeIs('sections.*') ? 'active' : '' }}" href="{{ route('sections.index') }}">
                                <span class="sidebar-icon">SC</span> Sections
                            </a>
                        @endif
                        @if ($canManageUnits)
                            <a class="sidebar-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}">
                                <span class="sidebar-icon">U</span> Units
                            </a>
                        @endif
                        @if ($canManageQuarters)
                            <a class="sidebar-link {{ request()->routeIs('quarters.*') ? 'active' : '' }}" href="{{ route('quarters.index') }}">
                                <span class="sidebar-icon">Q</span> Quarters
                            </a>
                        @endif
                        @if ($canManageSettings)
                            <a class="sidebar-link {{ request()->routeIs('settings.company.*') ? 'active' : '' }}" href="{{ route('settings.company.edit') }}">
                                <span class="sidebar-icon">S</span> Company Settings
                            </a>
                        @endif
                    </nav>
                @endif

                <div class="mt-5 p-3 rounded-3" style="background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.10);">
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
                            <div class="text-muted small mt-1">{{ $companySettings->tagline }}</div>
                        </div>
                    </div>

                    <div class="topbar-actions d-flex align-items-center gap-3">
                        @if ($canManageUsers)
                            <div class="dropdown">
                                <button class="notification-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Registration notifications">
                                    &#128276;
                                    @if ($pendingRegistrationCount > 0)
                                        <span class="notification-count">{{ $pendingRegistrationCount > 9 ? '9+' : $pendingRegistrationCount }}</span>
                                    @endif
                                </button>
                                <div class="dropdown-menu dropdown-menu-end notification-menu">
                                    <div class="p-3">
                                        <div class="fw-bold">Registration Requests</div>
                                        <div class="text-muted small">{{ $pendingRegistrationCount }} waiting approval</div>
                                    </div>

                                    @forelse ($recentPendingUsers as $pendingUser)
                                        <a class="notification-item" href="{{ route('users.management.index', ['status' => 'pending', 'edit_user' => $pendingUser->id]) }}">
                                            <div class="fw-bold">{{ $pendingUser->name }}</div>
                                            <div class="text-muted small">{{ $pendingUser->email }}</div>
                                            <div class="text-muted small">{{ $pendingUser->department?->name ?? 'No department selected' }}</div>
                                        </a>
                                    @empty
                                        <div class="px-3 py-4 text-muted small border-top">No new registration requests.</div>
                                    @endforelse

                                    <div class="p-2 border-top">
                                        <a class="btn btn-sm btn-maroon w-100" href="{{ route('users.management.index', ['status' => 'pending']) }}">View Approvals</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
                            <span data-theme-icon>L</span>
                            <span data-theme-label>Light</span>
                        </button>
                        <a class="text-decoration-none fw-bold" style="color: var(--arm-ink);" href="{{ route('profile.show') }}">Profile</a>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            const themeKey = 'smart-goals-theme';
            const root = document.documentElement;

            function applyTheme(theme) {
                const nextTheme = theme === 'dark' ? 'dark' : 'light';
                root.dataset.theme = nextTheme;
                localStorage.setItem(themeKey, nextTheme);

                document.querySelectorAll('[data-theme-label]').forEach((label) => {
                    label.textContent = nextTheme === 'dark' ? 'Dark' : 'Light';
                });

                document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
                    icon.textContent = nextTheme === 'dark' ? 'D' : 'L';
                });
            }

            applyTheme(localStorage.getItem(themeKey) || 'light');

            document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
                });
            });

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
