<x-filament-panels::page>
    <div class="block md:hidden">
        <div
            class="bg-primary-50 border border-primary-200 rounded-2xl p-4 flex items-center justify-between shadow-sm outline outline-1 outline-white/50 animate-pulse">
            <div class="flex items-center gap-3">
                <div class="bg-primary-500 p-2.5 rounded-xl text-white shadow-sm">
                    <x-heroicon-m-academic-cap class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-black text-primary-900 leading-none mb-1">Daftar Ujian Aktif</p>
                    <p class="text-[11px] text-primary-700 font-medium italic">Ketuk panah untuk mulai ujian</p>
                </div>
            </div>

            <button onclick="scrollToExams()"
                class="bg-white text-primary-600 p-3 rounded-xl shadow-md border border-primary-100 active:scale-90 transition-transform flex items-center justify-center">
                <x-heroicon-m-chevron-double-down class="w-5 h-5" />
            </button>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        @livewire(\App\Filament\Student\Widgets\StudentInfoWidget::class)
        @livewire(\App\Filament\Student\Widgets\ExamRulesWidget::class)
    </div>

    <div id="section-tabel-ujian" class="transition-all duration-500">
        @livewire(\App\Filament\Student\Widgets\ActiveExamsTable::class)
    </div>

    @push('scripts')
        <script>
            function scrollToExams() {
                const element = document.getElementById('section-tabel-ujian');
                const topbarOffset = 80;

                const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
                const offsetPosition = elementPosition - topbarOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        </script>
    @endpush

</x-filament-panels::page>
