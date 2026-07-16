<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Company Settings</h1>
    </x-slot>

    <style>
        .settings-panel {
            border: 1px solid #e6e9ef;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(20, 24, 31, .04);
        }

        .settings-preview {
            border: 1px solid #e6e9ef;
            border-radius: 12px;
            background: #fbfcfd;
        }

        .settings-logo {
            width: 52px;
            height: 52px;
            border-radius: 10px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #e6e9ef;
            padding: 5px;
        }

        .btn-maroon {
            background: var(--arm-maroon);
            border-color: var(--arm-maroon);
            color: #fff;
            font-weight: 800;
        }

        .btn-maroon:hover {
            background: var(--arm-maroon-dark);
            border-color: var(--arm-maroon-dark);
            color: #fff;
        }
    </style>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="settings-panel p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 mb-4">
            <div>
                <h2 class="h5 fw-bold mb-1">Brand Details</h2>
                <div class="text-muted small">These details control the company name shown in the sidebar, login screen, and reports.</div>
            </div>
            <div class="settings-preview p-3 d-flex align-items-center gap-3">
                @if ($settings->logoUrl())
                    <img class="settings-logo" src="{{ $settings->logoUrl() }}" alt="{{ $settings->company_name }} logo">
                @else
                    <div class="brand-mark">{{ $settings->brand_mark }}</div>
                @endif
                <div>
                    <div class="fw-bold">{{ $settings->company_short_name }}</div>
                    <div class="text-muted small">{{ $settings->product_name }}</div>
                </div>
            </div>
        </div>

        <form method="post" action="{{ route('settings.company.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Company Name</label>
                    <input class="form-control" name="company_name" value="{{ old('company_name', $settings->company_name) }}" required>
                    @error('company_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Short Display Name</label>
                    <input class="form-control" name="company_short_name" value="{{ old('company_short_name', $settings->company_short_name) }}" placeholder="Used in the sidebar">
                    @error('company_short_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Brand Mark</label>
                    <input class="form-control" name="brand_mark" value="{{ old('brand_mark', $settings->brand_mark) }}" maxlength="12" required>
                    <small class="text-muted">Used when no logo is uploaded. Example: 90, AC, HR</small>
                    @error('brand_mark') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Company Logo</label>
                    <input class="form-control" type="file" name="logo" accept="image/*">
                    <small class="text-muted">PNG, JPG, GIF, or WebP up to 2MB.</small>
                    @error('logo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Product Name</label>
                    <input class="form-control" name="product_name" value="{{ old('product_name', $settings->product_name) }}" required>
                    @error('product_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tagline</label>
                    <input class="form-control" name="tagline" value="{{ old('tagline', $settings->tagline) }}">
                    @error('tagline') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email', $settings->email) }}">
                    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone</label>
                    <input class="form-control" name="phone" value="{{ old('phone', $settings->phone) }}">
                    @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Website</label>
                    <input class="form-control" name="website" value="{{ old('website', $settings->website) }}">
                    @error('website') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Address</label>
                    <input class="form-control" name="address" value="{{ old('address', $settings->address) }}">
                    @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-maroon">Save Company Settings</button>
            </div>
        </form>
    </div>
</x-app-layout>
