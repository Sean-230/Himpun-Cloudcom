@extends('layouts.app')
@section('title', $proker['nama'].' — Himpun')

@php
    $pill = [
        'Berjalan' => 'bg-[#e6f4ec] text-good',
        'Pembentukan panitia' => 'bg-[#fbf0dc] text-warn',
        'LPJ' => 'bg-[#e8f0f7] text-actual',
        'Usulan' => 'bg-black/5 text-muted',
        'Selesai' => 'bg-black/5 text-muted',
    ];
@endphp

@section('content')
{{-- shortcut: jobdesc and meeting changes live in the page only; saving needs the models. --}}
<div x-data="prokerDetail(@js($proker), { canEdit: @js($canEdit) })">
    <a href="{{ route('proker.index') }}" class="text-[13px] text-muted hover:text-ink">← Proker</a>
    <h1 class="mt-1 text-[22px] font-semibold leading-tight">{{ $proker['nama'] }}</h1>
    <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-muted">
        <span class="rounded-full px-2 py-0.5 text-[13px] {{ $pill[$proker['status']] }}">{{ $proker['status'] }}</span>
        <span>{{ $proker['jenis'] }}</span>
        <span>· {{ $proker['ketupel'] ? 'Ketupel: '.$proker['ketupel'] : 'Ketupel belum ditunjuk' }}</span>
        <span>· PJ: {{ $proker['pj'] }}</span>
    </p>

    <dl class="mt-5 grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-line bg-line lg:grid-cols-4">
        <div class="bg-white p-4">
            <dt class="text-muted">Hari-H</dt>
            <dd class="mt-1 text-[28px] font-normal leading-none tabular-nums"
                x-text="daysLeft > 0 ? daysLeft + ' hari' : daysLeft === 0 ? 'Hari ini' : 'Selesai'"></dd>
            <p class="mt-2 text-[13px] text-muted" x-text="fmtLong(proker.hariH) + ' · ' + proker.lokasi"></p>
        </div>
        <div class="bg-white p-4">
            <dt class="text-muted">Jobdesc selesai</dt>
            <dd class="mt-1 text-[28px] font-normal leading-none tabular-nums" x-text="totals.done + ' dari ' + totals.total"></dd>
            <p class="mt-2 text-[13px] text-muted" x-text="totals.overdue + ' lewat tenggat'"></p>
        </div>
        <div class="bg-white p-4">
            <dt class="text-muted">Divisi</dt>
            <dd class="mt-1 text-[28px] font-normal leading-none tabular-nums" x-text="divisions.length"></dd>
            <p class="mt-2 text-[13px] text-muted" x-text="totals.koor + ' koordinator · ' + totals.anggota + ' anggota'"></p>
        </div>
        <div class="bg-white p-4">
            <dt class="text-muted">Rapat mingguan</dt>
            <dd class="mt-1 text-[28px] font-normal leading-none tabular-nums" x-text="rapat.length"></dd>
            <p class="mt-2 text-[13px] text-muted" x-text="meetings.length + ' rapat sampai Hari-H'"></p>
        </div>
    </dl>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_384px]">
        {{-- Jobdesc and targets, one card per division --}}
        <div class="flex min-w-0 flex-col gap-4">
            <div>
                <h2 class="text-base font-semibold">Jobdesc dan target per divisi</h2>
                <p class="mt-0.5 text-[13px] text-muted" x-text="canEdit ? 'Target dihitung mundur dari Hari-H. Centang kalau sudah selesai.' : 'Target dihitung mundur dari Hari-H. Hanya HOD yang bisa mengubah jobdesc dan jadwal rapat.'"></p>
            </div>

            <template x-for="div in divisions" :key="div.name">
                <section class="rounded-xl border border-line p-5">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="text-base font-semibold" x-text="div.name"></h3>
                        <p class="text-[13px] tabular-nums text-muted" x-text="progress(div).done + ' dari ' + progress(div).total + ' selesai'"></p>
                    </div>
                    <p class="mt-0.5 text-[13px] text-muted" x-text="div.koor.length ? 'Koordinator: ' + div.koor.join(', ') : 'Belum ada koordinator'"></p>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" :aria-valuenow="progress(div).pct" aria-valuemin="0" aria-valuemax="100" :aria-label="'Progres ' + div.name">
                        <div class="h-full rounded-full bg-actual" :style="'width: ' + progress(div).pct + '%'"></div>
                    </div>

                    <ul class="mt-3">
                        <template x-for="j in div.jobdesc" :key="j.id">
                            <li class="flex items-start gap-3 border-t border-line py-2.5 first:border-t-0">
                                <input type="checkbox" x-model="j.done" :disabled="!canEdit" class="mt-0.5 size-4 shrink-0 accent-accent disabled:opacity-60" :aria-label="'Selesai: ' + j.title">
                                <span class="min-w-0 flex-1" :class="j.done && 'text-muted line-through'" x-text="j.title"></span>
                                <span class="shrink-0 text-[13px] tabular-nums text-muted" x-text="targetLabel(j.h) + ' · ' + fmtShort(dateFor(j.h))"></span>
                                <span x-show="status(j) !== 'Belum'" class="shrink-0 rounded-full px-2 py-0.5 text-[13px]"
                                      :class="status(j) === 'Selesai' ? 'bg-[#e6f4ec] text-good' : 'bg-[#fbe9e7] text-bad'" x-text="status(j)"></span>
                                <button type="button" x-show="canEdit" @click="removeJobdesc(div, j.id)" class="px-1 text-muted hover:text-ink" :aria-label="'Hapus jobdesc ' + j.title">×</button>
                            </li>
                        </template>
                    </ul>
                    <p x-show="div.jobdesc.length === 0" class="mt-3 text-[13px] text-muted">Belum ada jobdesc untuk divisi ini.</p>

                    <form x-show="canEdit" @submit.prevent="addJobdesc(div)" class="mt-3 flex flex-wrap items-center gap-2 border-t border-line pt-3">
                        <input type="text" x-model="jobdescDraft[div.name].title" maxlength="120" placeholder="Jobdesc baru" class="field min-w-0 flex-1" :aria-label="'Jobdesc baru untuk ' + div.name">
                        <label class="flex items-center gap-1.5 text-[13px] text-muted">
                            <span>Target H-</span>
                            <input type="number" x-model.number="jobdescDraft[div.name].h" min="-60" max="365" class="field w-16! px-2! text-center" aria-label="Target, hari sebelum Hari-H">
                        </label>
                        <button type="submit" class="h-10 rounded-[10px] px-4 ring-1 ring-line hover:bg-black/5">Tambah</button>
                        <p x-show="jobdescError[div.name]" x-text="jobdescError[div.name]" role="alert" class="basis-full text-[13px] text-bad"></p>
                    </form>
                </section>
            </template>
        </div>

        {{-- Weekly meetings --}}
        <aside class="flex min-w-0 flex-col gap-6">
            <section>
                <h2 class="mb-2 text-base font-semibold">Jadwal rapat mingguan</h2>
                <div class="rounded-xl border border-line">
                    <ul>
                        <template x-for="r in rapat" :key="r.id">
                            <li class="flex items-start gap-3 border-b border-line p-4">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium" x-text="scopeLabel(r.scope)"></p>
                                    <p class="text-[13px] text-muted" x-text="'Setiap ' + dayName(r.day) + ' · ' + r.time + ' · ' + (r.place || 'Tempat belum diisi')"></p>
                                </div>
                                <button type="button" x-show="canEdit" @click="removeRapat(r.id)" class="px-1 text-muted hover:text-ink" :aria-label="'Hapus ' + scopeLabel(r.scope)">×</button>
                            </li>
                        </template>
                    </ul>
                    <p x-show="rapat.length === 0" class="border-b border-line p-4 text-muted last:border-b-0" x-text="canEdit ? 'Belum ada jadwal rapat. Tambahkan rapat mingguan pertama di bawah.' : 'Belum ada jadwal rapat.'"></p>

                    <form x-show="canEdit" @submit.prevent="addRapat()" class="grid grid-cols-2 gap-3 p-4">
                        <label class="col-span-2">
                            <span class="mb-1.5 block font-medium">Rapat</span>
                            <select x-model="rapatDraft.scope" class="field">
                                <option value="pleno">Rapat Pleno (semua panitia)</option>
                                <option value="koor">Rapat Koordinator dan HOD/PJ</option>
                                <template x-for="div in divisions" :key="div.name">
                                    <option :value="'div:' + div.name" x-text="'Rapat divisi ' + div.name"></option>
                                </template>
                            </select>
                        </label>
                        <label>
                            <span class="mb-1.5 block font-medium">Hari</span>
                            <select x-model="rapatDraft.day" class="field">
                                <template x-for="w in weekdays" :key="w.day">
                                    <option :value="w.day" x-text="w.name"></option>
                                </template>
                            </select>
                        </label>
                        <label>
                            <span class="mb-1.5 block font-medium">Jam</span>
                            <input type="time" x-model="rapatDraft.time" class="field">
                        </label>
                        <label class="col-span-2">
                            <span class="mb-1.5 block font-medium">Tempat <span class="font-normal text-muted">(opsional)</span></span>
                            <input type="text" x-model="rapatDraft.place" maxlength="80" placeholder="mis. Zoom atau Ruang Himpunan" class="field">
                        </label>
                        <button type="submit" class="col-span-2 h-9 rounded-[10px] bg-accent px-4 font-medium text-white">Tambah jadwal</button>
                        <p x-show="rapatError" x-text="rapatError" role="alert" class="col-span-2 text-[13px] text-bad"></p>
                    </form>
                </div>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold">Rapat berikutnya</h2>
                <ul class="rounded-xl border border-line">
                    <template x-for="m in meetings.slice(0, 6)" :key="m.date + m.id">
                        <li class="flex items-start gap-3 border-b border-line p-4 last:border-b-0">
                            <span class="w-16 shrink-0 tabular-nums text-muted" x-text="dayName(new Date(m.date + 'T00:00:00').getDay()).slice(0, 3) + ', ' + fmtShort(m.date)"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block" x-text="scopeLabel(m.scope)"></span>
                                <span class="block text-[13px] tabular-nums text-muted" x-text="m.time + (m.place ? ' · ' + m.place : '')"></span>
                            </span>
                        </li>
                    </template>
                </ul>
                <p x-show="meetings.length === 0" class="rounded-xl border border-line p-4 text-muted"
                   x-text="daysLeft < 0 ? 'Hari-H sudah lewat.' : 'Belum ada rapat terjadwal.'"></p>
                <p x-show="meetings.length > 6" class="mt-2 text-[13px] tabular-nums text-muted" x-text="'dan ' + (meetings.length - 6) + ' rapat lagi sampai Hari-H'"></p>
            </section>
        </aside>
    </div>
</div>
@endsection
