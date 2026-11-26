@extends('layouts.app')
@section('title', 'Proker — Himpun')

@php
    use App\Support\Access;
    use App\Support\DummyData;

    // $prokers is already filtered for this role (see Access::listFor); $me is the acting person.
    $own = in_array($me['role'], ['koor', 'anggota'], true); // koordinator and anggota see their own prokers, in a lighter table
    $rp = fn (int $n) => 'Rp '.number_format($n, 0, ',', '.');
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $tgl = function (string $d) use ($bulan) {
        $c = \Carbon\Carbon::parse($d);

        return $c->day.' '.$bulan[$c->month].' '.$c->year;
    };
    $left = fn (int $n) => $n > 0 ? $n.' hari lagi' : ($n === 0 ? 'hari ini' : 'sudah lewat');
    $pill = [
        'Berjalan' => 'bg-[#e6f4ec] text-good',
        'Pembentukan panitia' => 'bg-[#fbf0dc] text-warn',
        'LPJ' => 'bg-[#e8f0f7] text-actual',
        'Usulan' => 'bg-black/5 text-muted',
        'Selesai' => 'bg-black/5 text-muted',
    ];

    [$title, $subtitle, $empty] = match ($me['role']) {
        'hod' => ['Proker', 'Yang sedang berjalan · '.count($prokers).' proker', 'Belum ada proker yang sedang berjalan. Buat proker baru atau lihat semua proker di <a href="'.route('dashboard').'" class="font-medium text-accent hover:underline">Ringkasan</a>.'],
        'pj' => ['Proker', 'Yang sedang berjalan dan kamu awasi · '.count($prokers).' proker', 'Belum ada proker berjalan yang kamu awasi sebagai PJ.'],
        default => ['Proker saya', 'Yang kamu ikuti · '.count($prokers).' proker', 'Kamu belum terdaftar di proker mana pun.'],
    };
@endphp

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-[22px] font-semibold leading-tight">{{ $title }}</h1>
        <p class="mt-0.5 text-muted">{{ $subtitle }}</p>
    </div>
    @if ($me['role'] === 'hod')
        <a href="{{ route('proker.create') }}" class="inline-flex h-9 items-center rounded-[10px] bg-accent px-4 font-medium text-white">Buat proker</a>
    @endif
</div>

@if (count($prokers) === 0)
    <p class="mt-6 rounded-xl border border-line p-5 text-muted">{!! $empty !!}</p>
@else
    <div class="mt-6 overflow-x-auto rounded-xl border border-line">
        <table class="w-full {{ $own ? 'min-w-[640px]' : 'min-w-[760px]' }} text-left">
            <thead class="text-[13px] text-muted">
                <tr>
                    <th class="px-5 py-3 font-normal">Proker</th>
                    @if ($own)
                        <th class="py-3 pr-4 font-normal">Peran kamu</th>
                        <th class="py-3 pr-4 font-normal">Hari-H</th>
                        <th class="py-3 pr-4 font-normal">Status</th>
                        <th class="px-5 py-3 font-normal">Jobdesc</th>
                    @else
                        <th class="py-3 pr-4 font-normal">Hari-H</th>
                        <th class="py-3 pr-4 font-normal">Jobdesc</th>
                        <th class="py-3 pr-4 font-normal">Panitia</th>
                        <th class="px-5 py-3 text-right font-normal">Anggaran terpakai</th>
                    @endif
                </tr>
            </thead>
            @foreach ($prokers as $p)
                @php $st = DummyData::stats($p); @endphp
                <tbody class="border-t border-line align-top">
                    <tr>
                        <td class="px-5 py-4">
                            <a href="{{ route('proker.show', $p['id']) }}" class="font-medium hover:underline">{{ $p['nama'] }}</a>
                            <p class="text-[13px] text-muted">{{ $p['ketupel'] ? 'Ketupel: '.$p['ketupel'] : 'Ketupel belum ditunjuk' }} · PJ: {{ $p['pj'] }}</p>
                        </td>
                        @if ($own)
                            <td class="py-4 pr-4">{{ Access::roleLabel($p, $me['name']) }}</td>
                            <td class="whitespace-nowrap py-4 pr-4 tabular-nums">
                                {{ $tgl($p['hariH']) }}
                                <p class="text-[13px] text-muted">{{ $left($st['daysLeft']) }}</p>
                            </td>
                            <td class="py-4 pr-4"><span class="whitespace-nowrap rounded-full px-2 py-0.5 text-[13px] {{ $pill[$p['status']] }}">{{ $p['status'] }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 tabular-nums">
                                {{ $st['done'] }} dari {{ $st['total'] }} selesai
                                <p class="text-[13px] {{ $st['overdue'] ? 'text-bad' : 'text-muted' }}">{{ $st['overdue'] ? $st['overdue'].' lewat tenggat' : 'tidak ada yang terlambat' }}</p>
                            </td>
                        @else
                            <td class="whitespace-nowrap py-4 pr-4 tabular-nums">
                                {{ $tgl($p['hariH']) }}
                                <p class="text-[13px] text-muted">{{ $left($st['daysLeft']) }}</p>
                            </td>
                            <td class="whitespace-nowrap py-4 pr-4 tabular-nums">
                                {{ $st['done'] }} dari {{ $st['total'] }} selesai
                                <p class="text-[13px] {{ $st['overdue'] ? 'text-bad' : 'text-muted' }}">{{ $st['overdue'] ? $st['overdue'].' lewat tenggat' : 'tidak ada yang terlambat' }}</p>
                            </td>
                            <td class="py-4 pr-4">
                                {{ $st['divisi'] }} divisi
                                <p class="text-[13px] tabular-nums text-muted">{{ $st['koor'] }} koordinator · {{ $st['anggota'] }} anggota</p>
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                @if ($st['spent'] !== null)
                                    {{ $st['spent'] }}%
                                    <p class="text-[13px] text-muted">{{ $rp($p['realisasi']) }}</p>
                                @else
                                    <span class="text-muted">–</span>
                                    <p class="text-[13px] text-muted">dari {{ $rp($p['rencana']) }}</p>
                                @endif
                            </td>
                        @endif
                    </tr>
                </tbody>
            @endforeach
        </table>
    </div>
    @if ($me['role'] === 'hod')
        <p class="mt-3 text-[13px] text-muted">Proker yang masih usulan, dalam pembentukan panitia, LPJ, atau sudah selesai ada di <a href="{{ route('dashboard') }}" class="font-medium text-accent hover:underline">Ringkasan</a>.</p>
    @endif
@endif
@endsection
