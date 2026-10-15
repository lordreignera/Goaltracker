@props([
    'title',
    'value',
    'support',
    'compareClass' => 'text-success',
    'iconName',
    'iconClass',
])

<article class="summary-card">
    <div class="d-flex justify-content-between gap-3">
        <div>
            <div class="summary-title">{{ $title }}</div>
            <div class="summary-value">{{ $value }}</div>
        </div>
        <span class="summary-icon {{ $iconClass }}">{!! $icon($iconName) !!}</span>
    </div>
    <div class="summary-support mt-3">{{ $support }}</div>
    <div class="summary-compare mt-3"><strong class="{{ $compareClass }}">0%</strong> vs last quarter</div>
</article>
