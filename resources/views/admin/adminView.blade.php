<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Console') — Himpun SU IMT UC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#F6FAFD] text-[#0A1931] antialiased selection:bg-[#B3CFE5] selection:text-[#0A1931]"
      x-data="{
          activeRole: '{{ request('role', 'ketupel') }}',
          activeProker: 'Techfest UC 2026',
          sidebarOpen: false
      }">

    <div class="min-h-screen flex">
        <!-- Sidebar (#0A1931) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed lg:static inset-y-0 left-0 z-40 w-68 bg-[#0A1931] text-white transition-transform duration-200 flex flex-col justify-between border-r border-[#1A3D63]">
            
            <div>
                <!-- Brand Header -->
                <div class="px-6 py-5 border-b border-[#1A3D63] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-[#4A7FA7] flex items-center justify-center font-bold text-white shadow-sm">
                            H
                        </div>
                        <div>
                            <span class="font-bold tracking-tight text-lg block leading-none">Himpun</span>
                            <span class="text-[11px] text-[#B3CFE5] tracking-wider uppercase">SU IMT • Univ. Ciputra</span>
                        </div>
                    </div>
                    <button @click="sidebarOpen = false" class="lg:hidden text-[#B3CFE5] hover:text-white">✕</button>
                </div>

                <!-- Active Proker Context Box -->
                <div class="px-4 py-4">
                    <div class="bg-[#1A3D63]/70 border border-[#4A7FA7]/40 rounded-xl p-3">
                        <p class="text-[10px] uppercase tracking-wider text-[#B3CFE5] font-semibold">Workspace Aktif</p>
                        <select x-model="activeProker"
                                class="mt-1 w-full bg-[#0A1931] text-white text-xs font-medium rounded-lg px-2.5 py-2 border border-[#4A7FA7]/50 focus:outline-none focus:border-[#B3CFE5]">
                            <option value="Techfest UC 2026">🚀 Techfest UC 2026</option>
                            <option value="IMT Orientation Camp">🏕️ IMT Orientation Camp</option>
                            <option value="National Hackathon UC">💻 National Hackathon UC</option>
                        </select>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="px-3 space-y-1 text-sm">
                    <p class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-[#B3CFE5]/70">Command Center</p>
                    
                    <a href="#overview"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#1A3D63] text-white font-medium border-l-4 border-[#B3CFE5]">
                        <span>📊</span>
                        <span>Dashboard & Ringkasan</span>
                    </a>

                    <a href="#hod-governance" x-show="activeRole === 'hod' || activeRole === 'dosen'"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#B3CFE5] hover:bg-[#1A3D63]/60 hover:text-white transition">
                        <span>🏛️</span>
                        <span>Assign Ketupel & PJ</span>
                    </a>

                    <a href="#koor-jobdesc" x-show="activeRole === 'ketupel' || activeRole === 'hod' || activeRole === 'dosen'"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#B3CFE5] hover:bg-[#1A3D63]/60 hover:text-white transition">
                        <span>🎯</span>
                        <span>Koordinator & Jobdesc</span>
                    </a>

                    <a href="#proker-timeline"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#B3CFE5] hover:bg-[#1A3D63]/60 hover:text-white transition">
                        <span>🗓️</span>
                        <span>Timeline & Hari-H</span>
                    </a>

                    <a href="#dosen-advisory" x-show="activeRole === 'dosen' || activeRole === 'hod'"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#B3CFE5] hover:bg-[#1A3D63]/60 hover:text-white transition">
                        <span>🎓</span>
                        <span>Advisory & Approval LPJ</span>
                    </a>
                </nav>
            </div>

            <!-- Strict Communication Line Reminder -->
            <div class="p-4 border-t border-[#1A3D63]">
                <div class="bg-[#1A3D63]/50 rounded-lg p-3 text-[11px] text-[#B3CFE5]">
                    <p class="font-semibold text-white mb-1">Alur Komunikasi Resmi</p>
                    <p class="leading-relaxed">Member → Koor → HOD/PJ → Ketupel ⇢ Dosen Pembina</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-[#B3CFE5]/60 px-6 py-3.5 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-[#0A1931] text-white">☰</button>
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-[#0A1931]" x-text="activeProker"></h1>
                        <p class="text-xs text-[#1A3D63]">Student Union Informatics — Universitas Ciputra</p>
                    </div>
                </div>

                <!-- Role Switcher Pill (Simulasi RBAC Dosen / HOD / Ketupel) -->
                <div class="flex items-center gap-2 bg-[#F6FAFD] p-1 rounded-xl border border-[#B3CFE5]">
                    <span class="text-[11px] font-semibold text-[#1A3D63] px-2 hidden sm:inline">View As:</span>
                    <button @click="activeRole = 'hod'"
                            :class="activeRole === 'hod' ? 'bg-[#0A1931] text-white shadow-xs' : 'text-[#1A3D63] hover:bg-[#B3CFE5]/40'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                        🏛️ HOD
                    </button>
                    <button @click="activeRole = 'ketupel'"
                            :class="activeRole === 'ketupel' ? 'bg-[#4A7FA7] text-white shadow-xs' : 'text-[#1A3D63] hover:bg-[#B3CFE5]/40'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                        👑 Ketupel
                    </button>
                    <button @click="activeRole = 'dosen'"
                            :class="activeRole === 'dosen' ? 'bg-[#1A3D63] text-white shadow-xs' : 'text-[#1A3D63] hover:bg-[#B3CFE5]/40'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                        🎓 Dosen Pembina
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 max-w-7xl w-full mx-auto space-y-6">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>