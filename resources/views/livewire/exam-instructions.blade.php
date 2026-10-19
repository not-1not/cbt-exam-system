<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $exam->title }}</h1>
        <p class="text-gray-600 mt-1">{{ $exam->code }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Deskripsi Ujian</h2>
            <p class="mt-2 text-gray-600 whitespace-pre-wrap">{{ $exam->description ?? "Tidak ada deskripsi." }}</p>
        </div>

        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-900">Aturan Ujian</h2>
            <ul class="mt-3 space-y-2 text-gray-700 list-disc list-inside">
                <li>Ujian berdurasi <strong>{{ $exam->duration_minutes }} menit</strong>.</li>
                <li>Total soal: <strong>{{ $exam->questions_count }}</strong> soal.</li>
                <li>Setiap soal memiliki bobot nilai yang berbeda.</li>
                @if($exam->shuffle_questions)
                    <li>Urutan soal diacak untuk setiap peserta.</li>
                @endif
                @if($exam->shuffle_options)
                    <li>Urutan pilihan jawaban diacak untuk setiap peserta.</li>
                @endif
                <li>Jawaban dapat diubah kapan saja sebelum mengirim ujian.</li>
                <li>Anda dapat menandai soal untuk direview nanti.</li>
                <li>Ujian akan otomatis terkirim saat waktu habis.</li>
            </ul>
        </div>

        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Informasi Percobaan</h2>
            <dl class="mt-3 grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2 text-gray-700">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Durasi</dt>
                    <dd class="mt-1 text-lg font-medium">{{ $exam->duration_minutes }} menit</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Jumlah Soal</dt>
                    <dd class="mt-1 text-lg font-medium">{{ $exam->questions_count }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Maksimal Percobaan</dt>
                    <dd class="mt-1 text-lg font-medium">{{ $exam->max_attempts ?? "Tidak dibatasi" }}</dd>
                </div>
                @if($exam->pass_mark)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nilai Lulus (Pass Mark)</dt>
                        <dd class="mt-1 text-lg font-medium">{{ $exam->pass_mark }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="p-6 bg-red-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-red-900">Peringatan Penting</h2>
            <ul class="mt-3 space-y-2 text-red-700 list-disc list-inside">
                <li>Pastikan koneksi internet stabil selama ujian berlangsung.</li>
                <li>Jangan menutup atau me-refresh halaman ujian.</li>
                <li>Waktu ujian terus berjalan meskipun Anda keluar dari halaman.</li>
                <li>Sistem akan otomatis mengirim jawaban saat waktu habis.</li>
            </ul>
        </div>

        <div class="p-6">
            <div class="flex justify-end space-x-3">
                <a href="{{ route("exam.dashboard") }}"
                   class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Kembali
                </a>
                <button wire:click="startExam"
                        class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Mulai Ujian
                </button>
            </div>
        </div>
    </div>
</div>
