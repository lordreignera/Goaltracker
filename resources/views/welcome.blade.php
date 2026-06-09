<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>90-Day SMART Goals Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f8fb; color: #19231f; }
        .hero { min-height: 92vh; display: flex; align-items: center; }
        .brand-mark { width: 56px; height: 56px; border-radius: 8px; background: #f4b942; color: #113d2c; }
        .panel { border: 1px solid #e5ebf1; border-radius: 8px; background: #fff; }
        .text-arm { color: #113d2c; }
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
                <p class="lead text-muted mt-3">Plan measurable goals, break them into weighted objectives, submit weekly updates, collect supervisor feedback, and track performance from staff level to organization level.</p>
                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg">Open Dashboard</a>
                    <a href="{{ route('goals.index') }}" class="btn btn-outline-secondary btn-lg">View Goals</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="panel p-4">
                    <div class="d-flex justify-content-between mb-2"><span>Department Score</span><strong>70%</strong></div>
                    <div class="progress mb-4"><div class="progress-bar bg-success" style="width: 70%"></div></div>
                    <div class="d-flex justify-content-between mb-2"><span>Approved Objectives</span><strong>60%</strong></div>
                    <div class="progress mb-4"><div class="progress-bar bg-warning" style="width: 60%"></div></div>
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
