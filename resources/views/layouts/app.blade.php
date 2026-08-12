<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SMART Goals Tracker') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        <style>
            :root {
                --sg-navy: #172554;
                --sg-blue: #2563eb;
                --sg-bg: #f8fafc;
                --sg-surface: #ffffff;
                --sg-surface-soft: #f1f5f9;
                --sg-text: #0f172a;
                --sg-muted: #64748b;
                --sg-success: #10b981;
                --sg-warning: #f59e0b;
                --sg-danger: #ef4444;
                --sg-border: #e2e8f0;
                --sg-field: #ffffff;
                --sg-shadow: 0 14px 36px rgba(15, 23, 42, .06);
                --sg-topbar: rgba(255, 255, 255, .92);
                --arm-maroon: var(--sg-blue);
                --arm-maroon-dark: #1d4ed8;
                --arm-line: var(--sg-border);
                --arm-surface: var(--sg-surface);
                --arm-ink: var(--sg-text);
                --arm-muted: var(--sg-muted);
                --arm-field: var(--sg-field);
                --arm-line: var(--sg-border);
            }

            html[data-theme="dark"] {
                --sg-bg: #0f172a;
                --sg-surface: #111827;
                --sg-surface-soft: #1e293b;
                --sg-text: #f8fafc;
                --sg-muted: #94a3b8;
                --sg-border: #334155;
                --sg-field: #0f172a;
                --sg-shadow: none;
                --sg-topbar: rgba(17, 24, 39, .92);
            }

            html,
            body {
                max-width: 100%;
                overflow-x: hidden;
            }

            body {
                margin: 0;
                background: var(--sg-bg);
                color: var(--sg-text);
                font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                letter-spacing: 0;
            }

            .app-shell {
                min-height: 100vh;
                display: block;
            }

            .app-shell.sidebar-collapsed {
                display: block;
            }

            .app-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                width: 280px;
                height: 100vh;
                overflow-y: auto;
                padding: 24px 18px;
                background: var(--sg-surface);
                border-right: 1px solid var(--sg-border);
                z-index: 30;
                transition: width .2s ease, padding .2s ease, transform .22s ease;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .app-sidebar::-webkit-scrollbar {
                width: 0;
                height: 0;
            }

            .sidebar-brand-row {
                min-width: 0;
            }

            .brand-copy {
                min-width: 0;
            }

            .brand-mark,
            .brand-logo {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                flex: 0 0 auto;
            }

            .brand-mark {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #eef2ff;
                color: var(--sg-navy);
                border: 1px solid #dbe4ff;
                font-weight: 800;
                font-size: .95rem;
            }

            .brand-logo {
                object-fit: contain;
                background: #fff;
                padding: 5px;
                border: 1px solid var(--sg-border);
            }

            .brand-title {
                color: var(--sg-navy);
                font-weight: 800;
                font-size: 1rem;
                line-height: 1.1;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .brand-subtitle {
                color: var(--sg-muted);
                font-size: .74rem;
                line-height: 1.2;
                margin-top: 3px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .sidebar-collapse-toggle {
                width: 34px;
                height: 34px;
                border: 1px solid var(--sg-border);
                border-radius: 10px;
                background: var(--sg-surface);
                color: var(--sg-muted);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-left: auto;
                flex: 0 0 auto;
            }

            .sidebar-collapse-toggle:hover {
                background: var(--sg-surface-soft);
                color: var(--sg-blue);
            }

            .sidebar-collapse-toggle svg {
                width: 16px;
                height: 16px;
                transform: rotate(90deg);
                transition: transform .2s ease;
            }

            .sidebar-section {
                color: var(--sg-muted);
                font-size: .68rem;
                font-weight: 800;
                text-transform: uppercase;
                margin: 26px 10px 8px;
            }

            .sidebar-nav {
                display: grid;
                gap: 4px;
            }

            .sidebar-link {
                position: relative;
                display: flex;
                align-items: center;
                gap: 11px;
                min-height: 42px;
                padding: 10px 12px;
                border-radius: 10px;
                color: var(--sg-muted);
                text-decoration: none;
                font-size: .9rem;
                font-weight: 700;
                transition: color .16s ease, background-color .16s ease;
            }

            .sidebar-link::before {
                content: "";
                position: absolute;
                left: 0;
                top: 10px;
                bottom: 10px;
                width: 3px;
                border-radius: 999px;
                background: transparent;
            }

            .sidebar-link svg {
                width: 18px;
                height: 18px;
                color: #475569;
                flex: 0 0 auto;
            }

            .sidebar-link:hover {
                color: var(--sg-navy);
                background: #eff6ff;
            }

            .sidebar-link:hover svg {
                color: var(--sg-blue);
            }

            .sidebar-link.active {
                color: var(--sg-navy);
                background: #dbeafe;
            }

            .sidebar-link.active::before {
                background: var(--sg-blue);
            }

            .sidebar-link.active svg {
                color: var(--sg-blue);
            }

            .sidebar-help {
                display: block;
                margin-top: 28px;
                padding: 14px;
                border: 1px solid var(--sg-border);
                border-radius: 12px;
                background: var(--sg-surface);
                color: var(--sg-text);
                text-decoration: none;
                box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
                transition: border-color .16s ease, background-color .16s ease, transform .16s ease;
            }

            .sidebar-help:hover,
            .sidebar-help:focus {
                background: #eff6ff;
                border-color: #bfdbfe;
                color: var(--sg-text);
                transform: translateY(-1px);
            }

            .sidebar-help svg {
                width: 20px;
                height: 20px;
                color: var(--sg-blue);
            }

            .app-shell.sidebar-collapsed .app-sidebar {
                width: 88px;
                padding-inline: 14px;
            }

            .app-shell.sidebar-collapsed .sidebar-brand-row {
                justify-content: center;
            }

            .app-shell.sidebar-collapsed .brand-copy,
            .app-shell.sidebar-collapsed .sidebar-section,
            .app-shell.sidebar-collapsed .sidebar-link span,
            .app-shell.sidebar-collapsed .sidebar-help .help-copy {
                display: none;
            }

            .app-shell.sidebar-collapsed .sidebar-link {
                justify-content: center;
                padding-inline: 10px;
            }

            .app-shell.sidebar-collapsed .sidebar-link::before {
                left: -6px;
            }

            .app-shell.sidebar-collapsed .sidebar-help {
                padding: 11px;
                display: flex;
                justify-content: center;
            }

            .app-shell.sidebar-collapsed .sidebar-collapse-toggle svg {
                transform: rotate(-90deg);
            }

            .app-main {
                min-width: 0;
                margin-left: 280px;
                transition: margin-left .2s ease;
            }

            .app-shell.sidebar-collapsed .app-main {
                margin-left: 88px;
            }

            .app-topbar {
                position: sticky;
                top: 0;
                z-index: 20;
                min-height: 82px;
                padding: 18px 32px;
                background: var(--sg-topbar);
                border-bottom: 1px solid var(--sg-border);
                backdrop-filter: blur(14px);
            }

            .topbar-inner {
                max-width: 1600px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 22px;
                min-width: 0;
            }

            .topbar-greeting {
                font-size: 1.05rem;
                font-weight: 800;
                color: var(--sg-text);
                margin: 0;
            }

            .topbar-support {
                margin: 4px 0 0;
                color: var(--sg-muted);
                font-size: .86rem;
            }

            .topbar-actions {
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 0;
                flex: 0 1 auto;
            }

            .topbar-search {
                position: relative;
                width: clamp(220px, 24vw, 340px);
                flex: 0 1 340px;
            }

            .topbar-search svg {
                position: absolute;
                left: 13px;
                top: 50%;
                transform: translateY(-50%);
                width: 17px;
                height: 17px;
                color: var(--sg-muted);
            }

            .topbar-search input {
                width: 100%;
                min-height: 42px;
                border: 1px solid var(--sg-border);
                border-radius: 10px;
                background: var(--sg-field);
                color: var(--sg-text);
                padding: 9px 12px 9px 40px;
                font-size: .88rem;
            }

            .topbar-search input:focus {
                outline: 0;
                border-color: var(--sg-blue);
                box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
            }

            .icon-button,
            .theme-toggle,
            .mobile-nav-toggle,
            .mobile-sidebar-close {
                border: 1px solid var(--sg-border);
                background: var(--sg-surface);
                color: var(--sg-text);
                min-height: 42px;
                border-radius: 10px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: background-color .16s ease, border-color .16s ease;
            }

            .icon-button {
                width: 42px;
                position: relative;
            }

            .icon-button:hover,
            .theme-toggle:hover,
            .mobile-nav-toggle:hover {
                background: var(--sg-surface-soft);
                border-color: #cbd5e1;
            }

            .icon-button svg,
            .theme-toggle svg,
            .mobile-nav-toggle svg,
            .mobile-sidebar-close svg {
                width: 18px;
                height: 18px;
            }

            .theme-toggle {
                gap: 8px;
                padding: 8px 13px;
                font-size: .86rem;
                font-weight: 700;
            }

            .notification-count {
                position: absolute;
                top: -7px;
                right: -7px;
                min-width: 19px;
                height: 19px;
                border-radius: 999px;
                background: var(--sg-danger);
                color: #fff;
                border: 2px solid var(--sg-surface);
                font-size: .66rem;
                font-weight: 800;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0 5px;
            }

            .notification-menu,
            .user-menu {
                border-color: var(--sg-border);
                background: var(--sg-surface);
                color: var(--sg-text);
                box-shadow: var(--sg-shadow);
                border-radius: 12px;
                overflow: hidden;
            }

            .notification-menu {
                width: min(92vw, 360px);
            }

            .notification-item,
            .user-menu-link {
                display: block;
                padding: 12px 14px;
                color: var(--sg-text);
                text-decoration: none;
                border-top: 1px solid var(--sg-border);
                background: transparent;
                width: 100%;
                text-align: left;
            }

            .notification-item:hover,
            .user-menu-link:hover {
                background: var(--sg-surface-soft);
                color: var(--sg-text);
            }

            .user-trigger {
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                border: 0;
                background: transparent;
                color: var(--sg-text);
                padding: 4px 0 4px 4px;
                max-width: 190px;
            }

            .user-trigger > svg {
                width: 16px;
                height: 16px;
                flex: 0 0 auto;
            }

            .avatar-dot {
                width: 38px;
                height: 38px;
                border-radius: 50%;
                background: var(--sg-navy);
                color: #fff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 800;
                font-size: .82rem;
                flex: 0 0 auto;
            }

            .user-meta {
                min-width: 0;
            }

            .user-name {
                font-size: .86rem;
                font-weight: 800;
                line-height: 1.1;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .user-role {
                color: var(--sg-muted);
                font-size: .74rem;
                line-height: 1.1;
                margin-top: 3px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .admin-content {
                padding: 28px 32px 48px;
            }

            .content-container {
                max-width: 1600px;
                margin: 0 auto;
            }

            .page-title {
                font-size: 1.25rem;
                font-weight: 800;
                margin: 0;
            }

            .admin-content .card,
            .admin-content .dashboard-card,
            .admin-content .stat-card,
            .admin-content .admin-panel,
            .admin-content .user-panel,
            .admin-content .goal-panel,
            .admin-content .goal-filter,
            .admin-content .report-card,
            .admin-content .bg-white {
                background: var(--sg-surface) !important;
                border-color: var(--sg-border) !important;
                color: var(--sg-text);
                box-shadow: var(--sg-shadow);
            }

            .admin-content .text-muted,
            .app-topbar .text-muted {
                color: var(--sg-muted) !important;
            }

            .admin-content .table-responsive {
                border: 1px solid var(--sg-border);
                border-radius: 12px;
                background: var(--sg-surface);
                overflow: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table {
                min-width: 760px;
                margin-bottom: 0;
                border-color: var(--sg-border);
                color: var(--sg-text);
            }

            .admin-content .table > :not(caption) > * > * {
                padding: 14px 16px;
                border-bottom-color: var(--sg-border);
                background: transparent;
            }

            .admin-content .table thead th {
                background: var(--sg-surface-soft);
                color: var(--sg-text);
                font-size: .82rem;
                font-weight: 800;
                white-space: nowrap;
                border-bottom: 1px solid var(--sg-border);
            }

            .admin-content .table tbody tr:hover {
                background: var(--sg-surface-soft);
            }

            .admin-content .badge {
                border-radius: 999px;
                font-weight: 800;
                padding: .42rem .62rem;
            }

            .admin-content .btn,
            .admin-content summary.btn {
                border-radius: 10px;
                font-weight: 800;
                transition: color .16s ease, background-color .16s ease, border-color .16s ease, box-shadow .16s ease;
            }

            .admin-content .btn-primary,
            .admin-content .btn-maroon {
                background: var(--sg-blue) !important;
                border-color: var(--sg-blue) !important;
                color: #fff !important;
            }

            .admin-content .btn-primary:hover,
            .admin-content .btn-maroon:hover,
            .admin-content .btn-primary:focus,
            .admin-content .btn-maroon:focus {
                background: #1d4ed8 !important;
                border-color: #1d4ed8 !important;
                color: #fff !important;
            }

            .admin-content .btn-outline-secondary,
            .admin-content .btn-outline-dark,
            .admin-content .btn-outline-danger,
            .admin-content .btn-outline-success {
                background: var(--sg-surface);
            }

            .admin-content .btn-outline-secondary {
                color: var(--sg-muted);
                border-color: #cbd5e1;
            }

            .admin-content .btn-outline-secondary:hover {
                color: var(--sg-navy) !important;
                background: #eff6ff !important;
                border-color: #bfdbfe !important;
            }

            .admin-content .form-control,
            .admin-content .form-select,
            .admin-content textarea.form-control {
                background-color: var(--sg-field);
                border-color: var(--sg-border);
                color: var(--sg-text);
                border-radius: 10px;
            }

            .admin-content .form-control:focus,
            .admin-content .form-select:focus,
            .admin-content textarea.form-control:focus {
                background-color: var(--sg-field);
                border-color: var(--sg-blue);
                color: var(--sg-text);
                box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .14);
            }

            .progress {
                background-color: #e2e8f0;
                border-radius: 999px;
            }

            .progress-bar {
                background: var(--sg-blue);
                border-radius: 999px;
            }

            .sidebar-backdrop,
            .mobile-nav-toggle,
            .mobile-sidebar-close {
                display: none;
            }

            @media (max-width: 1199.98px) {
                .topbar-search {
                    display: none;
                }

                .topbar-actions {
                    flex: 0 0 auto;
                }
            }

            @media (max-width: 991.98px) {
                .app-shell {
                    display: block;
                }

                .app-sidebar {
                    position: fixed;
                    inset: 0 auto 0 0;
                    width: min(86vw, 312px);
                    height: 100vh;
                    transform: translateX(-105%);
                    transition: transform .22s ease;
                    box-shadow: 24px 0 60px rgba(15, 23, 42, .2);
                }

                .app-main,
                .app-shell.sidebar-collapsed .app-main {
                    margin-left: 0;
                }

                .app-shell.sidebar-collapsed .app-sidebar {
                    width: min(86vw, 312px);
                    padding: 24px 18px;
                }

                .app-shell.sidebar-collapsed .sidebar-brand-row {
                    justify-content: flex-start;
                }

                .app-shell.sidebar-collapsed .brand-copy,
                .app-shell.sidebar-collapsed .sidebar-section,
                .app-shell.sidebar-collapsed .sidebar-link span,
                .app-shell.sidebar-collapsed .sidebar-help .help-copy {
                    display: block;
                }

                .app-shell.sidebar-collapsed .sidebar-link {
                    justify-content: flex-start;
                    padding-inline: 12px;
                }

                .app-shell.sidebar-collapsed .sidebar-link::before {
                    left: 0;
                }

                .app-shell.sidebar-collapsed .sidebar-help {
                    display: block;
                    padding: 14px;
                }

                .sidebar-collapse-toggle {
                    display: none;
                }

                body.sidebar-open .app-sidebar {
                    transform: translateX(0);
                }

                .sidebar-backdrop {
                    display: block;
                    position: fixed;
                    inset: 0;
                    background: rgba(15, 23, 42, .48);
                    z-index: 25;
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
                    margin-left: auto;
                    width: 38px;
                }

                .mobile-nav-toggle {
                    display: inline-flex;
                    width: 42px;
                    flex: 0 0 auto;
                }

                .app-topbar {
                    padding: 14px 16px;
                }

                .topbar-inner {
                    align-items: flex-start;
                    flex-wrap: wrap;
                }

                .topbar-actions {
                    gap: 8px;
                    margin-left: auto;
                }

                .theme-toggle span[data-theme-label],
                .user-trigger .user-meta {
                    display: none;
                }

                .admin-content {
                    padding: 20px 16px 38px;
                }
            }

            @media (max-width: 575.98px) {
                .app-topbar {
                    min-height: auto;
                }

                .topbar-inner {
                    flex-direction: column;
                    gap: 12px;
                }

                .topbar-actions {
                    width: 100%;
                    justify-content: space-between;
                }

                .admin-content {
                    padding: 16px 10px 32px;
                }

                .table {
                    min-width: 680px;
                }
            }
        </style>
    </head>
    <body>
        <x-banner />

        @php
            $companySettings = \App\Models\CompanySetting::current();
            $currentUser = Auth::user();
            $roleLabel = $currentUser->getRoleNames()->first() ?? ucfirst((string) $currentUser->role);
            $initials = collect(explode(' ', trim($currentUser->name)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('');
            $hour = now()->format('H');
            $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            $canManageDepartments = $currentUser->isAdmin() || $currentUser->can('manage departments');
            $canManageGoalPillars = $currentUser->isAdmin() || $currentUser->can('manage goal pillars');
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
            $icon = function (string $name): string {
                return match ($name) {
                    'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h8V3H3v10Zm10 8h8V11h-8v10ZM3 21h8v-6H3v6Zm10-12h8V3h-8v6Z"/></svg>',
                    'target' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="m15 9 5-5m-1 0h-4v4"/></svg>',
                    'plus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v8m-4-4h8"/></svg>',
                    'folder' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>',
                    'report' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-7"/></svg>',
                    'building' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M8 7h4M8 11h4M8 15h4m8 6v-8a2 2 0 0 0-2-2h-2M3 21h18"/></svg>',
                    'sections' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z"/></svg>',
                    'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                    'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>',
                    'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-5"/></svg>',
                    'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.4 15a1.8 1.8 0 0 0 .36 1.98l.05.05a2.1 2.1 0 1 1-2.97 2.97l-.05-.05a1.8 1.8 0 0 0-1.98-.36 1.8 1.8 0 0 0-1.09 1.65V21a2.1 2.1 0 1 1-4.2 0v-.07A1.8 1.8 0 0 0 8.43 19.3a1.8 1.8 0 0 0-1.98.36l-.05.05a2.1 2.1 0 1 1-2.97-2.97l.05-.05A1.8 1.8 0 0 0 3.84 15a1.8 1.8 0 0 0-1.65-1.09H2.1a2.1 2.1 0 1 1 0-4.2h.07A1.8 1.8 0 0 0 3.84 8a1.8 1.8 0 0 0-.36-1.98l-.05-.05A2.1 2.1 0 1 1 6.4 3l.05.05a1.8 1.8 0 0 0 1.98.36 1.8 1.8 0 0 0 1.09-1.65V1.7a2.1 2.1 0 1 1 4.2 0v.07a1.8 1.8 0 0 0 1.09 1.65 1.8 1.8 0 0 0 1.98-.36l.05-.05a2.1 2.1 0 1 1 2.97 2.97l-.05.05A1.8 1.8 0 0 0 19.4 8a1.8 1.8 0 0 0 1.65 1.09h.07a2.1 2.1 0 1 1 0 4.2h-.07A1.8 1.8 0 0 0 19.4 15Z"/></svg>',
                    'search' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
                    'bell' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
                    'sun' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>',
                    'moon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.5 6.5 0 0 0 21 12.8Z"/></svg>',
                    'menu' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h16"/></svg>',
                    'x' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 6 6 18M6 6l12 12"/></svg>',
                    'chevron' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg>',
                    'help' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 1 1 4.9 2.3c-.9.6-1.5 1.1-1.5 2.2v.3M12 17h.01"/></svg>',
                    default => '',
                };
            };
        @endphp

        <div class="sidebar-backdrop" data-sidebar-close></div>

        <div class="app-shell" data-app-shell>
            <aside class="app-sidebar">
                <div class="sidebar-brand-row d-flex align-items-center gap-3">
                    @if ($companySettings->logoUrl())
                        <img class="brand-logo" src="{{ $companySettings->logoUrl() }}" alt="{{ $companySettings->company_name }} logo">
                    @else
                        <div class="brand-mark">{{ $companySettings->brand_mark }}</div>
                    @endif
                    <div class="brand-copy">
                        <div class="brand-title">{{ $companySettings->company_short_name ?: $companySettings->company_name }}</div>
                        <div class="brand-subtitle">{{ $companySettings->product_name }}</div>
                    </div>
                    <button class="sidebar-collapse-toggle" type="button" aria-label="Collapse sidebar" data-sidebar-collapse>{!! $icon('chevron') !!}</button>
                    <button class="mobile-sidebar-close" type="button" aria-label="Close menu" data-sidebar-close>{!! $icon('x') !!}</button>
                </div>

                <div class="sidebar-section">Main</div>
                <nav class="sidebar-nav" aria-label="Main navigation">
                    <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        {!! $icon('dashboard') !!}<span>Dashboard</span>
                    </a>
                    @if ($canManageGoalPillars)
                        <a class="sidebar-link {{ request()->routeIs('goal-pillars.*') ? 'active' : '' }}" href="{{ route('goal-pillars.index') }}">
                            {!! $icon('target') !!}<span>Goal Pillars</span>
                        </a>
                    @endif
                    @if ($currentUser->canManageGoals())
                        <a class="sidebar-link {{ request()->routeIs('goals.create') ? 'active' : '' }}" href="{{ route('goals.create') }}">
                            {!! $icon('plus') !!}<span>Strategic Goals per Pillar</span>
                        </a>
                    @endif
                    <a class="sidebar-link {{ request()->routeIs('goals.*') && ! request()->routeIs('goals.create') ? 'active' : '' }}" href="{{ route('goals.index') }}">
                        {!! $icon('folder') !!}<span>My Goals</span>
                    </a>
                    <a class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.quarterly.index') }}">
                        {!! $icon('report') !!}<span>Reports</span>
                    </a>
                </nav>

                @if ($canManageAdministration || $canManageUsers || $canManageGoalPillars)
                    <div class="sidebar-section">Organization</div>
                    <nav class="sidebar-nav" aria-label="Organization navigation">
                        @if ($canManageDepartments)
                            <a class="sidebar-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">
                                {!! $icon('building') !!}<span>Departments</span>
                            </a>
                        @endif
                        @if ($canManageSections)
                            <a class="sidebar-link {{ request()->routeIs('sections.*') ? 'active' : '' }}" href="{{ route('sections.index') }}">
                                {!! $icon('sections') !!}<span>Sections</span>
                            </a>
                        @endif
                        @if ($canManageUnits)
                            <a class="sidebar-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}">
                                {!! $icon('sections') !!}<span>Units</span>
                            </a>
                        @endif
                        @if ($canManageUsers)
                            <a class="sidebar-link {{ request()->routeIs('users.management.*') || request()->routeIs('users.approvals.*') ? 'active' : '' }}" href="{{ route('users.management.index') }}">
                                {!! $icon('users') !!}<span>Users</span>
                            </a>
                        @endif
                    </nav>
                @endif

                @if ($canManageQuarters || $canManageRoles || $canManageSettings)
                    <div class="sidebar-section">Administration</div>
                    <nav class="sidebar-nav" aria-label="Administration navigation">
                        @if ($canManageQuarters)
                            <a class="sidebar-link {{ request()->routeIs('quarters.*') ? 'active' : '' }}" href="{{ route('quarters.index') }}">
                                {!! $icon('calendar') !!}<span>Quarters / Cycles</span>
                            </a>
                        @endif
                        @if ($canManageRoles)
                            <a class="sidebar-link {{ request()->routeIs('roles.management.*') ? 'active' : '' }}" href="{{ route('roles.management.index') }}">
                                {!! $icon('shield') !!}<span>Roles & Permissions</span>
                            </a>
                        @endif
                        @if ($canManageSettings)
                            <a class="sidebar-link {{ request()->routeIs('settings.company.*') ? 'active' : '' }}" href="{{ route('settings.company.edit') }}">
                                {!! $icon('settings') !!}<span>Company Settings</span>
                            </a>
                        @endif
                    </nav>
                @endif

                <a class="sidebar-help {{ request()->routeIs('help.user-guide') ? 'active' : '' }}" href="{{ route('help.user-guide') }}">
                    <div class="d-flex align-items-center gap-2">
                        {!! $icon('help') !!}
                        <div class="help-copy">
                            <div class="fw-bold small">Need Help?</div>
                            <div class="text-muted small">View guides and support</div>
                        </div>
                    </div>
                </a>
            </aside>

            <div class="app-main">
                <header class="app-topbar">
                    <div class="topbar-inner">
                        <div class="d-flex align-items-start gap-3 min-w-0">
                            <button class="mobile-nav-toggle" type="button" aria-label="Open menu" data-sidebar-open>{!! $icon('menu') !!}</button>
                            <div class="min-w-0">
                                <p class="topbar-greeting">{{ $greeting }}, {{ $currentUser->name }}</p>
                                <p class="topbar-support">Here is what is happening with your goals today.</p>
                            </div>
                        </div>

                        <div class="topbar-actions">
                            <form class="topbar-search" method="get" action="{{ route('goals.index') }}">
                                {!! $icon('search') !!}
                                <input type="search" name="search" placeholder="Search goals, departments..." aria-label="Search goals and departments">
                            </form>

                            @if ($canManageUsers)
                                <div class="dropdown">
                                    <button class="icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Registration notifications">
                                        {!! $icon('bell') !!}
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
                                            <a class="btn btn-sm btn-primary w-100" href="{{ route('users.management.index', ['status' => 'pending']) }}">View Approvals</a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
                                <span data-theme-icon>{!! $icon('sun') !!}</span>
                                <span data-theme-label>Light</span>
                            </button>

                            <div class="dropdown">
                                <button class="user-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="avatar-dot">{{ $initials ?: 'U' }}</span>
                                    <span class="user-meta text-start">
                                        <span class="user-name d-block">{{ $currentUser->name }}</span>
                                        <span class="user-role d-block">{{ $roleLabel }}</span>
                                    </span>
                                    {!! $icon('chevron') !!}
                                </button>
                                <div class="dropdown-menu dropdown-menu-end user-menu">
                                    <div class="px-3 py-3">
                                        <div class="fw-bold">{{ $currentUser->name }}</div>
                                        <div class="text-muted small">{{ $currentUser->email }}</div>
                                    </div>
                                    <a class="user-menu-link" href="{{ route('profile.show') }}">Profile</a>
                                    <a class="user-menu-link" href="{{ route('profile.show') }}">Account Settings</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="user-menu-link" type="submit">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="admin-content">
                    <div class="content-container">
                        @if (isset($header))
                            <div class="mb-4">
                                {{ $header }}
                            </div>
                        @endif

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
            const sidebarKey = 'smart-goals-sidebar';
            const root = document.documentElement;
            const appShell = document.querySelector('[data-app-shell]');
            const sunIcon = `{!! $icon('sun') !!}`;
            const moonIcon = `{!! $icon('moon') !!}`;

            function applyTheme(theme) {
                const nextTheme = theme === 'dark' ? 'dark' : 'light';
                root.dataset.theme = nextTheme;
                localStorage.setItem(themeKey, nextTheme);

                document.querySelectorAll('[data-theme-label]').forEach((label) => {
                    label.textContent = nextTheme === 'dark' ? 'Dark' : 'Light';
                });

                document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
                    icon.innerHTML = nextTheme === 'dark' ? moonIcon : sunIcon;
                });
            }

            applyTheme(localStorage.getItem(themeKey) || 'light');

            function applySidebarState(state) {
                const collapsed = state === 'collapsed';
                appShell?.classList.toggle('sidebar-collapsed', collapsed);

                document.querySelectorAll('[data-sidebar-collapse]').forEach((button) => {
                    button.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
                    button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                });
            }

            applySidebarState(localStorage.getItem(sidebarKey) || 'expanded');

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

            document.querySelectorAll('[data-sidebar-collapse]').forEach((button) => {
                button.addEventListener('click', () => {
                    const nextState = appShell?.classList.contains('sidebar-collapsed') ? 'expanded' : 'collapsed';
                    localStorage.setItem(sidebarKey, nextState);
                    applySidebarState(nextState);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    document.body.classList.remove('sidebar-open');
                }
            });
        </script>
    </body>
</html>
