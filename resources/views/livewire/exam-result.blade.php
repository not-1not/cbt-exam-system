<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Hasil Ujian</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1 font-medium">{{ $attempt->exam->title }} <span class="font-mono text-xs text-gray-400">({{ $attempt->exam->code }})</span></p>
        </div>
        <div>
            <a href="{{ route('exam.dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-xs sm:text-sm font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <!-- Info Waktu & Pengiriman -->
        <div class="p-4 sm:p-6 border-b border-gray-200 bg-slate-50/70">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Pengiriman</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                            {{ $attempt->force_submitted_at ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $attempt->force_submitted_at ? 'Dikirim Otomatis (Waktu Habis)' : 'Selesai Dikirim' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Mulai Ujian</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $attempt->started_at?->format('d M Y, H:i') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Selesai Ujian</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $attempt->submitted_at?->format('d M Y, H:i') ?? ($attempt->ended_at?->format('d M Y, H:i') ?? '-') }}</dd>
                </div>
            </div>
        </div>

        <!-- 3 Cards: Nilai Akhir (Persen), Ringkasan Jawaban, Status Kelulusan -->
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Card 1: Nilai Akhir Persen -->
                <div class="text-center p-5 bg-gradient-to-br from-indigo-50 to-indigo-100/50 rounded-2xl border border-indigo-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-700 mb-1">Nilai Akhir</p>
                    <p class="text-4xl sm:text-5xl font-black text-indigo-600 tracking-tight">
                        {{ number_format($attempt->score, 2) }}<span class="text-2xl font-bold">%</span>
                    </p>
                    <p class="text-xs font-medium text-indigo-600/80 mt-2">Maksimal Nilai 100%</p>
                </div>

                <!-- Card 2: Rincian Soal Benar -->
                <div class="text-center p-5 bg-slate-50 rounded-2xl border border-slate-200/70">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Jumlah Jawaban</p>
                    <p class="text-4xl sm:text-5xl font-black text-slate-800 tracking-tight">
                        {{ $correctCount }}<span class="text-xl font-bold text-slate-400">/{{ $totalQuestions }}</span>
                    </p>
                    <p class="text-xs font-semibold text-slate-500 mt-2">
                        <span class="text-emerald-600 font-bold">{{ $correctCount }} Benar</span> &bull; 
                        <span class="text-red-500 font-bold">{{ $incorrectCount }} Salah</span> &bull; 
                        <span class="text-gray-400 font-medium">{{ $unansweredCount }} Kosong</span>
                    </p>
                </div>

                <!-- Card 3: Status Kelulusan -->
                <div class="text-center p-5 {{ $passStatus === 'lulus' ? 'bg-gradient-to-br from-emerald-50 to-emerald-100/50 border-emerald-200' : ($passStatus === 'tidak_lulus' ? 'bg-gradient-to-br from-red-50 to-red-100/50 border-red-200' : 'bg-slate-50 border-slate-200') }} rounded-2xl border">
                    <p class="text-xs font-bold uppercase tracking-wider {{ $passStatus === 'lulus' ? 'text-emerald-700' : ($passStatus === 'tidak_lulus' ? 'text-red-700' : 'text-gray-600') }} mb-1">
                        Status Kelulusan
                    </p>
                    @if($passStatus === 'lulus')
                        <p class="text-3xl sm:text-4xl font-black text-emerald-600 tracking-tight mt-1">LULUS</p>
                        <p class="text-xs font-medium text-emerald-700 mt-2">Nilai &ge; KKM ({{ number_format($attempt->exam->pass_mark, 0) }}%)</p>
                    @elseif($passStatus === 'tidak_lulus')
                        <p class="text-3xl sm:text-4xl font-black text-red-600 tracking-tight mt-1">TIDAK LULUS</p>
                        <p class="text-xs font-medium text-red-700 mt-2">Nilai &lt; KKM ({{ number_format($attempt->exam->pass_mark, 0) }}%)</p>
                    @else
                        <p class="text-3xl sm:text-4xl font-black text-gray-500 tracking-tight mt-1">SELESAI</p>
                        <p class="text-xs font-medium text-gray-500 mt-2">Tidak ada batas KKM</p>
                    @endif
                </div>
            </div>

            <!-- Rumus Penilaian Bar -->
            <div class="mt-5 p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-700">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 font-bold uppercase text-[10px]">Rumus Nilai</span>
                    <span class="font-medium">Rumus: <strong>(Jumlah Benar / Total Soal) &times; 100%</strong></span>
                </div>
                <div class="font-mono text-slate-800 bg-white px-3 py-1 rounded-lg border border-slate-200 shadow-sm font-semibold">
                    ({{ $correctCount }} / {{ $totalQuestions }}) &times; 100% = <strong class="text-indigo-600">{{ number_format($attempt->score, 2) }}%</strong>
                </div>
            </div>
        </div>

        @if($attempt->force_submitted_at)
            <div class="p-4 sm:p-5 border-b border-red-100 bg-red-50/70">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-xs sm:text-sm text-red-800 font-medium">Ujian dikirim otomatis karena batas waktu habis: <strong>{{ $attempt->force_reason }}</strong></p>
                </div>
            </div>
        @endif

        <!-- Detail Jawaban Siswa -->
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Rincian Jawaban Soal</h2>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left">Pertanyaan</th>
                            <th class="px-4 py-3 text-left">Jawaban Siswa</th>
                            <th class="px-4 py-3 text-left">Kunci Jawaban</th>
                            <th class="px-4 py-3 text-center w-24">Hasil</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($attempt->exam->questions as $index => $question)
                            <?php
                                $answer = $attempt->answers->firstWhere('question_id', $question->id);
                                $selectedOption = $answer?->option;
                                // Validate that the option belongs to this question
                                if ($selectedOption && $selectedOption->question_id !== $question->id) {
                                    $selectedOption = null;
                                }
                                $correctOption = $question->options->firstWhere('is_correct', true);
                                $isCorrect = (bool) ($answer?->is_correct ?? false) && $selectedOption !== null;
                                $hasAnswer = $selectedOption !== null || !empty($answer?->answer_text);

                                $optIdx = $selectedOption ? $question->options->search(fn($o) => $o->id === $selectedOption->id) : false;
                                $optLetter = $optIdx !== false ? chr(65 + $optIdx) : '-';

                                $correctIdx = $correctOption ? $question->options->search(fn($o) => $o->id === $correctOption->id) : false;
                                $correctLetter = $correctIdx !== false ? chr(65 + $correctIdx) : '-';
                            ?>
                            <tr class="{{ $isCorrect ? 'bg-emerald-50/40 hover:bg-emerald-50/70' : ($hasAnswer ? 'bg-red-50/40 hover:bg-red-50/70' : 'bg-slate-50/40 hover:bg-slate-50/70') }} transition">
                                <td class="px-4 py-3.5 font-bold text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 text-gray-800 max-w-xs sm:max-w-md">
                                    <div class="line-clamp-2 leading-relaxed">{{ $question->text }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-gray-900">
                                    @if($selectedOption)
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="font-bold text-indigo-600">({{ $optLetter }})</span>
                                            <span>{{ $selectedOption->text }}</span>
                                        </span>
                                    @elseif($answer?->answer_text)
                                        <span class="text-gray-800 italic">{{ $answer->answer_text }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Tidak dijawab</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 font-medium text-emerald-800">
                                    @if($correctOption)
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="font-bold">({{ $correctLetter }})</span>
                                            <span>{{ $correctOption->text }}</span>
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">Koreksi Manual</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($hasAnswer)
                                        @if($isCorrect)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Benar</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Salah</span>
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                            Kosong
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-center">
                <a href="{{ route('exam.dashboard') }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-xl shadow-md shadow-indigo-600/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Kembali ke Dashboard Siswa</span>
                </a>
            </div>
        </div>
    </div>
</div>
