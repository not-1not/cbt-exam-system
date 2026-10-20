<div class="min-h-screen bg-gray-50">
    <div class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $attempt->exam->title }}</h1>
                    <p class="text-sm text-gray-500">{{ $attempt->exam->code }}</p>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Sisa Waktu</p>
                        <p class="text-2xl font-mono font-bold {{ $timeRemaining <= 300 ? "text-red-600" : "text-gray-900" }}"
                           wire:poll.30s="checkExpiry"
                           id="timer">
                            {{ gmdate("H:i:s", $timeRemaining) }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">
                            Soal: {{ $answeredCount }}/{{ count($questions) }}
                        </span>
                        <button wire:click="submitExam"
                                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Kirim Ujian
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8 pt-24">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sticky top-28 h-fit">
                    <h3 class="font-semibold text-gray-900 mb-3">Navigasi Soal</h3>

                    <div class="grid grid-cols-5 gap-2 mb-4">
                        @foreach($questions as $index => $question)
                            <?php
                                $status = $questionStatus[$question->id] ?? "unanswered";
                                $classes = "w-full h-10 rounded border-2 text-sm font-medium transition-colors ";
                                $classes .= match($status) {
                                    "answered" => "bg-green-100 border-green-400 text-green-800 hover:bg-green-200",
                                    "flagged" => "bg-yellow-100 border-yellow-400 text-yellow-800 hover:bg-yellow-200",
                                    default => "bg-gray-100 border-gray-300 text-gray-600 hover:bg-gray-200",
                                };
                                $classes .= $index === $currentQuestionIndex ? " ring-2 ring-indigo-500" : "";
                            ?>
                            <button wire:click="goToQuestion({{ $index }})"
                                    class="{{ $classes }}"
                                    title="Soal {{ $index + 1 }}: {{ ucfirst($status) }}">
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                    </div>

                    <div class="flex space-x-2">
                        <button wire:click="previousQuestion"
                                class="flex-1 px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                {{ $currentQuestionIndex === 0 ? "disabled" : "" }}>
                            Sebelumnya
                        </button>
                        <button wire:click="nextQuestion"
                                class="flex-1 px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                {{ $currentQuestionIndex === count($questions) - 1 ? "disabled" : "" }}>
                            Selanjutnya
                        </button>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <span class="inline-flex items-center">
                                <span class="w-3 h-3 rounded border-2 border-gray-300 bg-gray-100 mr-1"></span>
                                Belum dijawab
                            </span>
                            <span class="inline-flex items-center">
                                <span class="w-3 h-3 rounded border-2 border-green-400 bg-green-100 mr-1"></span>
                                Terjawab
                            </span>
                            <span class="inline-flex items-center">
                                <span class="w-3 h-3 rounded border-2 border-yellow-400 bg-yellow-100 mr-1"></span>
                                Direview
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    @if($currentQuestion)
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-900">Soal {{ $currentQuestionIndex + 1 }} dari {{ count($questions) }}</h2>
                                <button wire:click="toggleFlag({{ $currentQuestion->id }})"
                                        class="px-3 py-1 text-sm font-medium rounded-md {{ in_array($currentQuestion->id, $flaggedQuestions) ? "bg-yellow-100 text-yellow-800" : "bg-gray-100 text-gray-600 hover:bg-gray-200" }}"
                                        title="{{ in_array($currentQuestion->id, $flaggedQuestions) ? "Hapus tanda review" : "Tandai untuk review" }}">
                                    {{ in_array($currentQuestion->id, $flaggedQuestions) ? "? Direview" : "Tandai Review" }}
                                </button>
                            </div>

                            <div class="prose max-w-none mb-6">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $currentQuestion->text }}</p>
                            </div>

                            @if($currentQuestion->options->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($currentQuestion->options as $option)
                                        <?php
                                            $isSelected = ($answers[$currentQuestion->id] ?? null) == $option->id;
                                        ?>
                                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors {{ $isSelected ? "border-indigo-500 bg-indigo-50" : "border-gray-200 hover:border-gray-300" }}">
                                            <input type="radio"
                                                   name="question_{{ $currentQuestion->id }}"
                                                   value="{{ $option->id }}"
                                                   wire:model.live="answers.{{ $currentQuestion->id }}"
                                                   class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 focus:ring-2">
                                            <span class="ml-3 text-gray-900">{{ $option->text }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jawaban Anda</label>
                                    <textarea wire:model.live.debounce.500ms="answers.{{ $currentQuestion->id }}_text"
                                              rows="6"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                              placeholder="Tulis jawaban Anda di sini..."></textarea>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-12 text-center text-gray-500">
                            Tidak ada soal tersedia.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showSubmitModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showSubmitModal', false)"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10 border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-10 w-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Konfirmasi Pengiriman Ujian</h3>
                            <p class="text-xs text-gray-500">Periksa kembali sebelum mengirim jawaban.</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-4 text-sm text-gray-700 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Soal Terjawab:</span>
                            <strong class="text-emerald-600 font-bold">{{ $answeredCount }} dari {{ count($questions) }} Soal</strong>
                        </div>
                        @if($answeredCount < count($questions))
                            <div class="flex justify-between pt-1 border-t border-slate-200 text-amber-700">
                                <span>Belum Dijawab:</span>
                                <strong class="font-bold">{{ count($questions) - $answeredCount }} Soal</strong>
                            </div>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500 mb-6">Apakah Anda yakin ingin menyelesaikan ujian ini sekarang? Setelah dikirim, jawaban tidak dapat diubah lagi.</p>

                    <div class="flex justify-end gap-3">
                        <button type="button"
                                wire:click="$set('showSubmitModal', false)"
                                class="px-4 py-2.5 border border-gray-300 text-xs font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition">
                            Batal, Lanjut Kerjakan
                        </button>
                        <button type="button"
                                wire:click="confirmSubmit"
                                class="px-5 py-2.5 text-xs font-bold rounded-xl text-white bg-red-600 hover:bg-red-500 transition shadow-md shadow-red-500/20"
                                {{ $isSubmitting ? "disabled" : "" }}>
                            {{ $isSubmitting ? "Mengirim..." : "Ya, Kirim Ujian" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($attempt->status !== "in_progress")
        <script>
            window.location.href = "{{ route("exam.result", $attempt) }}";
        </script>
    @endif
</div>

@push("scripts")
<script>
    document.addEventListener("livewire:load", function() {
        Livewire.on("exam-auto-submitted", function() {
            window.location.href = "{{ route("exam.result", $attempt) }}";
        });
    });
</script>
@endpush
