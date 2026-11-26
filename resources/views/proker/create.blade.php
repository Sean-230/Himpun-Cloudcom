@extends('layouts.app')
@section('title', 'Buat proker — Himpun')

@php
    // $members and $divisions come from App\Support\DummyData (see the route).
    $roleColumns = [['koor', 'Koordinator', 'Tambah koordinator'], ['anggota', 'Anggota', 'Tambah anggota']];
@endphp

@section('content')
<div x-data="prokerForm(@js($members), @js($divisions))">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('proker.index') }}" class="text-[13px] text-muted hover:text-ink">← Proker</a>
            <h1 class="mt-1 text-[22px] font-semibold leading-tight">Buat proker</h1>
            <p class="mt-0.5 text-muted">Sebagai HOD · SU IMT · Periode 2025/2026</p>
        </div>
        <a href="{{ route('proker.index') }}" class="inline-flex h-9 items-center rounded-[10px] px-4 ring-1 ring-line hover:bg-black/5">Batal</a>
    </div>

    <form x-show="!done" @submit.prevent="submit()" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_384px]">
        <div class="flex min-w-0 flex-col gap-4">
            <section class="rounded-xl border border-line p-5">
                <h2 class="text-base font-semibold">Informasi proker</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="mb-2 block font-medium">Nama proker</span>
                        <input type="text" x-model="form.name" placeholder="mis. Techfest UC 2026" maxlength="100" class="field" :class="err('name') && 'border-bad!'" :aria-invalid="!!err('name')">
                        <p x-show="err('name')" x-text="err('name')" role="alert" data-error class="mt-1.5 text-[13px] text-bad"></p>
                    </label>

                    <fieldset class="sm:col-span-2">
                        <legend class="mb-2 font-medium">Jenis</legend>
                        <div class="flex flex-wrap gap-5">
                            <label class="flex items-center gap-2"><input type="radio" value="new" x-model="form.type" class="accent-accent"> Baru</label>
                            <label class="flex items-center gap-2"><input type="radio" value="repeat" x-model="form.type" class="accent-accent"> Ulangi dari periode lalu</label>
                        </div>
                    </fieldset>

                    <label x-show="form.type === 'repeat'" x-cloak class="sm:col-span-2">
                        <span class="mb-2 block font-medium">Proker sebelumnya</span>
                        <select x-model="form.previous" class="field" :class="err('previous') && 'border-bad!'" :aria-invalid="!!err('previous')">
                            <option value="">Pilih proker periode lalu</option>
                            <option>Techfest UC 2025</option>
                            <option>IMT Orientation Camp 2025</option>
                            <option>IMT Class Meeting 2025</option>
                        </select>
                        <p x-show="err('previous')" x-text="err('previous')" role="alert" data-error class="mt-1.5 text-[13px] text-bad"></p>
                    </label>

                    <label>
                        <span class="mb-2 block font-medium">Hari-H</span>
                        <input type="date" x-model="form.hariH" :min="today" class="field" :class="err('hariH') && 'border-bad!'" :aria-invalid="!!err('hariH')">
                        <p x-show="err('hariH')" x-text="err('hariH')" role="alert" data-error class="mt-1.5 text-[13px] text-bad"></p>
                    </label>
                    <label>
                        <span class="mb-2 block font-medium">Lokasi <span class="font-normal text-muted">(opsional)</span></span>
                        <input type="text" x-model="form.lokasi" placeholder="mis. Auditorium UC" class="field">
                    </label>

                    <label>
                        <span class="mb-2 block font-medium">Ketupel <span class="font-normal text-muted">(opsional)</span></span>
                        <select x-model="form.ketupel" @change="claim('ketupel')" class="field" :class="err('ketupel') && 'border-bad!'" :aria-invalid="!!err('ketupel')">
                            <option value="">Belum ditunjuk</option>
                            <template x-for="m in members" :key="m.id">
                                <option :value="m.id"
                                        x-text="m.name + ' · ' + m.divisi + (heldBesides(m.id, 'Ketupel').length ? ' · sekarang ' + heldBesides(m.id, 'Ketupel')[0] : '')"></option>
                            </template>
                        </select>
                        <p x-show="err('ketupel')" x-text="err('ketupel')" role="alert" data-error class="mt-1.5 text-[13px] text-bad"></p>
                        <p x-show="noticeAt('ketupel')" x-text="noticeAt('ketupel')" role="status" class="mt-1.5 text-[13px] text-muted"></p>
                    </label>
                    <label>
                        <span class="mb-2 block font-medium">Penanggung jawab (PJ) <span class="font-normal text-muted">(opsional)</span></span>
                        <select x-model="form.pj" @change="claim('pj')" class="field" :class="err('pj') && 'border-bad!'" :aria-invalid="!!err('pj')">
                            <option value="">Belum ditunjuk</option>
                            <template x-for="m in members.filter((x) => x.hod)" :key="m.id">
                                <option :value="m.id"
                                        x-text="m.name + ' · ' + m.divisi + (heldBesides(m.id, 'PJ').length ? ' · sekarang ' + heldBesides(m.id, 'PJ')[0] : '')"></option>
                            </template>
                        </select>
                        <p x-show="err('pj')" x-text="err('pj')" role="alert" data-error class="mt-1.5 text-[13px] text-bad"></p>
                        <p x-show="noticeAt('pj')" x-text="noticeAt('pj')" role="status" class="mt-1.5 text-[13px] text-muted"></p>
                    </label>
                </div>
            </section>

            <section class="rounded-xl border border-line p-5">
                <h2 class="text-base font-semibold">Divisi panitia</h2>
                <p class="mt-0.5 text-muted">Centang divisi yang dibutuhkan. Koordinator dan anggota boleh dikosongkan dan diisi nanti. Satu orang hanya bisa punya satu peran di satu proker; memilihnya lagi memindahkan perannya.</p>
                <p x-show="err('divisions')" x-text="err('divisions')" role="alert" data-error class="mt-1.5 text-[13px] text-bad"></p>
                <p x-show="err('people')" x-text="err('people')" role="alert" data-error class="mt-1.5 text-[13px] text-bad"></p>
                <p x-show="err('jobdesc')" x-text="err('jobdesc')" role="alert" data-error class="mt-1.5 text-[13px] text-bad"></p>

                <div class="mt-4 flex flex-col gap-3">
                    <template x-for="div in divisions" :key="div.name">
                        <div class="rounded-xl border border-line" :class="div.on ? 'bg-frame/60' : ''">
                            <label class="flex cursor-pointer items-start gap-3 p-4">
                                <input type="checkbox" x-model="div.on" class="mt-0.5 size-4 accent-accent">
                                <span class="min-w-0 flex-1">
                                    <span class="block font-medium" x-text="div.name"></span>
                                    <span class="block text-[13px] text-muted" x-text="div.desc"></span>
                                </span>
                                <span x-show="div.on" x-cloak class="shrink-0 text-[13px] tabular-nums text-muted"
                                      x-text="div.koor.length + ' koordinator · ' + div.anggota.length + ' anggota · ' + div.jobdesc.length + ' jobdesc'"></span>
                            </label>

                            <div x-show="div.on" x-cloak class="grid gap-4 border-t border-line p-4 md:grid-cols-2">
                                @foreach ($roleColumns as [$key, $label, $addLabel])
                                    <div>
                                        <p class="mb-2 font-medium">{{ $label }} <span class="font-normal text-muted">(opsional)</span></p>
                                        <ul class="mb-2 flex flex-wrap gap-1.5">
                                            <template x-for="id in div.{{ $key }}" :key="id">
                                                <li class="flex items-center gap-1.5 rounded-full py-0.5 pl-0.5 pr-1.5 ring-1 ring-line">
                                                    <span class="inline-flex size-6 items-center justify-center rounded-full text-[13px] font-medium"
                                                          :style="avatarStyle(person(id).name)" x-text="initials(person(id).name)"></span>
                                                    <span x-text="person(id).name"></span>
                                                    <span x-show="roles(id) >= 3" class="rounded-full bg-[#fbf0dc] px-1.5 text-[13px] text-warn"
                                                          x-text="roles(id) + ' peran'"></span>
                                                    <button type="button" @click="remove(div, '{{ $key }}', id)" class="px-1 text-muted hover:text-ink"
                                                            :aria-label="'Hapus ' + person(id).name">×</button>
                                                </li>
                                            </template>
                                        </ul>
                                        <p x-show="div.{{ $key }}.length === 0" class="mb-2 text-[13px] text-muted">Belum diisi. Bisa dilengkapi nanti.</p>
                                        <select class="field" aria-label="{{ $addLabel }} untuk divisi" @change="add(div, '{{ $key }}', $event.target.value); $event.target.value = ''">
                                            <option value="">{{ $addLabel }}</option>
                                            <template x-for="m in available(div, '{{ $key }}')" :key="m.id">
                                                <option :value="m.id" x-text="m.name + ' · ' + m.divisi + (holds(m.id).length ? ' · sekarang ' + holds(m.id)[0] : m.roles ? ' · ' + m.roles + ' peran aktif' : '')"></option>
                                            </template>
                                        </select>
                                    </div>
                                @endforeach
                                <p x-show="noticeAt(div.name)" x-text="noticeAt(div.name)" role="status" class="text-[13px] text-muted md:col-span-2"></p>

                                <div class="md:col-span-2">
                                    <p class="mb-2 font-medium">Jobdesc dan target <span class="font-normal text-muted">(opsional)</span></p>
                                    <ul class="flex flex-col gap-2">
                                        <template x-for="j in div.jobdesc" :key="j.id">
                                            <li class="flex items-center gap-2">
                                                <input type="text" x-model="j.title" maxlength="120" placeholder="Apa yang harus dikerjakan divisi ini" aria-label="Jobdesc" class="field"
                                                       :class="err('jobdesc') && !j.title.trim() && 'border-bad!'">
                                                <input type="number" x-model.number="j.h" min="-60" max="365" aria-label="Target, hari sebelum Hari-H" class="field w-16! shrink-0 px-2! text-center">
                                                <span class="hidden w-28 shrink-0 text-[13px] tabular-nums text-muted sm:block"
                                                      x-text="targetLabel(j.h) + (targetDate(j.h) ? ' · ' + targetDate(j.h) : '')"></span>
                                                <button type="button" @click="removeJobdesc(div, j.id)" class="px-1 text-muted hover:text-ink" aria-label="Hapus jobdesc">×</button>
                                            </li>
                                        </template>
                                    </ul>
                                    <p x-show="div.jobdesc.length === 0" class="text-[13px] text-muted">Belum ada jobdesc. Bisa ditambahkan nanti.</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-3">
                                        <button type="button" @click="addJobdesc(div)" class="h-8 rounded-lg px-3 text-[13px] ring-1 ring-line hover:bg-black/5">Tambah jobdesc</button>
                                        <span class="text-[13px] text-muted">Angka = hari sebelum Hari-H. Minus berarti sesudah Hari-H.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-4 flex gap-2">
                    <input type="text" x-model="custom" @keydown.enter.prevent="addCustom()" placeholder="Divisi lain, mis. Konsumsi" maxlength="40" class="field" aria-label="Nama divisi lain">
                    <button type="button" @click="addCustom()" class="h-10 shrink-0 rounded-[10px] px-4 ring-1 ring-line hover:bg-black/5">Tambah divisi</button>
                </div>
            </section>
        </div>

        <aside class="lg:sticky lg:top-0 lg:self-start">
            <section class="rounded-xl border border-line p-5">
                <h2 class="text-base font-semibold">Susunan panitia</h2>
                <p class="mt-3 font-medium" x-text="form.name.trim() || 'Proker tanpa nama'" :class="form.name.trim() ? '' : 'text-muted'"></p>
                <p class="text-[13px] text-muted" x-text="form.hariH ? 'Hari-H ' + fmtDate(form.hariH) : 'Hari-H belum diisi'"></p>

                <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5 border-t border-line pt-4">
                    <dt class="text-muted">Ketupel</dt>
                    <dd x-text="form.ketupel ? person(Number(form.ketupel)).name : 'Belum ditunjuk'" :class="form.ketupel ? '' : 'text-muted'"></dd>
                    <dt class="text-muted">PJ</dt>
                    <dd x-text="form.pj ? person(Number(form.pj)).name : 'Belum ditunjuk'" :class="form.pj ? '' : 'text-muted'"></dd>
                </dl>

                <div class="mt-4 border-t border-line pt-4">
                    <p x-show="active.length === 0" class="text-muted">Belum ada divisi dipilih.</p>
                    <ul class="flex flex-col gap-3">
                        <template x-for="div in active" :key="div.name">
                            <li>
                                <p class="font-medium" x-text="div.name"></p>
                                <p class="text-[13px]" :class="div.koor.length ? '' : 'text-muted'"
                                   x-text="div.koor.length ? 'Koordinator: ' + names(div.koor) : 'Belum ada koordinator'"></p>
                                <p class="text-[13px]" :class="div.anggota.length ? 'text-muted' : 'text-muted'"
                                   x-text="div.anggota.length ? div.anggota.length + ' anggota' : 'Belum ada anggota'"></p>
                            </li>
                        </template>
                    </ul>
                </div>

                <p class="mt-4 border-t border-line pt-4 tabular-nums text-muted"
                   x-text="totals.divisi + ' divisi · ' + totals.koor + ' koordinator · ' + totals.anggota + ' anggota · ' + totals.jobdesc + ' jobdesc'"></p>

                <button type="submit" class="mt-4 h-9 w-full rounded-[10px] bg-accent px-4 font-medium text-white">Buat proker</button>
                <ul x-show="tried && !canSubmit" x-cloak role="alert" class="mt-3 list-disc pl-5 text-[13px] text-bad">
                    <template x-for="(msg, key) in errors" :key="key"><li x-text="msg"></li></template>
                </ul>
            </section>
        </aside>
    </form>

    <section x-show="done" x-cloak class="mt-6 max-w-xl rounded-xl border border-line p-5">
        <h2 class="text-base font-semibold">Proker dibuat</h2>
        <p class="mt-2"><span class="font-medium" x-text="form.name"></span>, Hari-H <span x-text="fmtDate(form.hariH)"></span>.</p>
        <p class="mt-1 text-muted tabular-nums" x-text="totals.divisi + ' divisi · ' + totals.koor + ' koordinator · ' + totals.anggota + ' anggota · ' + totals.jobdesc + ' jobdesc. Yang masih kosong bisa dilengkapi nanti.'"></p>
        <p class="mt-3 text-[13px] text-muted">Ini pratinjau: data belum disimpan.</p>
        <div class="mt-4 flex gap-2">
            <a href="{{ route('proker.index') }}" class="inline-flex h-9 items-center rounded-[10px] bg-accent px-4 font-medium text-white">Ke daftar proker</a>
            <button type="button" @click="location.reload()" class="h-9 rounded-[10px] px-4 ring-1 ring-line hover:bg-black/5">Buat proker lain</button>
        </div>
    </section>
</div>
@endsection
