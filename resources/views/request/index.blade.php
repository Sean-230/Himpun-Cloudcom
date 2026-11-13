@extends('layouts.app')
@section('title', 'Request Antar Divisi — Himpun')

@php
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $tgl = fn (string $d) => \Carbon\Carbon::parse($d)->day.' '.$bulan[\Carbon\Carbon::parse($d)->month].' '.\Carbon\Carbon::parse($d)->year;

    $pill = [
        'Menunggu' => 'bg-[#fbf0dc] text-warn',
        'Diproses' => 'bg-[#e8f0f7] text-actual',
        'Selesai'  => 'bg-[#e6f4ec] text-good',
        'Ditolak'  => 'bg-black/5 text-muted',
    ];

    $reqList = collect($requests);
    $tabs = [
        'semua'    => ['Semua request', $reqList->count()],
        'Menunggu' => ['Menunggu respon', $reqList->where('status', 'Menunggu')->count()],
        'Diproses' => ['Sedang dikerjakan', $reqList->where('status', 'Diproses')->count()],
        'Selesai'  => ['Selesai', $reqList->whereIn('status', ['Selesai', 'Ditolak'])->count()],
    ];

    $aktifProkers = collect($prokers)->whereNotIn('status', ['Selesai'])->values();
@endphp

@section('content')
<div x-data="{
    tab: 'semua',
    showModal: {{ $errors->any() ? 'true' : 'false' }},
    filterDivisi: 'semua',
    selectedProker: '{{ old('proker', $aktifProkers->first()['nama'] ?? '') }}',
    dariDivisi: '{{ old('dari_divisi', 'PDD Dokum') }}',
    keDivisi: '{{ old('ke_divisi', 'Inventory') }}'
}">
    {{-- Header Halaman --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-[22px] font-semibold leading-tight">Request antar divisi</h1>
            <p class="mt-0.5 text-muted">Koordinasi kebutuhan operasional lintas koordinator divisi</p>
        </div>
        <button type="button" @click="showModal = true" class="inline-flex h-9 items-center gap-2 rounded-[10px] bg-accent px-4 font-medium text-white hover:opacity-95">
            <span>+ Buat request baru</span>
        </button>
    </div>

    {{-- Alert Notifikasi Sukses --}}
    @if (session('success'))
        <div class="mt-4 flex items-center justify-between rounded-xl border border-line bg-[#e6f4ec] px-4 py-3 text-[13px] text-good">
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="font-medium hover:underline">Tutup</button>
        </div>
    @endif

    {{-- Tab Filter & Filter Divisi --}}
    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-b border-line">
        <div class="flex gap-5 overflow-x-auto" role="tablist">
            @foreach ($tabs as $key => [$label, $count])
                <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                        class="-mb-px whitespace-nowrap border-b-2 pb-2.5"
                        :class="tab === '{{ $key }}' ? 'border-accent font-medium' : 'border-transparent text-muted hover:text-ink'">
                    {{ $label }} <span class="tabular-nums text-muted">{{ $count }}</span>
                </button>
            @endforeach
        </div>

        <select x-model="filterDivisi" aria-label="Filter divisi" class="mb-2 h-8 rounded-lg bg-white px-2.5 text-[13px] ring-1 ring-line">
            <option value="semua">Semua divisi</option>
            @foreach ($divisions as $div)
                <option value="{{ $div['name'] }}">{{ $div['name'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- Daftar Request Antar-Koordinator --}}
    <div class="mt-6 flex flex-col gap-3">
        @forelse ($reqList as $req)
            @php
                $tabMatch = $req['status'] === 'Ditolak' ? 'Selesai' : $req['status'];
            @endphp
            <article
                x-show="(tab === 'semua' || tab === '{{ $tabMatch }}') && (filterDivisi === 'semua' || filterDivisi === '{{ $req['dari_divisi'] }}' || filterDivisi === '{{ $req['ke_divisi'] }}')"
                class="rounded-xl border border-line bg-white p-5 transition-colors hover:border-black/20">

                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        {{-- Alur Divisi: Dari -> Ke --}}
                        <div class="flex flex-wrap items-center gap-2 text-[13px]">
                            <span class="rounded-md bg-black/5 px-2 py-0.5 font-medium text-ink">{{ $req['dari_divisi'] }}</span>
                            <span class="text-muted">→</span>
                            <span class="rounded-md bg-black/5 px-2 py-0.5 font-medium text-ink">{{ $req['ke_divisi'] }}</span>
                            <span class="text-muted">· {{ $req['proker'] }}</span>
                            <span class="text-muted">· {{ $req['waktu'] }}</span>
                        </div>

                        <h2 class="mt-2 text-base font-semibold text-ink">{{ $req['judul'] }}</h2>
                        <p class="mt-1 text-[13px] text-muted">{{ $req['catatan'] }}</p>
                    </div>

                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[13px] {{ $pill[$req['status']] ?? 'bg-black/5 text-muted' }}">
                        {{ $req['status'] }}
                    </span>
                </div>

                {{-- Footer Kartu: Info Koordinator, Tenggat & Tombol Aksi --}}
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-3 text-[13px]">
                    <div class="flex flex-wrap items-center gap-4 text-muted">
                        <span class="flex items-center gap-1.5">
                            <x-avatar :name="$req['dari_koor']" />
                            <span>Dari: <strong class="font-medium text-ink">{{ $req['dari_koor'] }}</strong></span>
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1.5">
                            <x-avatar :name="$req['ke_koor']" />
                            <span>Untuk: <strong class="font-medium text-ink">{{ $req['ke_koor'] }}</strong></span>
                        </span>
                        <span>•</span>
                        <span class="tabular-nums">Tenggat: <strong class="font-medium text-ink">{{ $tgl($req['tenggat']) }}</strong></span>
                    </div>

                    {{-- Aksi Koordinator Tujuan --}}
                    <div class="flex items-center gap-2">
                        @if ($req['status'] === 'Menunggu')
                            <form method="POST" action="{{ route('request.status', $req['id']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Ditolak">
                                <button type="submit" class="h-8 rounded-lg px-3 text-[13px] text-muted ring-1 ring-line hover:bg-black/5 hover:text-ink">
                                    Tolak
                                </button>
                            </form>
                            <form method="POST" action="{{ route('request.status', $req['id']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Diproses">
                                <button type="submit" class="h-8 rounded-lg bg-accent px-3 text-[13px] font-medium text-white hover:opacity-95">
                                    Terima & Kerjakan
                                </button>
                            </form>
                        @elseif ($req['status'] === 'Diproses')
                            <form method="POST" action="{{ route('request.status', $req['id']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Selesai">
                                <button type="submit" class="h-8 rounded-lg bg-[#e6f4ec] px-3 text-[13px] font-medium text-good hover:opacity-90">
                                    Tandai Selesai
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-line p-8 text-center text-muted">
                Belum ada permintaan antar-divisi.
            </div>
        @endforelse
    </div>

    {{-- Modal Form Buat Request Baru --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="showModal = false">
        <div class="w-full max-w-lg rounded-2xl border border-line bg-white p-6" @click.outside="showModal = false">
            <div class="flex items-center justify-between border-b border-line pb-3">
                <h2 class="text-base font-semibold">Buat request ke divisi lain</h2>
                <button type="button" @click="showModal = false" class="text-muted hover:text-ink">✕</button>
            </div>

            @if ($errors->any())
                <div class="mt-3 rounded-lg bg-[#fbf0dc] p-3 text-[13px] text-warn">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('request.store') }}" class="mt-4 flex flex-col gap-4">
                @csrf

                <div>
                    <label class="block text-[13px] font-medium text-ink">Program Kerja (Proker)</label>
                    <select name="proker" x-model="selectedProker" required class="mt-1 h-9 w-full rounded-lg bg-white px-3 text-sm ring-1 ring-line">
                        @foreach ($aktifProkers as $p)
                            <option value="{{ $p['nama'] }}">{{ $p['nama'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-[13px] font-medium text-ink">Dari Divisi (Pengirim)</label>
                        <select name="dari_divisi" x-model="dariDivisi" required class="mt-1 h-9 w-full rounded-lg bg-white px-3 text-sm ring-1 ring-line">
                            @foreach ($divisions as $div)
                                <option value="{{ $div['name'] }}">{{ $div['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-medium text-ink">Ke Divisi (Tujuan)</label>
                        <select name="ke_divisi" x-model="keDivisi" required class="mt-1 h-9 w-full rounded-lg bg-white px-3 text-sm ring-1 ring-line">
                            @foreach ($divisions as $div)
                                <option value="{{ $div['name'] }}">{{ $div['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-ink">Kebutuhan / Judul Request</label>
                    <input type="text" name="judul" value="{{ old('judul') }}" required
                           placeholder="Contoh: Peminjaman 2 kamera & tripod untuk liputan"
                           class="mt-1 h-9 w-full rounded-lg bg-white px-3 text-sm ring-1 ring-line placeholder:text-muted">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-ink">Tenggat Dibutuhkan</label>
                    <input type="date" name="tenggat" value="{{ old('tenggat', now()->addDays(3)->format('Y-m-d')) }}" required
                           class="mt-1 h-9 w-full rounded-lg bg-white px-3 text-sm ring-1 ring-line">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-ink">Detail Spesifikasi / Catatan</label>
                    <textarea name="catatan" rows="3" placeholder="Jelaskan detail ukuran, jumlah barang, atau lokasi penggunaan..."
                              class="mt-1 w-full rounded-lg bg-white p-3 text-sm ring-1 ring-line placeholder:text-muted">{{ old('catatan') }}</textarea>
                </div>

                <div class="mt-2 flex items-center justify-end gap-2 border-t border-line pt-4">
                    <button type="button" @click="showModal = false" class="h-9 rounded-[10px] px-4 text-sm text-muted ring-1 ring-line hover:bg-black/5 hover:text-ink">
                        Batal
                    </button>
                    <button type="submit" class="h-9 rounded-[10px] bg-accent px-4 text-sm font-medium text-white hover:opacity-95">
                        Kirim Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection