{{-- resources/views/filament/components/lightbox-overlay.blade.php --}}
<div x-data="{
    open: false,
    src: '',
    type: '',
    init() {
        window.addEventListener('open-media-lightbox', (e) => {
            this.src = e.detail.src;
            this.type = e.detail.type;
            this.open = true;
        });
    },
    close() {
        this.open = false;
        // Stop media saat ditutup agar suara tidak bocor
        this.src = '';
    }
}" x-show="open" x-cloak
    class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/90 p-4 shadow-2xl"
    style="backdrop-filter: blur(8px);">

    <button @click="close()"
        class="absolute top-5 right-5 text-white/70 hover:text-white transition-colors p-2 z-[100000]">
        <x-heroicon-m-x-mark class="w-10 h-10" />
    </button>

    <div class="relative max-h-full max-w-4xl w-full flex items-center justify-center" @click.stop>

        {{-- RENDER GAMBAR --}}
        <template x-if="type === 'image'">
            <img :src="src" class="max-h-[90vh] max-w-full rounded-lg shadow-2xl object-contain">
        </template>

        {{-- RENDER VIDEO --}}
        <template x-if="type === 'video'">
            <video :src="src" controls autoplay class="max-h-[80vh] w-full rounded-lg shadow-2xl"></video>
        </template>

        {{-- RENDER AUDIO --}}
        <template x-if="type === 'audio'">
            <div class="bg-white p-8 rounded-2xl w-full max-w-md shadow-2xl">
                <div class="text-center mb-4">
                    <x-heroicon-m-speaker-wave class="w-12 h-12 mx-auto text-indigo-600" />
                    <p class="text-gray-600 font-bold mt-2 uppercase tracking-widest text-xs">Audio Player</p>
                </div>
                <audio :src="src" controls autoplay class="w-full"></audio>
            </div>
        </template>

        {{-- RENDER PDF / DOKUMEN --}}
        <template x-if="type === 'pdf'">
            <iframe :src="src + '#toolbar=0'"
                class="w-full h-[90vh] rounded-lg border-none shadow-2xl bg-white"></iframe>
        </template>

        {{-- FALLBACK --}}
        <template x-if="type === 'other'">
            <div class="bg-white p-10 rounded-2xl text-center shadow-2xl">
                <x-heroicon-o-document-text class="w-16 h-16 mx-auto text-gray-400" />
                <p class="mt-4 text-gray-900 font-bold">File tidak dapat dipreview</p>
                <a :href="src" download
                    class="mt-4 inline-block bg-primary-600 text-white px-6 py-2 rounded-lg font-bold">Download File</a>
            </div>
        </template>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    .soal-content img {
        max-width: 50% !important;
        cursor: zoom-in !important;
        border-radius: 8px;
        margin: 10px 0;
    }

    @media (max-width: 640px) {
        .soal-content img {
            max-width: 100% !important;
        }
    }
</style>
