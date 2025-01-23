@props([
    'route' => null,
    'href' => null,
    'icon' => null,
    'title',
    'active' => null
])

@php
    $classes = 'nav-link';
    
    if ($route) {
        $href = route($route);
        $active = request()->routeIs($route);
    }
    
    if ($active) {
        $classes .= ' active';
    }
@endphp

<li class="nav-item">
    <a href="{{ $href }}" class="{{ $classes }}">
        @if($icon)
            <i class="menu-icon {{ $icon }}"></i>
        @endif
        <span class="menu-title">{{ $title }}</span>
    </a>
</li>
