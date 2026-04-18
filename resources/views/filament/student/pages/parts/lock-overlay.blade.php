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
                x-on:click="isNavigatingAllowed = true"
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
