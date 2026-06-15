<x-guest-layout>
    @php($companySettings = \App\Models\CompanySetting::current())
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --arm-maroon: #c43b3f;
            --arm-maroon-dark: #a12d31;
            --arm-maroon-soft: #e66b6f;
            --arm-gold: #f3b23a;
            --arm-ink: #20242a;
            --arm-muted: #66707e;
            --arm-field: #eef3fb;
            --arm-line: #dfe5ec;
            --arm-page: #f6f4f2;
        }

        body {
            background:
                linear-gradient(180deg, var(--arm-maroon) 0 10px, transparent 10px),
                radial-gradient(circle at 15% 20%, rgba(196, 59, 63, .08), transparent 28%),
                var(--arm-page);
        }

        .login-page {
            min-height: 100vh;
            padding: 32px 16px;
            color: var(--arm-ink);
        }

        .login-frame {
            width: min(100%, 940px);
            min-height: 660px;
            overflow: hidden;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 22px 60px rgba(30, 20, 20, .13);
        }

        .brand-panel {
            background: linear-gradient(155deg, rgba(196,59,63,0.94), rgba(196,59,63,0.9));
            color: #fff;
            padding: 30px;
            position: relative;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent, rgba(0, 0, 0, .16));
        }

        .brand-inner {
            position: relative;
            z-index: 2;
            width: 100%;
        }

        .brand-mark {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            background: var(--arm-gold);
            color: #20312b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 500;
            flex: 0 0 auto;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            background: #fff;
            object-fit: contain;
            padding: 7px;
            flex: 0 0 auto;
        }

        .brand-title {
            max-width: 100%;
        }

        .brand-heading {
            font-size: clamp(2.15rem, 4vw, 2.95rem);
            line-height: 1.12;
            letter-spacing: 0;
        }

        .brand-copy {
            font-size: 1.16rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, .84);
        }

        .metric-tile {
            height: 126px;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 10px;
            background: rgba(255, 255, 255, .1);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }

        .metric-value {
            min-height: 38px;
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
            white-space: nowrap;
        }

        .metric-label {
            color: rgba(255, 255, 255, .76);
            font-size: .82rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .metric-value.word {
            font-size: 1.55rem;
        }

        .form-panel {
            padding: 44px 46px;
            background: linear-gradient(180deg, #fff, #fbfcfd);
        }

        .login-form {
            width: min(100%, 390px);
        }

        .login-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #ecd3d5;
            background: #fff6f6;
            color: var(--arm-maroon);
            border-radius: 999px;
            padding: 7px 12px;
            font-size: .82rem;
            font-weight: 700;
        }

        .form-label {
            color: #111827;
        }

        .form-control {
            height: 56px;
            border-radius: 9px;
            background: var(--arm-field);
            border: 1px solid #d2d9e2;
            padding-inline: 17px;
        }

        .form-control:focus {
            border-color: var(--arm-maroon);
            box-shadow: 0 0 0 .18rem rgba(196, 59, 63, .12);
            background: var(--arm-field);
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap .form-control {
            padding-right: 48px;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--arm-maroon);
            font-size: .82rem;
            font-weight: 800;
            padding: 6px 8px;
        }

        .form-check-input:checked {
            background-color: var(--arm-maroon);
            border-color: var(--arm-maroon);
        }

        .btn-maroon {
            height: 60px;
            border-radius: 9px;
            background: linear-gradient(90deg, var(--arm-maroon-dark), var(--arm-maroon));
            border: 0;
            color: #fff;
            font-weight: 800;
            box-shadow: 0 10px 20px rgba(196, 59, 63, .14);
        }

        .btn-maroon:hover,
        .btn-maroon:focus {
            color: #fff;
            background: linear-gradient(90deg, #8a1f23, #b11e22);
        }

        .divider {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 12px;
            color: var(--arm-muted);
            font-size: .9rem;
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            background: var(--arm-line);
        }

        .btn-outline-maroon {
            min-height: 52px;
            border-radius: 9px;
            border: 2px solid var(--arm-maroon);
            color: var(--arm-maroon);
            font-weight: 800;
            background: #fff;
        }

        .btn-outline-maroon:hover {
            background: var(--arm-maroon);
            color: #fff;
        }

        .text-maroon {
            color: var(--arm-maroon);
        }

        @media (max-width: 991.98px) {
            .login-frame {
                min-height: auto;
            }

            .brand-panel,
            .form-panel {
                padding: 34px 24px;
            }
        }

        @media (max-width: 575.98px) {
            .login-page {
                padding: 18px 10px;
                align-items: flex-start !important;
            }

            .login-frame {
                border-radius: 14px;
            }

            .brand-panel,
            .form-panel {
                padding: 24px 18px;
            }

            .brand-mark {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .brand-logo {
                width: 60px;
                height: 60px;
            }

            .brand-panel .fs-3 {
                font-size: 1.25rem !important;
            }

            .brand-panel .fs-5 {
                font-size: 1rem !important;
            }

            .brand-heading {
                font-size: 1.85rem;
            }

            .brand-copy {
                font-size: 1rem;
                line-height: 1.55;
            }

            .metric-tile {
                height: 96px;
            }

            .metric-value {
                font-size: 1.55rem;
            }

            .metric-value.word {
                font-size: 1.25rem;
            }

            .form-control {
                height: 50px;
            }

            .btn-maroon {
                height: 54px;
            }
        }
    </style>

    <div class="login-page d-flex align-items-center justify-content-center">
        <div class="login-frame row g-0">
            <section class="brand-panel col-lg-6 d-flex align-items-center">
                <div class="brand-inner">
                    <div class="d-flex align-items-center gap-3 mb-5">
                        @if ($companySettings->logoUrl())
                            <img class="brand-logo" src="{{ $companySettings->logoUrl() }}" alt="{{ $companySettings->company_name }} logo">
                        @else
                            <div class="brand-mark">{{ $companySettings->brand_mark }}</div>
                        @endif
                        <div class="lh-sm">
                            <div class="fw-bold fs-3">{{ $companySettings->company_name }}</div>
                            <div class="fs-5 opacity-75 mt-2">{{ $companySettings->product_name }}</div>
                        </div>
                    </div>

                    <div class="brand-title">
                        <h1 class="brand-heading fw-bold mb-4">Track the work that moves the mission.</h1>
                        <p class="brand-copy mb-0">
                            {{ $companySettings->tagline }}
                        </p>
                    </div>

                    <div class="row g-3 mt-5">
                        <div class="col-md-4">
                            <div class="metric-tile p-3">
                                <div class="metric-value">90</div>
                                <div class="metric-label mt-3">Day cycles</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-tile p-3">
                                <div class="metric-value">100%</div>
                                <div class="metric-label mt-3">Objective weights</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-tile p-3">
                                <div class="metric-value word">Weekly</div>
                                <div class="metric-label mt-3">Reviews</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="form-panel col-lg-6 d-flex align-items-center justify-content-center">
                <div class="login-form">
                    <div class="text-center mb-5">
                        <div class="login-kicker mb-3">Secure staff login</div>
                        <h2 class="fw-bold mb-2">Welcome Back</h2>
                        <p class="text-secondary mb-0">Use your staff account to continue to the goals dashboard.</p>
                    </div>

                    <x-validation-errors class="alert alert-danger mb-4" />

                    @session('status')
                        <div class="alert alert-success mb-4">
                            {{ $value }}
                        </div>
                    @endsession

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Enter email address">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="password-wrap">
                                <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                                <button class="password-toggle" type="button" data-password-toggle="password">Show</button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                            <label class="form-check d-flex align-items-center gap-2 mb-0">
                                <input id="remember_me" class="form-check-input mt-0" type="checkbox" name="remember">
                                <span class="form-check-label text-secondary">Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-decoration-none text-maroon fw-semibold small" href="{{ route('password.request') }}">
                                    Forgot Password?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-maroon w-100">
                            Login
                        </button>
                    </form>

                    <div class="divider my-4">New to the platform?</div>

                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-outline-maroon w-100 d-flex align-items-center justify-content-center">
                        Join as a Staff Member
                    </a>

                    <p class="text-center text-secondary small mt-3 mb-0">
                        Enter the email address assigned to your account.
                    </p>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                const isHidden = input.type === 'password';

                input.type = isHidden ? 'text' : 'password';
                button.textContent = isHidden ? 'Hide' : 'Show';
            });
        });
    </script>
</x-guest-layout>
