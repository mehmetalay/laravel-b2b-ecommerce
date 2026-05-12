@if ($label)
    <label for="{{ $name }}" class="col-form-label">{{ $label }}</label>
@endif

<textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}" {{ $attributes->merge(['class' => 'form-control']) }}>{{ old($name, $value) }}</textarea>
