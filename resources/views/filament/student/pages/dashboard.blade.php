<x-filament-panels::page>

    <!-- TESTING SNAPSHOT AREA -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 mb-6"
        x-data="{
            isMonitoring: false,
            lastTime: null,
            async startTest() {
                try {
                    window.screenStream = await navigator.mediaDevices.getDisplayMedia({
                        video: { displaySurface: 'monitor' },
                        audio: false
                    });

                    this.isMonitoring = true;
                    this.lastTime = new Date().toLocaleTimeString();

                    // Mulai interval ambil gambar (setiap 5 detik untuk test)
                    window.snapshotInterval = setInterval(() => {
                        this.takeTestSnapshot();
                    }, 5000);

                    window.screenStream.getVideoTracks()[0].onended = () => {
                        this.stopTest();
                    };
                } catch (err) {
                    alert('Gagal akses layar: ' + err);
                }
            },
            stopTest() {
                if (window.screenStream) {
                    window.screenStream.getTracks().forEach(track => track.stop());
                }
                clearInterval(window.snapshotInterval);
                this.isMonitoring = false;
            },
            takeTestSnapshot() {
                const video = document.createElement('video');
                video.srcObject = window.screenStream;
                video.play();

                video.onloadedmetadata = () => {
                    const canvas = document.getElementById('test-canvas');
                    canvas.width = 640;
                    canvas.height = 360;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const imageData = canvas.toDataURL('image/jpeg', 0.5);
                    $wire.uploadSnapshot(imageData);
                    this.lastTime = new Date().toLocaleTimeString();
                    video.srcObject = null;
                };
            }
        }">

        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Sample Screen Monitoring</h2>
                <p class="text-xs text-gray-500">Gunakan area ini untuk menguji fitur share screen sebelum ujian dimulai.
                </p>
            </div>
            <div class="flex gap-2">
                <template x-if="!isMonitoring">
                    <button @click="startTest()"
                        class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-700 transition-all">
                        Aktifkan Kamera Layar
                    </button>
                </template>
                <template x-if="isMonitoring">
                    <button @click="stopTest()"
                        class="bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:bg-red-700 transition-all">
                        Matikan
                    </button>
                </template>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 items-center">
            <!-- Preview Area -->
            <div
                class="relative aspect-video bg-gray-100 dark:bg-gray-900 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center overflow-hidden">
                <template x-if="!isMonitoring">
                    <div class="text-center p-4">
                        <x-heroicon-o-camera class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                        <p class="text-xs text-gray-400 font-medium">Layar belum dibagikan</p>
                    </div>
                </template>

                <template x-if="isMonitoring">
                    <div class="w-full h-full">
                        <img :src="$wire.lastSnapshotUrl" class="w-full h-full object-cover"
                            x-show="$wire.lastSnapshotUrl">
                        <div
                            class="absolute bottom-2 left-2 bg-black/50 text-white text-[10px] px-2 py-1 rounded-lg backdrop-blur-sm">
                            Real-time Preview (Update: <span x-text="lastTime"></span>)
                        </div>
                    </div>
                </template>
            </div>

            <!-- Info Area -->
            <div class="space-y-3">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                    <h4 class="text-xs font-bold text-blue-900 dark:text-blue-300 uppercase mb-1">Cara Kerja:</h4>
                    <ul class="text-[11px] text-blue-800 dark:text-blue-400 list-disc list-inside space-y-1">
                        <li>Sistem menangkap layar Anda setiap 5 detik.</li>
                        <li>Gambar dikirim ke server (Storage).</li>
                        <li>Admin dapat melihat aktivitas layar ini selama ujian berlangsung.</li>
                        <li>Jika Share Screen dihentikan, ujian akan otomatis terkunci.</li>
                    </ul>
                </div>
                <canvas id="test-canvas" style="display:none;"></canvas>
            </div>
        </div>
    </div>

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
