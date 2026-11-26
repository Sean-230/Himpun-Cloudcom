@props(['name'])
@php
    $paths = [
        'home' => 'M3 11l9-8 9 8v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1z',
        'flag' => 'M5 21V4m0 0h11l-2 4 2 4H5',
        'briefcase' => 'M4 7h16v13H4z M9 7V4h6v3',
        'users' => 'M16 20v-1a4 4 0 00-4-4H8a4 4 0 00-4 4v1 M10 11a3 3 0 100-6 3 3 0 000 6 M20 20v-1a3 3 0 00-2-2.8 M16 5.3a3 3 0 010 5.4',
        'grid' => 'M4 4h7v7H4z M13 4h7v7h-7z M4 13h7v7H4z M13 13h7v7h-7z',
        'check' => 'M9 12l2 2 4-4 M12 21a9 9 0 100-18 9 9 0 000 18',
        'wallet' => 'M3 7h16a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2z M3 7l2-3h12 M16 14h2',
        'megaphone' => 'M4 10v4h3l7 4V6L7 10z M18 9a4 4 0 010 6',
        'sparkle' => 'M12 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2z',
        'file' => 'M7 3h7l5 5v13H7z M14 3v5h5',
        'sliders' => 'M4 7h10 M18 7h2 M4 17h2 M10 17h10 M14 5v4 M6 15v4',
        'search' => 'M11 18a7 7 0 100-14 7 7 0 000 14 M20 20l-4-4',
        'bell' => 'M6 16v-5a6 6 0 1112 0v5l2 2H4z M10 20h4',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'size-4 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] }}"/></svg>
