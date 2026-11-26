@props(['current' => 0])
@php $steps = ['Anggota', 'Koordinator', 'HOD/PJ', 'Ketupel']; @endphp
{{-- Where an approval is stuck: done steps in ink, current underlined, future grey. --}}
<ol class="flex items-center text-[13px]" aria-label="Rantai persetujuan">
    @foreach ($steps as $i => $step)
        <li @class([
            'pb-0.5',
            'text-ink' => $i < $current,
            'border-b-2 border-accent font-medium text-ink' => $i === $current,
            'border-b-2 border-transparent text-muted' => $i > $current,
        ]) @if ($i === $current) aria-current="step" @endif>{{ $step }}</li>
        @unless ($loop->last)
            <li class="mx-1.5 h-px w-3 bg-line" aria-hidden="true"></li>
        @endunless
    @endforeach
</ol>
