<div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-gray-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Ruang Ujian Siswa</h1>
            <p class="text-sm text-gray-600 mt-1">
                Selamat datang, <strong class="text-indigo-600 font-semibold">{{ auth()->user()->name }}</strong>. Silakan pilih ujian yang tersedia atau lanjutkan pengerjaan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Status: Siswa Aktif
            </span>
        </div>
    </div>

    <!-- 1. Ujian Sedang Berlangsung (Active Attempts) -->
    @if($activeAttempts->isNotEmpty())
        <div class="mb-10">
            <div class="flex items-center gap-2 mb-4">
                <span class="flex h-3 w-3 rounded-full bg-amber-500 animate-ping"></span>
                <h2 class="text-lg font-bold text-gray-900">Ujian Sedang Berlangsung</h2>
            </div>

            <div class="space-y-4">
                @foreach($activeAttempts as $attempt)
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50 border-2 border-amber-300 p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-amber-500 text-white shadow-sm">
                                        Sedang Berlangsung
                                    </span>
                                    <span class="text-xs font-mono text-gray-500">{{ $attempt->exam->code }}</span>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $attempt->exam->title }}</h3>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs sm:text-sm text-gray-600">
                                    <span>Dimulai: <strong>{{ $attempt->started_at?->format('H:i:s') }}</strong></span>
                                    <span>&bull;</span>
                                    <span>Jumlah Soal: <strong>{{ $attempt->exam->questions->count() }} butir</strong></span>
                                    <span>&bull;</span>
                                    <span class="text-amber-700 font-medium">Batas Selesai: <strong>{{ $attempt->ended_at?->format('H:i:s') }}</strong></span>
                                </div>
                            </div>

                            <div>
                                <a href="{{ route('exam.take', $attempt) }}"
                                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-sm bg-gradient-to-r from-emerald-600 to-teal-600 text-white hover:from-emerald-500 hover:to-teal-500 shadow-md shadow-emerald-500/20 transition transform hover:-translate-y-0.5">
                                    <span>Lanjutkan Mengerjakan Soal</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2. Daftar Ujian Tersedia (Available Exams) -->
    <div class="mb-10">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-900">Daftar Paket Ujian Tersedia</h2>
            <p class="text-xs text-gray-500">Pilih paket ujian berikut untuk memulai pengerjaan.</p>
        </div>

        @if($exams->isEmpty())
            <div class="bg-gray-50 rounded-2xl border border-dashed border-gray-300 p-8 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-sm font-semibold text-gray-700">Tidak ada paket ujian baru saat ini</p>
                <p class="text-xs text-gray-500 mt-0.5">Semua ujian yang tersedia sudah Anda kerjakan atau belum dipublikasikan oleh guru.</p>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($exams as $exam)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 hover:border-indigo-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between p-6">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div>
                                    <span class="text-xs font-mono font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                        {{ $exam->code }}
                                    </span>
                                    <h3 class="text-lg font-bold text-gray-900 mt-2 leading-snug">{{ $exam->title }}</h3>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                    Tersedia
                                </span>
                            </div>

                            <p class="text-xs text-gray-500 line-clamp-2 mb-4 leading-relaxed">
                                {{ $exam->description ?? 'Paket ujian CBT online.' }}
                            </p>

                            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100 mb-4">
                                <div>
                                    <span class="text-gray-400 block">Durasi</span>
                                    <strong class="text-gray-800 font-semibold">{{ $exam->duration_minutes }} Menit</strong>
                                </div>
                                <div>
                                    <span class="text-gray-400 block">Jumlah Soal</span>
                                    <strong class="text-gray-800 font-semibold">{{ $exam->questions_count }} Butir</strong>
                                </div>
                                <div>
                                    <span class="text-gray-400 block">Passing Grade</span>
                                    <strong class="text-emerald-600 font-semibold">{{ $exam->pass_mark ?? 70 }} Poin</strong>
                                </div>
                                <div>
                                    <span class="text-gray-400 block">Percobaan</span>
                                    <strong class="text-gray-800 font-semibold">{{ $exam->completed_attempts_count }}/{{ $exam->max_attempts ?? 'Bebas' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100">
                            <a href="{{ route('exam.instructions', $exam) }}"
                               class="w-full inline-flex justify-center items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm shadow-indigo-500/20 transition">
                                <span>Lihat Petunjuk & Mulai Ujian</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 3. Riwayat & Hasil Ujian (Completed Attempts) -->
    @if($completedAttempts->isNotEmpty())
        <div class="mb-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-gray-900">Riwayat & Hasil Nilai Ujian</h2>
                <p class="text-xs text-gray-500">Hasil dan nilai dari ujian yang telah Anda selesaikan.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @foreach($completedAttempts as $attempt)
                        <?php
                            $isPassed = $attempt->exam->pass_mark ? ($attempt->score >= $attempt->exam->pass_mark) : true;
                        ?>
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50 transition">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-gray-900 text-sm sm:text-base">{{ $attempt->exam->title }}</h4>
                                    <span class="text-xs font-mono text-gray-400">({{ $attempt->exam->code }})</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span>Selesai: {{ $attempt->submitted_at?->format('d M Y, H:i') ?? '-' }}</span>
                                    <span>&bull;</span>
                                    <span>Status: {{ $attempt->force_submitted_at ? 'Waktu Habis' : 'Dikirim Selesai' }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <div class="text-lg sm:text-xl font-extrabold {{ $isPassed ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ number_format($attempt->score, 1) }}%
                                    </div>
                                    <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded-full {{ $isPassed ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $isPassed ? 'LULUS' : 'BELUM LULUS' }}
                                    </span>
                                </div>

                                <div>
                                    <a href="{{ route('exam.result', $attempt) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                        <span>Detail Hasil</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
