<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>90-Day SMART Goals Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; color: #0f172a; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .hero { min-height: 92vh; display: flex; align-items: center; }
        .brand-mark { width: 56px; height: 56px; border-radius: 12px; background: #dbeafe; color: #172554; }
        .panel { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 14px 36px rgba(15, 23, 42, .06); }
        .text-arm { color: #172554; }
        .btn-primary { background: #2563eb; border-color: #2563eb; }
        .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
        .progress-bar { background-color: #2563eb; }
    </style>
</head>
<body>
<main class="hero">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="brand-mark d-flex align-items-center justify-content-center fw-bold fs-4">90</div>
                    <div>
                        <div class="fw-semibold">Africa Renewal Ministries</div>
                        <div class="text-muted">SMART Goals Accountability Tracker</div>
                    </div>
                </div>
                <h1 class="display-5 fw-bold text-arm">90-Day SMART Goals Accountability Tracker</h1>
                <p class="lead text-muted mt-3">Plan measurable goals, break them into weighted objectives, submit progress reports, collect supervisor feedback, and track performance from staff level to organization level.</p>
                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Open Dashboard</a>
                    <a href="{{ route('goals.index') }}" class="btn btn-outline-secondary btn-lg">View Goals</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="panel p-4">
                    <div class="d-flex justify-content-between mb-2"><span>Department Score</span><strong>70%</strong></div>
                    <div class="progress mb-4"><div class="progress-bar" style="width: 70%"></div></div>
                    <div class="d-flex justify-content-between mb-2"><span>Approved Objectives</span><strong>60%</strong></div>
                    <div class="progress mb-4"><div class="progress-bar" style="width: 60%; background-color: #10b981;"></div></div>
                    <div class="d-flex justify-content-between mb-2"><span>Pending Reviews</span><strong>12</strong></div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0">Improve ICT Service Delivery</div>
                        <div class="list-group-item px-0">Upgrade Staff Computers</div>
                        <div class="list-group-item px-0">Reduce Helpdesk Response Time</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
