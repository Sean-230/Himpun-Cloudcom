@extends('layouts.app')
@section('title', 'Ringkasan — Himpun')

@php
    use App\Support\DummyData;

    // $prokers and $members come from App\Support\DummyData (see the route).
    $rp = fn (int $n) => 'Rp '.number_format($n, 0, ',', '.');
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $tgl = fn (string $d) => \Carbon\Carbon::parse($d)->day.' '.$bulan[\Carbon\Carbon::parse($d)->month];
    $sisaHari = fn (string $d) => (int) ceil(now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($d), false));

    $group = fn (string $status) => match ($status) { 'Selesai' => 'selesai', 'Usulan' => 'usulan', default => 'berjalan' };
    $pill = [
        'Berjalan' => 'bg-[#e6f4ec] text-good',
        'Pembentukan panitia' => 'bg-[#fbf0dc] text-warn',
        'LPJ' => 'bg-[#e8f0f7] text-actual',
        'Usulan' => 'bg-black/5 text-muted',
        'Selesai' => 'bg-black/5 text-muted',
    ];
    $prokerList = collect($prokers);
    $tabs = [
        'semua' => ['Semua proker', $prokerList->count()],
        'berjalan' => ['Berjalan', $prokerList->filter(fn ($p) => $group($p['status']) === 'berjalan')->count()],
        'usulan' => ['Usulan', $prokerList->filter(fn ($p) => $group($p['status']) === 'usulan')->count()],
        'selesai' => ['Selesai', $prokerList->filter(fn ($p) => $group($p['status']) === 'selesai')->count()],
    ];
    $aktif = $prokerList->where('status', '!=', 'Selesai');

    $kpis = [
        ['Proker aktif', $aktif->count(), $aktif->where('status', 'Berjalan')->count().' berjalan, '.$aktif->where('status', 'Usulan')->count().' usulan'],
        ['Anggota aktif', count($members), '8 peserta Pulse'],
        ['Persetujuan menunggu', 5, '3 menunggu kamu'],
        ['Tugas lewat tenggat', 4, 'terlama 6 hari'],
    ];

    // Rupiah in millions, only for prokers that have spent something.
    $budget = $prokerList->filter(fn ($p) => $p['realisasi'] > 0)
        ->mapWithKeys(fn ($p) => [$p['short'] => [$p['rencana'] / 1e6, $p['realisasi'] / 1e6]])->all();
    $budgetMax = ceil(collect($budget)->flatten()->max() / 10) * 10;
    $techfest = $prokerList->firstWhere('id', 1);

    // Workload: columns are the prokers with a committee being built or running.
    $cols = $prokerList->whereIn('status', ['Berjalan', 'Pembentukan panitia'])->values();
    $workload = collect($members)->sortByDesc('roles')->take(6)->values();

    $approvals = [
        ['Master rundown & brief pembicara', 'Techfest UC 2026', 'Felicia Hartono', 2, '2 hari'],
        ['Pengeluaran sewa sound system '.$rp(4200000), 'Techfest UC 2026', 'Bryan Santoso', 2, '1 hari'],
        ['Proposal National Hackathon UC', 'Usulan proker', 'Darren Lim', 1, '4 hari'],
    ];

    $upcoming = $prokerList->filter(fn ($p) => $sisaHari($p['hariH']) >= 0)->sortBy('hariH')->take(4)
        ->map(fn ($p) => ['Hari-H '.$p['nama'], $p['hariH']]);
@endphp

@section('content')
<div x-data="{ tab: 'semua', open: null }">
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-[22px] font-semibold leading-tight">Ringkasan</h1>
        <p class="mt-0.5 text-muted">SU IMT · Periode 2025/2026</p>
    </div>
    <a href="{{ route('proker.create') }}" class="inline-flex h-9 items-center rounded-[10px] bg-accent px-4 font-medium text-white">Buat proker</a>
</div>

