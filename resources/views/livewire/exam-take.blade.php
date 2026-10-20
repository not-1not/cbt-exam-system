<div x-data="{
    currentIndex: 0,
    totalQuestions: {{ count($questions) }},
    answers: @js((object) $answers),
    flagged: @js($flaggedQuestions),
    saveStatus: 'Tersimpan',
    isSaving: false,
    showModal: false,
    timeRemaining: {{ $timeRemaining }},
    timerInterval: null,

    init() {
        this.timerInterval = setInterval(() => {
            if (this.timeRemaining > 0) {
                this.timeRemaining--;
            } else {
                clearInterval(this.timerInterval);
                $wire.autoSubmit();
            }
        }, 1000);
    },

    formatTimer(seconds) {
        if (seconds <= 0) return '00:00:00';
        const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
        const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
        const s = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${h}:${m}:${s}`;
    },

    goTo(index) {
        if (index >= 0 && index < this.totalQuestions) {
            this.currentIndex = index;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },

    selectOption(questionId, optionId) {
        this.answers[questionId] = optionId;
        this.isSaving = true;
        this.saveStatus = 'Menyimpan...';

        $wire.saveAnswer(questionId, optionId).then(() => {
            this.isSaving = false;
            this.saveStatus = 'Tersimpan';
        }).catch(() => {
            this.isSaving = false;
            this.saveStatus = 'Gagal menyimpan';
        });
    },

    saveEssay(questionId, text) {
        this.answers[questionId + '_text'] = text;
        this.isSaving = true;
        this.saveStatus = 'Menyimpan...';

        $wire.saveAnswer(questionId, null, text).then(() => {
            this.isSaving = false;
            this.saveStatus = 'Tersimpan';
        }).catch(() => {
            this.isSaving = false;
            this.saveStatus = 'Gagal menyimpan';
        });
    },

    toggleFlag(questionId) {
        if (this.flagged.includes(questionId)) {
            this.flagged = this.flagged.filter(id => id !== questionId);
        } else {
            this.flagged.push(questionId);
        }
        $wire.toggleFlag(questionId);
    },

    isAnswered(questionId) {
        return this.answers[questionId] !== undefined && this.answers[questionId] !== null && this.answers[questionId] !== '';
    },

    isFlagged(questionId) {
        return this.flagged.includes(questionId);
    },

    getAnsweredCount() {
        return Object.keys(this.answers).filter(k => !k.includes('_text') && this.answers[k] !== null && this.answers[k] !== '').length;
    }
}" class="min-h-screen bg-slate-100 flex flex-col">

    <!-- Top Fixed Bar -->
    <header class="fixed top-0 left-0 right-0 z-40 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-indigo-500/20">
                    CBT
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-bold text-gray-900 leading-tight truncate max-w-[200px] sm:max-w-md">
                        {{ $attempt->exam->title }}
                    </h1>
                    <span class="text-xs font-mono text-gray-500">{{ $attempt->exam->code }}</span>
                </div>
            </div>

            <!-- Timer & Actions -->
            <div class="flex items-center gap-3 sm:gap-6">
                <!-- Status Sinkronisasi Real-time -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-50 border border-slate-200 text-slate-600">
                    <span class="h-2 w-2 rounded-full"
                          :class="isSaving ? 'bg-amber-500 animate-ping' : 'bg-emerald-500'"></span>
                    <span x-text="saveStatus"></span>
                </div>

                <!-- Timer Countdown -->
                <div class="text-center px-3 py-1 sm:px-4 sm:py-1.5 rounded-xl border"
                     :class="timeRemaining <= 300 ? 'bg-red-50 border-red-200 text-red-600' : 'bg-indigo-50/70 border-indigo-100 text-indigo-950'">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Sisa Waktu</span>
                    <span class="font-mono font-extrabold text-base sm:text-lg tracking-tight"
                          :class="timeRemaining <= 300 ? 'text-red-600 animate-pulse' : 'text-indigo-900'"
                          x-text="formatTimer(timeRemaining)">
                    </span>
                </div>

                <!-- Tombol Kirim Ujian -->
                <button type="button"
                        @click="showModal = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-red-600 hover:bg-red-500 shadow-md shadow-red-600/20 transition transform hover:-translate-y-0.5">
                    <span>Selesai / Kirim</span>
                    <svg class="w-4 h-4 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-24 pb-12 w-full flex-grow">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">

            <!-- Left: Soal Konten (3 Kolom) -->
            <div class="lg:col-span-3 order-2 lg:order-1">
                @if(count($questions) === 0)
                    <div class="bg-white rounded-2xl p-12 text-center border border-gray-200 shadow-sm">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <h3 class="mt-4 text-base font-bold text-gray-800">Paket Ujian Belum Memiliki Soal</h3>
                        <p class="text-xs text-gray-500 mt-1">Silakan hubungi pengawas atau guru pengampu ujian.</p>
                    </div>
                @else
                    @foreach($questions as $index => $q)
                        <div wire:key="q-card-{{ $q->id }}"
                             x-show="currentIndex === {{ $index }}"
                             x-cloak
                             class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-150">
                            <!-- Question Header -->
                            <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-slate-50/60">
                                <div class="flex items-center gap-3">
                                    <span class="h-8 px-3 rounded-lg bg-indigo-600 text-white font-bold text-sm flex items-center justify-center shadow-sm">
                                        Soal {{ $index + 1 }}
                                    </span>
                                    <span class="text-xs font-semibold text-gray-500">
                                        Bobot: {{ $q->points }} Poin
                                    </span>
                                </div>

                                <button type="button"
                                        @click="toggleFlag({{ $q->id }})"
                                        :class="isFlagged({{ $q->id }}) ? 'bg-amber-100 border-amber-300 text-amber-800' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold transition">
                                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v7a1 1 0 01-1 1H5.414l-2.707 2.707A1 1 0 011 14.414V4a1 1 0 011-1z"/></svg>
                                    <span x-text="isFlagged({{ $q->id }}) ? 'Ragu-ragu (Ditandai)' : 'Ragu-ragu'"></span>
                                </button>
                            </div>

                            <!-- Question Text -->
                            <div class="p-6 sm:p-8">
                                <div class="prose max-w-none text-base sm:text-lg text-gray-900 leading-relaxed mb-8 select-none">
                                    {!! nl2br(e($q->text)) !!}
                                </div>

                                <!-- Multiple Choice Options -->
                                @if($q->options->isNotEmpty())
                                    <div class="space-y-3">
                                        @foreach($q->options as $optIndex => $option)
                                            <?php
                                                $labels = ['A', 'B', 'C', 'D', 'E', 'F'];
                                                $letter = $labels[$optIndex] ?? ($optIndex + 1);
                                            ?>
                                            <div wire:key="q-opt-{{ $q->id }}-{{ $option->id }}"
                                                 @click="selectOption({{ $q->id }}, {{ $option->id }})"
                                                 :class="answers[{{ $q->id }}] == {{ $option->id }} ? 'border-indigo-600 bg-indigo-50/70 shadow-sm ring-1 ring-indigo-500' : 'border-gray-200 hover:border-gray-300 hover:bg-slate-50/60'"
                                                 class="flex items-start gap-4 p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all duration-100 select-none">
                                                
                                                <!-- Letter Badge -->
                                                <div :class="answers[{{ $q->id }}] == {{ $option->id }} ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300'"
                                                     class="h-7 w-7 rounded-full border-2 flex items-center justify-center font-bold text-xs flex-shrink-0 transition-colors mt-0.5">
                                                    {{ $letter }}
                                                </div>

                                                <!-- Option Text -->
                                                <div class="text-sm sm:text-base text-gray-800 leading-relaxed pt-0.5">
                                                    {{ $option->text }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <!-- Essay Textarea -->
                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider">Tulis Jawaban Uraian Anda:</label>
                                        <textarea rows="6"
                                                  class="w-full p-4 border-2 border-gray-200 rounded-2xl text-sm text-gray-800 focus:border-indigo-500 focus:ring-0 transition"
                                                  placeholder="Ketik jawaban Anda di sini..."
                                                  :value="answers['{{ $q->id }}_text'] || ''"
                                                  @input.debounce.500ms="saveEssay({{ $q->id }}, $event.target.value)"></textarea>
                                    </div>
                                @endif
                            </div>

                            <!-- Bottom Question Nav inside card -->
                            <div class="p-4 sm:p-6 bg-slate-50 border-t border-gray-100 flex items-center justify-between gap-4">
                                <button type="button"
                                        @click="goTo(currentIndex - 1)"
                                        :disabled="currentIndex === 0"
                                        :class="currentIndex === 0 ? 'opacity-40 cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-white text-gray-700 hover:bg-gray-100 shadow-sm'"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-xs sm:text-sm font-semibold transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    <span>Sebelumnya</span>
                                </button>

                                <span class="text-xs font-semibold text-gray-500">
                                    Soal <span x-text="currentIndex + 1"></span> dari <span x-text="totalQuestions"></span>
                                </span>

                                <button type="button"
                                        @click="goTo(currentIndex + 1)"
                                        :disabled="currentIndex === totalQuestions - 1"
                                        :class="currentIndex === totalQuestions - 1 ? 'opacity-40 cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-indigo-600 text-white hover:bg-indigo-500 shadow-md shadow-indigo-600/20'"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition">
                                    <span>Selanjutnya</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Right: Question Navigation Grid (1 Kolom) -->
            <div class="lg:col-span-1 order-1 lg:order-2 sticky top-24">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <h3 class="font-bold text-gray-900 text-sm">Nomor Soal</h3>
                        <span class="text-xs font-semibold text-indigo-600">
                            <span x-text="getAnsweredCount()"></span>/{{ count($questions) }} Terjawab
                        </span>
                    </div>

                    <!-- Grid Buttons -->
                    <div class="grid grid-cols-5 gap-2 mb-6">
                        @foreach($questions as $index => $q)
                            <button wire:key="q-nav-btn-{{ $q->id }}"
                                    type="button"
                                    @click="goTo({{ $index }})"
                                    :class="{
                                        'ring-2 ring-indigo-600 border-indigo-600 font-extrabold': currentIndex === {{ $index }},
                                        'bg-amber-100 border-amber-400 text-amber-800 font-bold': isFlagged({{ $q->id }}),
                                        'bg-emerald-100 border-emerald-400 text-emerald-800 font-bold': isAnswered({{ $q->id }}) && !isFlagged({{ $q->id }}),
                                        'bg-white border-gray-200 text-gray-700 hover:bg-gray-50': !isAnswered({{ $q->id }}) && !isFlagged({{ $q->id }})
                                    }"
                                    class="h-10 rounded-xl border text-xs font-semibold flex items-center justify-center transition-all duration-100 shadow-sm cursor-pointer select-none">
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Legend -->
                    <div class="space-y-2 pt-3 border-t border-gray-100 text-[11px] text-gray-600">
                        <div class="flex items-center gap-2">
                            <span class="h-3.5 w-3.5 rounded bg-emerald-100 border border-emerald-400"></span>
                            <span>Sudah Dijawab</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-3.5 w-3.5 rounded bg-amber-100 border border-amber-400"></span>
                            <span>Ragu-ragu</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-3.5 w-3.5 rounded bg-white border border-gray-300"></span>
                            <span>Belum Dijawab</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Konfirmasi Kirim Ujian (Alpine Controlled) -->
    <template x-if="showModal">
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>

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

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-4 text-sm text-gray-700 space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Soal Terjawab:</span>
                            <strong class="text-emerald-600 font-bold">
                                <span x-text="getAnsweredCount()"></span> dari {{ count($questions) }} Soal
                            </strong>
                        </div>
                        <template x-if="getAnsweredCount() < totalQuestions">
                            <div class="flex justify-between pt-1 border-t border-slate-200 text-amber-700 text-xs">
                                <span>Belum Dijawab:</span>
                                <strong class="font-bold" x-text="(totalQuestions - getAnsweredCount()) + ' Soal'"></strong>
                            </div>
                        </template>
                    </div>

                    <p class="text-xs text-gray-500 mb-6">Apakah Anda yakin ingin menyelesaikan dan mengirim ujian ini? Setelah dikirim, jawaban tidak dapat diubah lagi.</p>

                    <div class="flex justify-end gap-3">
                        <button type="button"
                                @click="showModal = false"
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
    </template>
</div>
