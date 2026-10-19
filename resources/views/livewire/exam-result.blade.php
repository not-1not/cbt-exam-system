<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Hasil Ujian</h1>
        <p class="text-gray-600 mt-1">{{ $attempt->exam->title }} ({{ $attempt->exam->code }})</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $attempt->force_submitted_at ? "bg-red-100 text-red-800" : "bg-green-100 text-green-800" }}">
                            {{ $attempt->force_submitted_at ? "Dikirim Otomatis (Waktu Habis)" : "Dikirim Manual" }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Mulai</dt>
                    <dd class="mt-1 text-gray-900">{{ $attempt->started_at?->format("d M Y H:i:s") ?? "-" }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Selesai</dt>
                    <dd class="mt-1 text-gray-900">{{ $attempt->submitted_at?->format("d M Y H:i:s") ?? ($attempt->ended_at?->format("d M Y H:i:s") ?? "-") }}</dd>
                </div>
            </div>
        </div>

        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-indigo-50 rounded-lg">
                    <p class="text-4xl font-bold text-indigo-600">{{ number_format($attempt->score, 2) }}</p>
                    <p class="text-sm text-indigo-700 mt-1">Nilai Diperoleh</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-4xl font-bold text-gray-600">{{ number_format($totalPoints, 2) }}</p>
                    <p class="text-sm text-gray-600 mt-1">Total Nilai Maksimal</p>
                </div>
                <div class="text-center p-4 {{ $passStatus === "lulus" ? "bg-green-50" : ($passStatus === "tidak_lulus" ? "bg-red-50" : "bg-gray-50") }} rounded-lg">
                    @if($passStatus === "lulus")
                        <p class="text-4xl font-bold text-green-600">LULUS</p>
                        <p class="text-sm text-green-700 mt-1">Nilai >= {{ $attempt->exam->pass_mark }}</p>
                    @elseif($passStatus === "tidak_lulus")
                        <p class="text-4xl font-bold text-red-600">TIDAK LULUS</p>
                        <p class="text-sm text-red-700 mt-1">Nilai < {{ $attempt->exam->pass_mark }}</p>
                    @else
                        <p class="text-4xl font-bold text-gray-600">--</p>
                        <p class="text-sm text-gray-600 mt-1">Tidak ada pass mark</p>
                    @endif
                </div>
            </div>
        </div>

        @if($attempt->force_submitted_at)
            <div class="p-6 border-b border-gray-200 bg-red-50">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-red-800">Ujian dikirim otomatis karena waktu habis: <strong>{{ $attempt->force_reason }}</strong></p>
                </div>
            </div>
        @endif

        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Detail Jawaban</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Soal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jawaban Anda</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kunci</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($attempt->exam->questions as $index => $question)
                            <?php
                                $answer = $attempt->answers->firstWhere("question_id", $question->id);
                                $selectedOption = $answer?->option;
                                $correctOption = $question->options->firstWhere("is_correct", true);
                                $isCorrect = $answer?->is_correct ?? false;
                            ?>
                            <tr class="{{ $isCorrect ? "bg-green-50" : ($answer ? "bg-red-50" : "bg-gray-50") }}">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">{{ $question->text }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($selectedOption)
                                        {{ $selectedOption->text }}
                                    @elseif($answer?->answer_text)
                                        {{ $answer->answer_text }}
                                    @else
                                        <span class="text-gray-400 italic">Tidak dijawab</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($correctOption)
                                        {{ $correctOption->text }}
                                    @else
                                        <span class="text-gray-400 italic">Essay</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($answer)
                                        @if($isCorrect)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                Benar
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                Salah
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Belum dijawab</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ number_format($answer?->points_earned ?? 0, 2) }} / {{ $question->points }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-center space-x-3">
                <a href="{{ route("exam.dashboard") }}"
                   class="px-6 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