<div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-b border-line">
    <div class="flex gap-5 overflow-x-auto" role="tablist">
        @foreach ($tabs as $key => [$label, $count])
            <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                    class="-mb-px whitespace-nowrap border-b-2 pb-2.5"
                    :class="tab === '{{ $key }}' ? 'border-accent font-medium' : 'border-transparent text-muted hover:text-ink'">{{ $label }} <span class="tabular-nums text-muted">{{ $count }}</span></button>
        @endforeach
    </div>
    <select aria-label="Rentang tanggal" class="mb-2 h-8 rounded-lg px-2 text-[13px] ring-1 ring-line">
        <option>Periode 2025/2026</option>
        <option>30 hari terakhir</option>
    </select>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_384px]">
    <div class="flex min-w-0 flex-col gap-4">
        {{-- KPI strip: one container, equal cells, 1px dividers. Type only, no tiles. --}}
        <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-line bg-line lg:grid-cols-4">
            @foreach ($kpis as [$label, $value, $caption])
                <div class="bg-white p-4">
                    <dt class="text-muted">{{ $label }}</dt>
                    <dd class="mt-1 text-[28px] font-normal leading-none tabular-nums">{{ $value }}</dd>
                    <p class="mt-2 text-[13px] text-muted">{{ $caption }}</p>
                </div>
            @endforeach
        </dl>

        {{-- Every proker; a row opens to show who fills each division. --}}
        <section class="rounded-xl border border-line p-5">
            <h2 class="text-base font-semibold">Semua proker</h2>
            <p class="mt-0.5 text-[13px] text-muted">Klik nama proker untuk melihat siapa mengisi tiap divisi.</p>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[620px] text-left">
                    <thead class="text-[13px] text-muted">
                        <tr>
                            <th class="py-2 font-normal">Proker</th>
                            <th class="py-2 font-normal">Hari-H</th>
                            <th class="py-2 font-normal">Status</th>
                            <th class="py-2 font-normal">Panitia</th>
                            <th class="py-2 text-right font-normal">Anggaran terpakai</th>
                        </tr>
                    </thead>
                    @foreach ($prokers as $p)
                        @php
                            $koor = collect($p['divisions'])->sum(fn ($d) => count($d['koor']));
                            $anggota = collect($p['divisions'])->sum(fn ($d) => count($d['anggota']));
                            $pct = $p['realisasi'] ? round($p['realisasi'] / $p['rencana'] * 100) : null;
                        @endphp
                        <tbody :hidden="! ['semua', '{{ $group($p['status']) }}'].includes(tab)" class="border-t border-line align-top">
                            <tr>
                                <td class="py-3 pr-3">
                                    <button type="button" @click="open = open === {{ $p['id'] }} ? null : {{ $p['id'] }}" :aria-expanded="open === {{ $p['id'] }}" class="flex items-start gap-1.5 text-left font-medium hover:underline">
                                        <svg class="mt-0.5 size-4 shrink-0 text-muted transition-transform" :class="open === {{ $p['id'] }} && 'rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                                        <span>{{ $p['nama'] }} <span class="font-normal text-muted">· {{ $p['jenis'] }}</span></span>
                                    </button>
                                    <p class="ml-[22px] text-[13px]">{{ $p['ketupel'] ? 'Ketupel: '.$p['ketupel'] : 'Ketupel belum ditunjuk' }}</p>
                                </td>
                                <td class="whitespace-nowrap py-3 pr-3 tabular-nums">{{ $tgl($p['hariH']) }} {{ \Carbon\Carbon::parse($p['hariH'])->year }}</td>
                                <td class="py-3 pr-3"><span class="whitespace-nowrap rounded-full px-2 py-0.5 text-[13px] {{ $pill[$p['status']] }}">{{ $p['status'] }}</span></td>
                                <td class="py-3 pr-3">
                                    {{ count($p['divisions']) }} divisi
                                    <p class="text-[13px] tabular-nums text-muted">{{ $koor }} koordinator · {{ $anggota }} anggota</p>
                                </td>
                                <td class="py-3 text-right tabular-nums">
                                    @if ($pct !== null)
                                        {{ $pct }}%
                                        <p class="text-[13px] text-muted">{{ $rp($p['realisasi']) }}</p>
                                    @else
                                        <span class="text-muted">–</span>
                                        <p class="text-[13px] text-muted">dari {{ $rp($p['rencana']) }}</p>
                                    @endif
                                </td>
                            </tr>
                            <tr x-show="open === {{ $p['id'] }}" x-cloak>
                                <td colspan="5" class="pb-4 pl-[22px]">
                                    <p class="mb-3 text-[13px] text-muted">PJ: {{ $p['pj'] }} · {{ $p['lokasi'] }}</p>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        @foreach ($p['divisions'] as $div)
                                            <div class="rounded-lg border border-line p-3">
                                                <p class="font-medium">{{ $div['name'] }}</p>
                                                <p class="mt-1 text-[13px] {{ $div['koor'] ? '' : 'text-muted' }}">{{ $div['koor'] ? 'Koordinator: '.implode(', ', $div['koor']) : 'Belum ada koordinator' }}</p>
                                                <p class="text-[13px] text-muted">{{ $div['anggota'] ? count($div['anggota']).' anggota: '.implode(', ', $div['anggota']) : 'Belum ada anggota' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </div>
        </section>

        {{-- The largest chart: plan against actual spend. --}}
        <section class="rounded-xl border border-line p-5">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="text-base font-semibold">Anggaran rencana vs realisasi per proker</h2>
                <p class="flex items-center gap-4 text-[13px] text-muted">
                    <span class="flex items-center gap-1.5"><i class="size-2.5 rounded-sm bg-planned"></i>Rencana</span>
                    <span class="flex items-center gap-1.5"><i class="size-2.5 rounded-sm bg-actual"></i>Realisasi</span>
                </p>
            </div>
            <div class="relative mt-6 h-56">
                @foreach ([0, 1, 2, 3] as $line)
                    <div class="absolute inset-x-0 border-t border-line/70" style="bottom: {{ $line * 33.33 }}%"></div>
                @endforeach
                <div class="relative flex h-full items-end justify-around">
                    @foreach ($budget as $name => [$plan, $actual])
                        <div class="flex h-full flex-1 items-end justify-center gap-1">
                            @foreach ([[$plan, 'bg-planned'], [$actual, 'bg-actual']] as [$v, $colour])
                                <div class="flex h-full w-4 flex-col items-center justify-end sm:w-8">
                                    <span class="mb-1 text-[13px] tabular-nums text-muted">{{ $v > 0 ? number_format($v, 1, ',', '.') : '–' }}</span>
                                    <div class="w-full rounded-t {{ $colour }}" style="height: {{ $v / $budgetMax * 88 }}%"></div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-2 flex justify-around text-[13px] text-muted">
                @foreach (array_keys($budget) as $name)
                    <span class="flex-1 px-1 text-center">{{ $name }}</span>
                @endforeach
            </div>
            <p class="mt-3 text-[13px] text-muted">Dalam juta rupiah. {{ $techfest['nama'] }}: {{ $rp($techfest['realisasi']) }} dari {{ $rp($techfest['rencana']) }} terpakai ({{ round($techfest['realisasi'] / $techfest['rencana'] * 100) }}%).</p>
        </section>

        <section class="rounded-xl border border-line p-5">
            <h2 class="text-base font-semibold">Beban kerja anggota lintas proker</h2>
            <p class="mt-0.5 text-[13px] text-muted">K = Ketupel · PJ = Penanggung jawab · Ko = Koordinator · A = Anggota</p>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[520px] text-left">
                    <thead class="text-[13px] text-muted">
                        <tr>
                            <th class="py-2 font-normal">Anggota</th>
                            @foreach ($cols as $c)
                                <th class="py-2 font-normal">{{ $c['short'] }}</th>
                            @endforeach
                            <th class="py-2 text-right font-normal">Peran aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($workload as $m)
                            <tr class="border-t border-line">
                                <td class="py-2.5">
                                    <span class="flex items-center gap-2"><x-avatar :name="$m['name']" />{{ $m['name'] }}</span>
                                </td>
                                @foreach ($cols as $c)
                                    @php $role = DummyData::roleIn($c, $m['name']); @endphp
                                    <td @class(['py-2.5 tabular-nums', 'text-muted' => $role === null || $role === 'A'])>{{ $role ?? '–' }}</td>
                                @endforeach
                                <td class="py-2.5 text-right tabular-nums">
                                    @if ($m['roles'] >= 3)
                                        <span class="rounded-full bg-[#fbf0dc] px-2 py-0.5 text-[13px] text-warn">{{ $m['roles'] }} peran</span>
                                    @else
                                        {{ $m['roles'] }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="flex min-w-0 flex-col gap-6">
        <section>
            <h2 class="mb-2 text-base font-semibold">Menunggu persetujuanmu</h2>
            <ul class="rounded-xl border border-line">
                @foreach ($approvals as [$title, $proker, $by, $step, $age])
                    <li class="flex flex-col gap-2 p-4 {{ $loop->first ? '' : 'border-t border-line' }}">
                        <p class="font-medium">{{ $title }}</p>
                        <p class="flex items-center gap-2 text-[13px] text-muted"><x-avatar :name="$by" />{{ $by }} · {{ $proker }} · {{ $age }}</p>
                        <x-chain-stepper :current="$step" />
                    </li>
                @endforeach
            </ul>
            <button type="button" class="mt-3 h-[30px] w-full rounded-lg px-3 text-[13px] ring-1 ring-line hover:bg-black/5">Lihat semua (5)</button>
        </section>

        <section>
            <h2 class="mb-2 text-base font-semibold">Segera</h2>
            <ul class="rounded-xl border border-line">
                @foreach ($upcoming as [$title, $date])
                    @php $left = $sisaHari($date); @endphp
                    <li class="flex items-center gap-3 p-4 {{ $loop->first ? '' : 'border-t border-line' }}">
                        <span class="w-12 shrink-0 tabular-nums text-muted">{{ $tgl($date) }}</span>
                        <span class="min-w-0 flex-1">{{ $title }}</span>
                        <span class="shrink-0 text-[13px] tabular-nums text-muted">{{ $left > 0 ? $left.' hari lagi' : 'hari ini' }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </aside>
</div>
</div>
@endsection
