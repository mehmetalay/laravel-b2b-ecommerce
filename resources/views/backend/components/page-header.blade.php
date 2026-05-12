@props([
    'title' => '',
])

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $title }}</h4>
    <div>
        {{ $slot }}
    </div>
</div>
