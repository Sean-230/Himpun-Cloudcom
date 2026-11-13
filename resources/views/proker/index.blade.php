@extends('layouts.app')
@section('title', 'Proker berjalan — Himpun')

@php
    // $prokers: only the ones with status "Berjalan" (see the route).
    $rp = fn (int $n) => 'Rp '.number_format($n, 0, ',', '.');
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $tgl = function (string $d) use ($bulan) {
        $c = \Carbon\Carbon::parse($d);

        return $c->day.' '.$bulan[$c->month].' '.$c->year;
    };
    $today = now()->startOfDay();
@endphp

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-[22px] font-semibold leading-tight">Proker</h1>
        <p class="mt-0.5 text-muted">Yang sedang berjalan · {{ count($prokers) }} proker</p>
    </div>
    <a href="{{ route('proker.create') }}" class="inline-flex h-9 items-center rounded-[10px] bg-accent px-4 font-medium text-white">Buat proker</a>
</div>

@if (count($prokers) === 0)
    <p class="mt-6 rounded-xl border border-line p-5 text-muted">Belum ada proker yang sedang berjalan. Buat proker baru atau lihat semua proker di <a href="{{ route('dashboard') }}" class="font-medium text-accent hover:underline">Ringkasan</a>.</p>
@else
    <div class="mt-6 overflow-x-auto rounded-xl border border-line">
        <table class="w-full min-w-[760px] text-left">
            <thead class="text-[13px] text-muted">
                <tr>
                    <th class="px-5 py-3 font-normal">Proker</th>
                    <th class="py-3 pr-4 font-normal">Hari-H</th>
                    <th class="py-3 pr-4 font-normal">Jobdesc</th>
                    <th class="py-3 pr-4 font-normal">Panitia</th>
                    <th class="px-5 py-3 text-right font-normal">Anggaran terpakai</th>
                </tr>
            </thead>
            @foreach ($prokers as $p)
                @php
                    $jobdesc = collect($p['divisions'])->flatMap(fn ($d) => $d['jobdesc']);
                    $done = $jobdesc->where('done', true)->count();
                    $overdue = $jobdesc->filter(fn ($j) => ! $j['done'] && \Carbon\Carbon::parse($p['hariH'])->subDays($j['h'])->lt($today))->count();
                    $koor = collect($p['divisions'])->sum(fn ($d) => count($d['koor']));
                    $anggota = collect($p['divisions'])->sum(fn ($d) => count($d['anggota']));
                    $left = (int) $today->diffInDays(\Carbon\Carbon::parse($p['hariH']), false);
                    $pct = $p['realisasi'] ? round($p['realisasi'] / $p['rencana'] * 100) : null;
                @endphp
                <tbody class="border-t border-line align-top">
                    <tr>
                        <td class="px-5 py-4">
                            <a href="{{ route('proker.show', $p['id']) }}" class="font-medium hover:underline">{{ $p['nama'] }}</a>
                            <p class="text-[13px] text-muted">{{ $p['ketupel'] ? 'Ketupel: '.$p['ketupel'] : 'Ketupel belum ditunjuk' }} · PJ: {{ $p['pj'] }}</p>
                        </td>
                        <td class="whitespace-nowrap py-4 pr-4 tabular-nums">
                            {{ $tgl($p['hariH']) }}
                            <p class="text-[13px] text-muted">{{ $left > 0 ? $left.' hari lagi' : ($left === 0 ? 'hari ini' : 'sudah lewat') }}</p>
                        </td>
                        <td class="whitespace-nowrap py-4 pr-4 tabular-nums">
                            {{ $done }} dari {{ $jobdesc->count() }} selesai
                            <p class="text-[13px] {{ $overdue ? 'text-bad' : 'text-muted' }}">{{ $overdue ? $overdue.' lewat tenggat' : 'tidak ada yang terlambat' }}</p>
                        </td>
                        <td class="py-4 pr-4">
                            {{ count($p['divisions']) }} divisi
                            <p class="text-[13px] tabular-nums text-muted">{{ $koor }} koordinator · {{ $anggota }} anggota</p>
                        </td>
                        <td class="px-5 py-4 text-right tabular-nums">
                            @if ($pct !== null)
                                {{ $pct }}%
                                <p class="text-[13px] text-muted">{{ $rp($p['realisasi']) }}</p>
                            @else
                                <span class="text-muted">–</span>
                                <p class="text-[13px] text-muted">dari {{ $rp($p['rencana']) }}</p>
                            @endif
                        </td>
                    </tr>
                </tbody>
            @endforeach
        </table>
    </div>
    <p class="mt-3 text-[13px] text-muted">Proker yang masih usulan, dalam pembentukan panitia, LPJ, atau sudah selesai ada di <a href="{{ route('dashboard') }}" class="font-medium text-accent hover:underline">Ringkasan</a>.</p>
@endif
@endsection
