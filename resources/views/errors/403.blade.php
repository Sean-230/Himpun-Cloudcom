@extends('layouts.app')
@section('title', 'Tidak bisa dibuka — Himpun')

@php
    use App\Support\Access;

    $role = Access::me();
@endphp

@section('content')
<h1 class="text-[22px] font-semibold leading-tight">Halaman ini tidak tersedia untukmu</h1>
<p class="mt-1 max-w-xl text-muted">Kamu sedang melihat Himpun sebagai {{ $role['label'] }} ({{ $role['name'] }}), dan peran itu tidak punya akses ke halaman ini. Ganti peran di pojok kanan atas kalau memang perlu.</p>
<a href="{{ route(Access::home($role)) }}" class="mt-5 inline-flex h-9 items-center rounded-[10px] bg-accent px-4 font-medium text-white">Kembali ke beranda</a>
@endsection
