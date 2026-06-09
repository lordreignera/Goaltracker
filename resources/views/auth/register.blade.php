<x-guest-layout>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --arm-maroon: #8f171b;
            --arm-maroon-dark: #681014;
            --arm-field: #eaf1fb;
            --arm-line: #dfe4ea;
        }

        body {
            background: linear-gradient(180deg, var(--arm-maroon) 0 10px, transparent 10px), #f5f5f4;
        }

        .register-page {
            min-height: 100vh;
            padding: 32px 16px;
        }

        .register-card {
            width: min(100%, 920px);
            border-radius: 18px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 22px 60px rgba(30, 20, 20, .12);
        }

        .brand-panel {
            background: linear-gradient(150deg, var(--arm-maroon-dark), var(--arm-maroon));
            color: #fff;
            padding: 34px 42px;
        }

        .brand-mark {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            background: #fff;
            color: var(--arm-maroon);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
        }

        .brand-copy {
            max-width: 690px;
        }

        .form-panel {
            padding: 42px;
        }

        .form-control,
        .form-select {
            min-height: 52px;
            border-radius: 9px;
            background-color: var(--arm-field);
            border-color: #d2d9e2;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--arm-maroon);
            box-shadow: 0 0 0 .2rem rgba(143, 23, 27, .14);
            background-color: var(--arm-field);
        }

        .btn-maroon {
            min-height: 56px;
            border-radius: 9px;
            background: linear-gradient(90deg, var(--arm-maroon-dark), var(--arm-maroon));
            color: #fff;
            border: 0;
            font-weight: 800;
        }

        .btn-maroon:hover {
            color: #fff;
            background: linear-gradient(90deg, #530c10, #7d1317);
        }

        .text-maroon {
            color: var(--arm-maroon);
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap .form-control {
            padding-right: 62px;
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

        @media (max-width: 991.98px) {
            .brand-panel,
            .form-panel {
                padding: 32px 24px;
            }
        }

        @media (max-width: 575.98px) {
            .register-page {
                padding: 18px 10px;
                align-items: flex-start !important;
            }

            .register-card {
                border-radius: 14px;
            }

            .brand-panel,
            .form-panel {
                padding: 24px 18px;
            }

            .brand-mark {
                width: 50px;
                height: 50px;
            }

            .brand-panel h1 {
                font-size: 2rem;
            }

            .form-control,
            .form-select {
                min-height: 48px;
            }

            .btn-maroon {
                min-height: 52px;
            }
        }
    </style>

    <div class="register-page d-flex align-items-center justify-content-center">
        <div class="register-card">
            <section class="brand-panel">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-4">
                    <div class="brand-mark">ARM</div>
                    <div class="brand-copy">
                        <h1 class="fw-bold mb-2">Request Staff Access</h1>
                        <p class="mb-2">Create your staff account request for the 90-Day SMART Goals Accountability Tracker.</p>
                        <p class="mb-0 opacity-75">A Super Admin will review and approve your account before you can sign in.</p>
                    </div>
                </div>
            </section>

            <section class="form-panel">
                <div class="mb-4">
                    <h2 class="fw-bold mb-2">Join as a Staff Member</h2>
                    <p class="text-secondary mb-0">Enter your details exactly as they should appear in reports.</p>
                </div>

                <x-validation-errors class="alert alert-danger mb-4" />

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label fw-semibold">First Name</label>
                            <input id="first_name" class="form-control" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus autocomplete="given-name">
                        </div>
                        <div class="col-md-6">
                            <label for="second_name" class="form-label fw-semibold">Second Name</label>
                            <input id="second_name" class="form-control" type="text" name="second_name" value="{{ old('second_name') }}" required autocomplete="family-name">
                        </div>
                        <div class="col-md-6">
                            <label for="phone_number" class="form-label fw-semibold">Phone Number</label>
                            <input id="phone_number" class="form-control" type="text" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                        </div>
                        <div class="col-md-6">
                            <label for="department_id" class="form-label fw-semibold">Department</label>
                            <select id="department_id" class="form-select" name="department_id" required>
                                <option value="">Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @if ($departments->isEmpty())
                                <small class="text-danger">No departments have been seeded yet.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="unit_id" class="form-label fw-semibold">Unit</label>
                            <select id="unit_id" class="form-select" name="unit_id">
                                <option value="">Select department first</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="requested_role" class="form-label fw-semibold">Requested Role</label>
                            <select id="requested_role" class="form-select" name="requested_role" required>
                                <option value="">Select requested role</option>
                                <option value="Staff" @selected(old('requested_role') === 'Staff')>Staff</option>
                                <option value="Supervisor" @selected(old('requested_role') === 'Supervisor')>Supervisor</option>
                                <option value="Manager" @selected(old('requested_role') === 'Manager')>Manager</option>
                            </select>
                            <small class="text-secondary">A Super Admin must approve this role before access is granted.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="password-wrap">
                                <input id="password" class="form-control" type="password" name="password" required autocomplete="new-password">
                                <button class="password-toggle" type="button" data-password-toggle="password">Show</button>
                            </div>
                            <small class="text-secondary">Use 8+ characters with uppercase, lowercase, a number, and a symbol.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                            <div class="password-wrap">
                                <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password">
                                <button class="password-toggle" type="button" data-password-toggle="password_confirmation">Show</button>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-maroon w-100 mt-4" type="submit">Submit Account Request</button>

                    <p class="text-center text-secondary small mt-3 mb-0">
                        Already approved? <a class="text-maroon fw-semibold text-decoration-none" href="{{ route('login') }}">Login here</a>
                    </p>
                </form>
            </section>
        </div>
    </div>

    @php
        $departmentUnits = $departments->mapWithKeys(fn ($department) => [
            $department->id => $department->units->map(fn ($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
            ])->values(),
        ]);
    @endphp

    <script>
        const departmentUnits = @json($departmentUnits);
        const oldDepartmentId = @json((string) old('department_id'));
        const oldUnitId = @json((string) old('unit_id'));
        const departmentSelect = document.getElementById('department_id');
        const unitSelect = document.getElementById('unit_id');

        function refreshUnits() {
            const selectedDepartment = departmentSelect.value;
            const units = departmentUnits[selectedDepartment] || [];

            unitSelect.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = selectedDepartment ? 'Select unit' : 'Select department first';
            unitSelect.appendChild(placeholder);

            units.forEach((unit) => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = unit.name;

                if (String(unit.id) === oldUnitId) {
                    option.selected = true;
                }

                unitSelect.appendChild(option);
            });
        }

        if (oldDepartmentId) {
            departmentSelect.value = oldDepartmentId;
        }

        departmentSelect.addEventListener('change', refreshUnits);
        refreshUnits();

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
