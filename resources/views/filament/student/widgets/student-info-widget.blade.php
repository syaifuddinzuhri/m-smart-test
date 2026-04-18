<x-filament-widgets::widget class="h-full">
    <x-filament::section class="h-full">
        <div class="flex items-center gap-4 border-b border-gray-100 pb-4 mb-4">
            <div class="flex-shrink-0">
                <div
                    class="w-12 h-12 rounded-full bg-primary-50 flex items-center justify-center text-primary-600 border border-primary-100">
                    <x-heroicon-s-user class="w-8 h-8" />
                </div>
            </div>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-md md:text-xl font-black text-gray-900 leading-tight">{{ auth()->user()->name }}
                    </h2>
                    <p class="text-xs md:text-sm text-gray-500 font-medium">NISN: {{ $user->student?->nisn ?? '-' }}</p>
                </div>
                <span
                    class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-600 border border-green-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    Aktif
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Kelas & Jurusan</p>
                <p class="text-sm font-semibold text-gray-800">{{ $user->student?->classroom?->name }} -
                    {{ $user->student?->classroom?->major?->name }}</p>
            </div>

            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Jenis Kelamin</p>
                <p class="text-sm font-semibold text-gray-800">{{ $user->student?->gender?->getLabel() ?? '-' }}</p>
            </div>

            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Tempat/Tanggal Lahir</p>
                <p class="text-sm font-semibold text-gray-800">
                    @if ($user->student?->pob && $user->student?->dob)
                        {{ $user->student->pob }},
                        {{ \Carbon\Carbon::parse($user->student->dob)->translatedFormat('d F Y') }}
                    @elseif($user->student?->pob)
                        {{ $user->student->pob }}, -
                    @elseif($user->student?->dob)
                        {{ \Carbon\Carbon::parse($user->student->dob)->translatedFormat('d F Y') }}
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 mt-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-100 flex items-center gap-3">
                    <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                        <x-heroicon-m-clipboard-document-list class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider leading-none mb-1">
                            Belum Dikerjakan</p>
                        <p class="text-md font-black text-gray-900">{{ $exams_pending_count }} <span
                                class="text-[10px] font-medium text-gray-500">Ujian</span></p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-blue-50 border border-blue-100 flex items-center gap-3">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <x-heroicon-m-check-badge class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider leading-none mb-1">
                            Selesai Dikerjakan</p>
                        <p class="text-md font-black text-gray-900">{{ $exams_done_count }} <span
                                class="text-[10px] font-medium text-gray-500">Ujian</span></p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                        <x-heroicon-m-trophy class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider leading-none mb-1">
                            Skor Tertinggi</p>
                        <p class="text-md font-black text-gray-900">{{ number_format($highest_score, 1) }}</p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-purple-50 border border-purple-100 flex items-center gap-3">
                    <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                        <x-heroicon-m-chart-bar class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider leading-none mb-1">Skor
                            Rata-rata
                        </p>
                        <p class="text-md font-black text-gray-900">{{ number_format($average_score, 1) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
