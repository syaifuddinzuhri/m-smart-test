<x-filament-panels::page>
    <div x-data="{
        isLocked: @entangle('isLocked'),
        showFullscreenOverlay: true,
        lockExam() {
            if (!this.isLocked) {
                $wire.call('lockExam');
            }
        },
        handleFullscreenChange() {
            if (!document.fullscreenElement && !this.isLocked) {
                // Jika keluar fullscreen, cukup tampilkan overlay, JANGAN lock
                this.showFullscreenOverlay = true;
            }
        }
    }"
        @keydown.window="
        const key = $event.key.toLowerCase();
        const isCmdOrCtrl = $event.ctrlKey || $event.metaKey;

        // Pengecualian Reload: F5 (116) atau Ctrl+R / Cmd+R
        if (key === 'f5' || (isCmdOrCtrl && key === 'r')) {
            return;
        }

        // BLOKIR AKSES BERBAHAYA
        if (
            (isCmdOrCtrl && ['t', 'n', 'u', 'i', 'j', 'p', 'e', 'k'].includes(key)) ||
            (isCmdOrCtrl && $event.shiftKey && ['n', 'i', 'j'].includes(key)) ||
            $event.key === 'F12' ||
            $event.altKey ||
            ($event.metaKey && key !== 'r')
        ) {
            $event.preventDefault();
            lockExam();
        }
    "
        @fullscreenchange.window="handleFullscreenChange()" @visibilitychange.window="if (document.hidden) lockExam()"
        @blur.window="setTimeout(() => { if (!document.hasFocus()) lockExam() }, 250)" class="relative">

        <template x-if="showFullscreenOverlay && !isLocked">
            <div
                class="fixed inset-0 z-[999998] bg-black/60 backdrop-blur-md flex items-center justify-center p-6 text-center">
                <div class="bg-white p-8 rounded-2xl max-w-sm shadow-2xl">
                    <div class="text-amber-500 mb-4 animate-bounce">
                        <x-heroicon-o-arrows-pointing-out class="w-16 h-16 mx-auto" />
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 uppercase tracking-tight">
                        Fokus Mode Aktif
                    </h3>

                    <div class="space-y-3 mb-6">
                        <p class="text-gray-600 text-sm leading-relaxed px-2">
                            Untuk menjaga integritas, Anda wajib mengerjakan ujian dalam
                            <span class="font-bold text-gray-800">Tampilan Layar Penuh (DESKTOP)</span>.
                            Jangan keluar dari halaman ini sebelum selesai.
                        </p>

                        <div
                            class="bg-red-50 border border-red-100 rounded-xl p-3 flex items-center gap-3 justify-center mx-auto">
                            <div class="relative flex h-3 w-3">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
                            </div>
                            <p class="text-[11px] sm:text-xs font-black text-red-700 uppercase tracking-widest">
                                Waktu ujian terus berjalan!
                            </p>
                        </div>
                    </div>
                    <x-filament::button size="lg" color="warning" class="w-full"
                        @click="triggerFullScreen(); showFullscreenOverlay = false">
                        MULAI KERJAKAN
                    </x-filament::button>
                    <p class="mt-4 text-[10px] text-gray-400 uppercase font-medium">
                        MANUSGI SMART TEST • SECURITY PROTOCOL
                    </p>
                </div>
            </div>
        </template>

        @if ($isLocked)
            @include('filament.student.pages.parts.lock-overlay')
        @endif

        <div :style="isLocked || showFullscreenOverlay ? 'filter: blur(20px); pointer-events: none;' : ''">
            @include('filament.student.pages.parts.exam-content')
        </div>
    </div>

    @push('scripts')
        <script>
            let isNavigatingAllowed = false;

            window.addEventListener('prepare-navigation', () => {
                isNavigatingAllowed = true;
                if (document.fullscreenElement) {
                    document.exitFullscreen().catch(err => {});
                }
                window.onbeforeunload = null;
            });

            // Izinkan browser melakukan refresh tanpa interupsi lock
            window.onbeforeunload = function(e) {
                if (!@js($isLocked) && !isNavigatingAllowed) {
                    const msg = "Sesi ujian sedang berjalan.";
                    e.returnValue = msg;
                    return msg;
                }
            };

            const triggerFullScreen = () => {
                const elem = document.documentElement;
                const rfs = elem.requestFullscreen || elem.webkitRequestFullscreen || elem.msRequestFullscreen;
                if (rfs) {
                    rfs.call(elem).catch(err => console.warn('Fullscreen Error:', err));
                }
            };

            // Trigger awal setelah load/refresh
            const forceStart = () => {
                if (!@js($isLocked)) triggerFullScreen();
                ['click', 'touchstart'].forEach(ev => document.removeEventListener(ev, forceStart));
            };
            document.addEventListener('click', forceStart);
            document.addEventListener('touchstart', forceStart);

            // Mencegah Klik Kanan & Tengah
            document.addEventListener('contextmenu', e => e.preventDefault());
            document.addEventListener('auxclick', (e) => {
                if (e.button === 1) {
                    e.preventDefault();
                    @this.call('lockExam');
                }
            });

            // Cegah Ctrl + Click
            document.addEventListener('click', (e) => {
                if ((e.ctrlKey || e.metaKey) && !isNavigatingAllowed) {
                    e.preventDefault();
                    @this.call('lockExam');
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
