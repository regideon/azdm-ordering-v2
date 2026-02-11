@php
    $tone = $getTone();

    $toneClass = match ($tone) {
        'primary' => 'text-primary-700 bg-primary-50 ring-primary-200',
        'success' => 'text-success-700 bg-success-50 ring-success-200',
        'warning' => 'text-warning-700 bg-warning-50 ring-warning-200',
        'danger'  => 'text-danger-700 bg-danger-50 ring-danger-200',
        default   => 'text-gray-700 bg-gray-50 ring-gray-200',
    };

    $text = $getText();
@endphp

<div class="rounded-lg px-3 py-2 text-sm ring-1 {{ $toneClass }}">
    @if ($isHtml())
        {!! $text !!}
    @else
        {{ $text }}
    @endif
</div>
