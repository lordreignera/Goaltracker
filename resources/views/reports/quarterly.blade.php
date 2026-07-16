<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Quarterly Report</h1>
    </x-slot>

    <style>
        .report-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            padding: 18px;
            box-shadow: 0 10px 28px rgba(20, 24, 31, .04);
        }

        .chart-row {
            display: grid;
            grid-template-columns: minmax(150px, 220px) 1fr 54px;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .chart-track {
            height: 14px;
            border-radius: 999px;
            background: #f0f2f5;
            overflow: hidden;
        }

        .chart-fill {
            height: 100%;
            border-radius: 999px;
            background: var(--arm-maroon);
        }

        @media (max-width: 575.98px) {
            .chart-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }
        }
    </style>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
        <form method="get" action="{{ route('reports.quarterly.index') }}" class="d-flex flex-column flex-sm-row gap-2">
            <select class="form-select" name="quarter_id">
                @foreach ($quarters as $quarterOption)
                    <option value="{{ $quarterOption->id }}" @selected($selectedQuarter->is($quarterOption))>
                        {{ $quarterOption->name }} ({{ $quarterOption->starts_at->format('M d, Y') }} - {{ $quarterOption->ends_at->format('M d, Y') }})
                    </option>
                @endforeach
            </select>
            <button class="btn btn-maroon">View</button>
        </form>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('reports.quarterly.csv', ['quarter_id' => $selectedQuarter->id]) }}">Download Table CSV</a>
            <a class="btn btn-outline-secondary" href="{{ route('reports.quarterly.pdf', ['quarter_id' => $selectedQuarter->id]) }}">Download PDF</a>
        </div>
    </div>

    @include('reports.partials.quarterly-content', ['isPdf' => false])
</x-app-layout>
