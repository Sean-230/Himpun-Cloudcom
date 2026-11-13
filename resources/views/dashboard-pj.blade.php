@extends('layouts.app')
@section('title', 'Ringkasan — Himpun')

@php
    use App\Support\Access;
    use App\Support\DummyData;

    // Short summary for a PJ: only the running prokers they supervise ($prokers, see the route).
    $me = Access::me();
    $stats = collect($prokers)->map(fn ($p) => DummyData::stats($p));
    $done = $stats->sum('done');
    $total = $stats->sum('total');
    $nearest = collect($prokers)->sortBy('hariH')->first();
    $nearestLeft = $nearest ? DummyData::stats($nearest)['daysLeft'] : null;
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $tgl = function (string $d) use ($bulan) {
        $c = \Carbon\Carbon::parse($d);

        return $c->day.' '.$bulan[$c->month].' '.$c->year;
    };
    $scope = ['pleno' => 'Pleno', 'koor' => 'Koordinator dan HOD/PJ'];

    $kpis = [
        ['Proker berjalan', count($prokers), 'yang kamu awasi'],
        ['Hari-H terdekat', $nearest ? $nearestLeft.' hari' : '–', $nearest['nama'] ?? 'belum ada'],
        ['Jobdesc selesai', $done.' dari '.$total, $total ? round($done / $total * 100).'% selesai' : 'belum ada jobdesc'],
        ['Lewat tenggat', $stats->sum('overdue'), $stats->sum('overdue') ? 'perlu perhatian' : 'tidak ada yang terlambat'],
    ];
@endphp

@section('content')
<h1 class="text-[22px] font-semibold leading-tight">Ringkasan</h1>
<p class="mt-0.5 text-muted">Proker yang kamu awasi sebagai PJ · {{ $me['name'] }}</p>

<dl class="mt-5 grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-line bg-line lg:grid-cols-4">
    @foreach ($kpis as [$label, $value, $caption])
        <div class="bg-white p-4">
            <dt class="text-muted">{{ $label }}</dt>
            <dd class="mt-1 text-[28px] font-normal leading-none tabular-nums">{{ $value }}</dd>
            <p class="mt-2 text-[13px] text-muted">{{ $caption }}</p>
        </div>
    @endforeach
</dl>

<h2 class="mb-3 mt-8 text-base font-semibold">Proker yang sedang berjalan</h2>

@if (count($prokers) === 0)
    <p class="rounded-xl border border-line p-5 text-muted">Belum ada proker berjalan yang kamu awasi sebagai PJ.</p>
@else
    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($prokers as $i => $p)
            @php $st = $stats[$i]; $pct = $st['total'] ? round($st['done'] / $st['total'] * 100) : 0; @endphp
            <section class="rounded-xl border border-line p-5">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <a href="{{ route('proker.show', $p['id']) }}" class="text-base font-semibold hover:underline">{{ $p['nama'] }}</a>
                    <span class="text-[13px] tabular-nums text-muted">{{ $st['daysLeft'] > 0 ? $st['daysLeft'].' hari lagi' : 'hari ini' }}</span>
                </div>
                <p class="mt-0.5 text-[13px] text-muted">Hari-H {{ $tgl($p['hariH']) }} · {{ $p['lokasi'] }} · {{ $p['ketupel'] ? 'Ketupel: '.$p['ketupel'] : 'Ketupel belum ditunjuk' }}</p>

                <div class="mt-3 flex items-baseline justify-between text-[13px]">
                    <span class="tabular-nums">{{ $st['done'] }} dari {{ $st['total'] }} jobdesc selesai</span>
                    <span class="{{ $st['overdue'] ? 'text-bad' : 'text-muted' }}">{{ $st['overdue'] ? $st['overdue'].' lewat tenggat' : 'tidak ada yang terlambat' }}</span>
                </div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progres {{ $p['nama'] }}">
                    <div class="h-full rounded-full bg-actual" style="width: {{ $pct }}%"></div>
                </div>

                <p class="mt-3 text-[13px] text-muted">
                    @forelse ($p['rapat'] as $r)
                        {{ $scope[$r['scope']] ?? 'Divisi '.str_replace('div:', '', $r['scope']) }}: {{ $hari[$r['day']] }} {{ $r['time'] }}{{ $loop->last ? '' : ' · ' }}
                    @empty
                        Belum ada jadwal rapat mingguan.
                    @endforelse
                </p>
                <a href="{{ route('proker.show', $p['id']) }}" class="mt-3 inline-block text-[13px] font-medium text-accent hover:underline">Buka detail proker →</a>
            </section>
        @endforeach
    </div>
@endif
@endsection
