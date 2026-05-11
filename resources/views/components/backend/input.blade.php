@if ($label)
    <label for="{{ $id }}" class="col-form-label">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>
@endif
<input type="{{ $type }}" {{ $attributes->merge(['class' => 'form-control']) }} id="{{ $id }}" name="{{ $id }}" value="{{ $value }}" {{ $attributes }}>