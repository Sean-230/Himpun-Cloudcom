@props(['name'])
@php
    // Stable pale tint per person: identity, not data.
    $hue = crc32($name) % 360;
    $initials = collect(explode(' ', $name))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
@endphp
<span title="{{ $name }}" style="background: hsl({{ $hue }} 55% 92%); color: hsl({{ $hue }} 40% 28%)"
      class="inline-flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-medium">{{ $initials }}</span>
