<x-filament-panels::page>
    <div x-data="{
        isLocked: @entangle('isLocked'),
        lockExam() {
            if (!this.isLocked) {
                $wire.call('lockExam');
            }
        },
        checkFullscreen() {
            checkFullscreen() {
                // Cek apakah ini perangkat mobile iOS
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

                if (isIOS) {
                    // Di iOS, kita tidak bisa paksa Fullscreen,
                    // jadi kita andalkan visibilitychange (pindah tab/minimize)
                    return;
                }

                // Untuk Android & Desktop, tetap paksa Fullscreen
                if (!document.fullscreenElement && !this.isLocked) {
                    this.lockExam();
                }
            }
        }
    }" @visibilitychange.window="if (document.hidden) lockExam()" @blur.window="lockExam()"
        @fullscreenchange.window="checkFullscreen()"
        @keydown.window="
        if ($event.keyCode == 123 || ($event.ctrlKey && $event.shiftKey && $event.keyCode == 73) || ($event.ctrlKey && $event.keyCode == 85) || $event.metaKey) {
            lockExam();
            $event.preventDefault();
        }
    "
        class="relative">
        @if ($isLocked)
            <div class="bg-gray-500/20"
                style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(20px); z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 24px;">

                <div
                    class="bg-white rounded-xl shadow-xl p-4 text-center border border-gray-100 w-full max-w-xl transition-all scale-100">

                    <div class="mb-4 flex justify-center">

                        <img src="{{ asset('icons/shield.png') }}" class="w-20" alt="">

                    </div>

                    <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tighter mb-3">
                        Akses Terputus!
                    </h2>

                    <div class="space-y-4 mb-10">
                        <p class="text-md text-gray-600 font-medium leading-relaxed">
                            Sistem mendeteksi aktivitas di luar jendela ujian. Untuk menjaga integritas, sesi Anda telah
                            <span class="text-red-600 font-bold underline">DITANGGUHKAN</span> secara otomatis.
                        </p>

                        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 flex gap-3 text-left">
                            <x-heroicon-m-information-circle class="w-5 h-5 text-amber-600 shrink-0" />
                            <p class="text-xs text-gray-500 font-medium leading-normal">
                                Pelanggaran dicatat oleh sistem (IP, Waktu, & Perangkat). Silahkan hubungi pengawas
                                ruangan jika ini adalah kendala teknis yang tidak disengaja.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <x-filament::button color="danger" size="xl" wire:click="backToDashboard"
                            class="w-full !rounded-2xl py-4 text-lg font-black uppercase tracking-wide shadow-xl shadow-red-100"
                            icon="heroicon-m-arrow-path">
                            Minta Token Baru
                        </x-filament::button>

                        <p class="text-[10px] text-gray-400 uppercase tracking-[0.1em] font-black mt-6">
                            Security Protocol — System ID: {{ $session->system_id }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div style="{{ $isLocked ? 'filter: blur(40px); pointer-events: none; user-select: none; opacity: 0.1;' : '' }}"
            class="transition-all duration-1000">
            <div x-data="{ showOverlay: true }" x-show="showOverlay" @click="triggerFullScreen(); showOverlay = false"
                class="fixed inset-0 z-[999999] bg-white/10 backdrop-blur-[2px] flex items-center justify-center cursor-pointer">
                <div class="bg-primary-600 text-white px-6 py-3 rounded-full font-bold animate-bounce shadow-2xl">
                    Klik Layar untuk Memulai Ujian
                </div>
            </div>
            @include('filament.student.pages.parts.exam-content')
        </div>
    </div>

    @push('scripts')
        <script>
            const triggerFullScreen = () => {
                const elem = document.documentElement;
                const rfs = elem.requestFullscreen || elem.webkitRequestFullscreen || elem.msRequestFullscreen;
                if (rfs) {
                    rfs.call(elem).catch(err => {
                        console.warn(`Error attempting to enable full-screen mode: ${err.message}`);
                    });
                }
            };

            // Fungsi untuk memaksa fullscreen di interaksi pertama apa pun
            const forceStart = () => {
                triggerFullScreen();
                // Hapus event listener setelah berhasil agar tidak mengganggu performa
                ['click', 'keydown', 'touchstart'].forEach(event => {
                    document.removeEventListener(event, forceStart);
                });
            };

            // Pasang listener pada hampir semua interaksi awal
            document.addEventListener('click', () => {
                if (!document.fullscreenElement && !@js($isLocked)) {
                    triggerFullScreen();
                }
            }, {
                once: true
            });
            document.addEventListener('keydown', forceStart);
            document.addEventListener('touchstart', forceStart);

            // Deteksi perubahan fullscreen
            document.addEventListener('fullscreenchange', () => {
                if (!document.fullscreenElement) {
                    // Jika keluar fullscreen, cek apakah halaman sedang terkunci atau tidak
                    // Jika tidak terkunci, paksa kunci lewat Livewire
                    @this.call('lockExam');
                }
            });

            // Tambahan: Auto-focus jika user kembali ke tab ini
            window.addEventListener('focus', () => {
                if (!document.fullscreenElement && !@js($isLocked)) {
                    // Memberi peringatan halus atau langsung kunci
                    console.log('User returned to tab');
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
