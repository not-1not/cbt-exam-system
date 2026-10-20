<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CBT Exam System - Sistem Ujian Berbasis Komputer</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="min-h-full bg-slate-900 text-slate-100 antialiased flex flex-col justify-between selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight text-white flex items-center gap-2">
                        CBT Exam System
                        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-medium">v1.0</span>
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <div class="flex items-center gap-3">
                        <span class="text-xs sm:text-sm text-slate-300">
                            Masuk sebagai: <strong class="text-white">{{ auth()->user()->name }}</strong>
                        </span>
                        @if(auth()->user()->hasRole('student'))
                            <a href="{{ route('exam.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-500 transition">
                                Ruang Ujian
                            </a>
                        @else
                            <a href="/admin" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-500 transition">
                                Panel Admin
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition">
                                Keluar (Logout)
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white transition">
                        Masuk Siswa
                    </a>
                    <a href="/admin" class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-500 transition shadow-sm">
                        Panel Admin
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1 py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
        <!-- Hero Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-medium mb-4">
                <span class="flex h-2 w-2 rounded-full bg-indigo-400 animate-pulse"></span>
                Sistem Ujian Berbasis Komputer & Online
            </div>
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                Pelaksanaan Ujian Cepat, Aman, & Terintegrasi
            </h2>
            <p class="mt-4 text-base sm:text-lg text-slate-400 leading-relaxed">
                Platform CBT lengkap dengan timer ujian real-time, navigasi soal fleksibel, deteksi anti-curang, dan panel manajemen ujian untuk guru & admin.
            </p>
        </div>

        <!-- 2 Main Access Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto mb-16">
            
            <!-- Student Portal Card -->
            <div class="relative group rounded-2xl bg-gradient-to-b from-slate-800/90 to-slate-800/40 p-8 border border-slate-700/80 hover:border-emerald-500/50 transition-all duration-300 shadow-xl flex flex-col justify-between">
                <div class="absolute -top-3 right-6 px-3 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    Livewire v3
                </div>
                <div>
                    <div class="h-14 w-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Portal Siswa (Peserta Ujian)</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6">
                        Masuk untuk melihat daftar ujian yang tersedia, mulai mengerjakan soal, membaca petunjuk ujian, dan mengecek hasil nilai secara langsung.
                    </p>
                    
                    <div class="rounded-xl bg-slate-900/60 p-4 border border-slate-700/50 mb-6 text-xs text-slate-300">
                        <div class="font-semibold text-slate-200 mb-2 flex items-center justify-between">
                            <span>Akun Demo Siswa:</span>
                            <span class="text-[10px] text-emerald-400 uppercase font-mono">Role: Student</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Email:</span>
                            <code class="text-emerald-300 font-mono">student@exam.test</code>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span class="text-slate-400">Password:</span>
                            <code class="text-emerald-300 font-mono">password</code>
                        </div>
                    </div>
                </div>

                <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition shadow-lg shadow-emerald-600/20 text-sm">
                    <span>Masuk ke Portal Siswa</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <!-- Admin / Teacher / Proctor Card -->
            <div class="relative group rounded-2xl bg-gradient-to-b from-slate-800/90 to-slate-800/40 p-8 border border-slate-700/80 hover:border-indigo-500/50 transition-all duration-300 shadow-xl flex flex-col justify-between">
                <div class="absolute -top-3 right-6 px-3 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    Filament v4
                </div>
                <div>
                    <div class="h-14 w-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Panel Admin & Pengawas</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6">
                        Pusat kendali ujian untuk Administrator, Guru, dan Pengawas. Kelola bank soal, jadwal ujian, durasi, opsi acak, serta pantau siswa secara langsung.
                    </p>
                    
                    <div class="rounded-xl bg-slate-900/60 p-4 border border-slate-700/50 mb-6 text-xs text-slate-300">
                        <div class="font-semibold text-slate-200 mb-2 flex items-center justify-between">
                            <span>Akun Demo Admin / Guru:</span>
                            <span class="text-[10px] text-indigo-400 uppercase font-mono">Role: Admin / Teacher</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Email:</span>
                            <code class="text-indigo-300 font-mono">admin@exam.test</code>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span class="text-slate-400">Password:</span>
                            <code class="text-indigo-300 font-mono">password</code>
                        </div>
                    </div>
                </div>

                <a href="/admin" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition shadow-lg shadow-indigo-600/20 text-sm">
                    <span>Buka Panel Admin (/admin)</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

        </div>

        <!-- Role Credentials Table / Reference -->
        <div class="max-w-4xl mx-auto rounded-2xl bg-slate-800/40 border border-slate-800 p-6 sm:p-8">
            <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Daftar Seluruh Akun Uji Coba Bawaan
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm text-slate-300">
                    <thead class="border-b border-slate-700 text-slate-400 text-xs uppercase font-medium">
                        <tr>
                            <th class="py-3 px-2">Role</th>
                            <th class="py-3 px-2">Email</th>
                            <th class="py-3 px-2">Password</th>
                            <th class="py-3 px-2">Hak Akses</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <tr>
                            <td class="py-3 px-2 font-medium text-white flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-red-400"></span> Administrator
                            </td>
                            <td class="py-3 px-2 font-mono text-slate-300">admin@exam.test</td>
                            <td class="py-3 px-2 font-mono text-slate-300">password</td>
                            <td class="py-3 px-2 text-indigo-400">Panel Admin (Full Control)</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-2 font-medium text-white flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-amber-400"></span> Guru (Teacher)
                            </td>
                            <td class="py-3 px-2 font-mono text-slate-300">teacher@exam.test</td>
                            <td class="py-3 px-2 font-mono text-slate-300">password</td>
                            <td class="py-3 px-2 text-indigo-400">Panel Admin (Kelola Soal & Ujian)</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-2 font-medium text-white flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-blue-400"></span> Pengawas (Proctor)
                            </td>
                            <td class="py-3 px-2 font-mono text-slate-300">proctor@exam.test</td>
                            <td class="py-3 px-2 font-mono text-slate-300">password</td>
                            <td class="py-3 px-2 text-indigo-400">Panel Admin (Pantau Sesi Siswa)</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-2 font-medium text-white flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Siswa (Student)
                            </td>
                            <td class="py-3 px-2 font-mono text-slate-300">student@exam.test</td>
                            <td class="py-3 px-2 font-mono text-slate-300">password</td>
                            <td class="py-3 px-2 text-emerald-400">Portal Ujian Siswa (/login)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} CBT Exam System. Dikembangkan dengan Laravel 12 & Filament v4.</p>
    </footer>

</body>
</html>
