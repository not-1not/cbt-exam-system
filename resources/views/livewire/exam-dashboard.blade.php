<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Ujian</h1>
        <p class="text-gray-600 mt-1">Pilih ujian yang tersedia untuk Anda ikuti</p>
    </div>

    @if($exams->isEmpty())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Tidak ada ujian tersedia</h3>
            <p class="mt-1 text-gray-500">Saat ini tidak ada ujian yang dapat Anda ikuti.</p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($exams as $exam)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $exam->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $exam->code }}</p>
                        </div>
                        @if($exam->attempts_count > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Percobaan: {{ $exam->attempts_count }}/{{ $exam->max_attempts ?? "-" }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2 text-sm text-gray-600">
                        <p>{{ $exam->description ?? "-" }}</p>
                        <p><strong>Durasi:</strong> {{ $exam->duration_minutes }} menit</p>
                        <p><strong>Jumlah Soal:</strong> {{ $exam->questions_count }}</p>
                        @if($exam->max_attempts)
                            <p><strong>Maks Percobaan:</strong> {{ $exam->max_attempts }}</p>
                        @endif
                        @if($exam->pass_mark)
                            <p><strong>Nilai Lulus:</strong> {{ $exam->pass_mark }}</p>
                        @endif
                        @if($exam->start_at || $exam->end_at)
                            <p><strong>Periode:</strong>
                                {{ $exam->start_at ? $exam->start_at->format("d M Y H:i") : "Sekarang" }} -
                                {{ $exam->end_at ? $exam->end_at->format("d M Y H:i") : "Tidak dibatasi" }}
                            </p>
                        @endif
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="{{ route("exam.instructions", $exam) }}"
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Lihat Instruksi
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
