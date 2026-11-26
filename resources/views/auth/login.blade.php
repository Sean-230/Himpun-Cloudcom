<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Himpun</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh items-center justify-center bg-frame p-4 font-sans text-sm text-ink antialiased">
    <div class="w-full max-w-sm rounded-2xl border border-line bg-white p-6 shadow-xs">
        <div class="mb-6 flex items-center gap-2.5">
            <svg class="size-7" viewBox="0 0 24 24" aria-hidden="true">
                <rect x="5" y="4" width="4" height="16" rx="1" fill="#0a1931"/>
                <rect x="15" y="4" width="4" height="16" rx="1" fill="#0a1931"/>
                <rect x="9" y="10.5" width="6" height="3" fill="#0a1931"/>
            </svg>
            <div>
                <h1 class="text-base font-semibold leading-tight">Masuk ke Himpun</h1>
                <p class="text-xs text-muted">SU IMT · Periode 2025/2026</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-xs font-medium text-ink">Email Ciputra</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@student.ciputra.ac.id"
                    required
                    autofocus
                    class="h-9 w-full rounded-lg border border-line bg-white px-3 text-sm outline-none focus:border-ink"
                >
            </div>

            <div>
                <label for="password" class="mb-1 block text-xs font-medium text-ink">Kata Sandi</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="••••••••"
                    required
                    class="h-9 w-full rounded-lg border border-line bg-white px-3 text-sm outline-none focus:border-ink"
                >
            </div>

            <label class="flex items-center gap-2 text-xs text-muted">
                <input type="checkbox" name="remember" class="rounded border-line">
                Ingat sesi saya
            </label>

            <button type="submit" class="mt-1 h-9 w-full rounded-lg bg-[#0a1931] px-4 font-medium text-white hover:opacity-90">
                Masuk
            </button>
        </form>

        @if (app()->isLocal())
            <div class="mt-5 border-t border-line pt-4 text-center text-xs text-muted">
                Mode lokal: akun default memakai password <code class="rounded bg-black/5 px-1 py-0.5 text-ink">password</code>
                atau <a href="{{ route('dashboard') }}" class="font-medium text-ink underline">lanjut tanpa login</a>.
            </div>
        @endif
    </div>
</body>
</html>