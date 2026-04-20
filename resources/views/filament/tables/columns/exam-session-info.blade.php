<div class="flex flex-col gap-3 py-3 px-2 w-full">
    @php
        $record = $getRecord();
        $isPassed = $record->student_score >= $record->passing_grade;
        $score = number_format($record->student_score, 2);
        $max = $record->target_max_score;
    @endphp

    <!-- BARIS 1: INFO DASAR (Desktop: 3 Kolom, Mobile: Stack 1-1-1) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
        <!-- Waktu Selesai -->
        <div class="flex flex-col">
            <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Waktu Selesai</span>
            <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 font-medium">
                <x-heroicon-m-calendar-days class="w-3.5 h-3.5" />
                @if ($record->finished_at)
                    {{ \Carbon\Carbon::parse($record->finished_at)->format('d/m/Y H:i T') }}
                @else
                    -
                @endif
                {{-- {{ \Carbon\Carbon::parse($record->finished_at?)->format('d M Y, H:i T')}} --}}
            </div>
        </div>

        <!-- Mata Pelajaran -->
        <div class="flex flex-col">
            <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-0.5 md:hidden">Mata
                Pelajaran</span>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                <span class="hidden md:inline">Mata Pelajaran: </span><span
                    class="font-semibold text-gray-700 dark:text-gray-200">{{ $record->subject?->name ?? '-' }}</span>
            </div>
        </div>

        <!-- Kelas -->
        <div class="flex flex-col">
            <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-0.5 md:hidden">Target
                Kelas</span>
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                Kelas: <span class="text-gray-700 dark:text-gray-200">{{ $record->target_classroom ?? '-' }}</span>
            </div>
        </div>
    </div>

    <hr class="border-gray-100 dark:border-gray-800">

    <!-- BARIS 2: STATUS & SKOR (Desktop: 3 Kolom, Mobile: 2 Kolom untuk skor, 1 Kolom untuk status) -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 items-start">

        <!-- Skor (Mobile: Col 1) -->
        <div class="flex flex-col gap-1">
            <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Hasil Skor</span>
            <div
                class="inline-flex items-center w-fit justify-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-black text-xs">
                @if ($max > 0)
                    {{ $score }} <span class="mx-1 text-blue-300 dark:text-blue-700">/</span> {{ $max }}
                @else
                    {{ $score }}
                @endif
            </div>
        </div>

        <!-- KKM (Mobile: Col 2) -->
        <div class="flex flex-col gap-1">
            <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Ambang Batas</span>
            <div
                class="inline-flex items-center w-fit justify-center px-2 py-1 rounded-lg bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700 text-[10px] font-bold">
                <x-heroicon-m-flag class="w-3 h-3 mr-1" />
                {{ number_format($record->passing_grade, 2) }}
            </div>
        </div>

        <!-- Status & Finalisasi (Mobile: Full Width 1 Kolom di bawah skor) -->
        <div
            class="col-span-2 md:col-span-1 gap-2 pt-1 md:pt-0 border-t md:border-t-0 border-gray-50 dark:border-gray-800">
            <div class="grid grid-cols-2">
                <div>
                    @if ($isPassed)
                        <div class="flex flex-col gap-1.5">
                            <div
                                class="inline-flex items-center w-fit md:flex px-2 py-0.5 gap-1 rounded-md text-[10px] font-bold bg-green-50 text-green-700 border border-green-200 dark:bg-green-950 dark:text-green-400 dark:border-green-900 uppercase tracking-wider">
                                <x-heroicon-m-check-badge class="w-4 h-4" />
                                <span>LULUS</span>
                            </div>
                            <span class="text-[10px] text-gray-400 italic font-medium leading-none">Memenuhi syarat
                                kelulusan</span>
                        </div>
                    @else
                        <div class="flex flex-col gap-1.5">
                            <div
                                class="inline-flex items-center w-fit md:flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 dark:bg-red-950 dark:text-red-400 dark:border-red-900 uppercase tracking-wider">
                                <x-heroicon-m-x-circle class="w-4 h-4" />
                                <span>TIDAK LULUS</span>
                            </div>
                            <span class="text-[10px] text-gray-400 italic font-medium leading-none">Belum memenuhi
                                syarat kelulusan</span>
                        </div>
                    @endif
                </div>

                <!-- Status Final -->
                <div class="flex flex-col gap-0.5">
                    @if ($record->finalized_at)
                        <div class="flex items-center gap-1.5">
                            <div
                                class="inline-flex items-center w-fit px-2 py-0.5 rounded-md text-[10px] font-bold bg-green-50 text-green-700 border border-green-200 uppercase tracking-wider">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                SUDAH FINAL
                            </div>
                            <div class="leading-none flex flex-col gap-0.5">
                                <span
                                    class="text-[11px] font-bold text-gray-800">{{ $record->finalized_at->format('d/m/Y') }}</span>
                                <span
                                    class="text-[10px] text-gray-500 font-medium">{{ $record->finalized_at->format('H:i:s T') }}</span>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col gap-1.5">
                            <div
                                class="inline-flex items-center w-fit px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-800 uppercase tracking-wider">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                BELUM FINAL
                            </div>
                            <span class="text-[10px] text-gray-400 italic font-medium leading-none">Menunggu finalisasi
                                pengajar</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
