@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => ' text-danger fw-bold ']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
