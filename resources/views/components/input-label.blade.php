@props(['value'])

<label {{ $attributes->merge(['class' => 'control-label']) }}>
    {{ $value ?? $slot }}
</label><i class="bar"></i>
