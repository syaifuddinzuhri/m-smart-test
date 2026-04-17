{{-- resources/views/components/latex-renderer.blade.php --}}
<script>
    (function() {
        const CONFIG = {
            delimiters: [
                { left: '$$', right: '$$', display: true },
                { left: '$', right: '$', display: false },
                { left: '\\(', right: '\\)', display: false },
                { left: '\\[', right: '\\]', display: true }
            ],
            throwOnError: false
        };

        const doRender = () => {
            if (typeof renderMathInElement !== 'function') {
                console.warn("KaTeX: renderMathInElement belum dimuat.");
                return;
            }

            // Cari elemen soal yang belum dirender
            const elements = document.querySelectorAll('.soal-content:not([data-render-complete])');

            elements.forEach((el) => {
                const html = el.innerHTML;
                // Cek apakah mengandung simbol matematika
                if (html.includes('$') || html.includes('\\')) {
                    try {
                        renderMathInElement(el, CONFIG);
                        el.setAttribute('data-render-complete', 'true');
                    } catch (e) {
                        console.error("KaTeX Render Error:", e);
                    }
                }
            });
        };

        // Debounce agar tidak berat saat hapus massal
        let timeout;
        const triggerRender = () => {
            clearTimeout(timeout);
            timeout = setTimeout(doRender, 50);
        };

        // 1. Lifecycle Events
        document.addEventListener('DOMContentLoaded', triggerRender);
        document.addEventListener('livewire:navigated', triggerRender);

        // 2. Livewire Hooks (Sangat penting untuk Filter & Delete)
        document.addEventListener('livewire:initialized', () => {
            // Setelah aksi apapun (Hapus, Bulk Delete, Ganti Tab, Filter)
            Livewire.hook('request.cycle.finished', () => {
                triggerRender();
            });

            // Jika elemen lama diperbarui kontennya (Morphing)
            Livewire.hook('morph.updated', ({ el }) => {
                if (el instanceof HTMLElement && el.classList.contains('soal-content')) {
                    el.removeAttribute('data-render-complete');
                    triggerRender();
                }
            });
        });

        // 3. Observer untuk elemen yang muncul dinamis (Modal)
        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                if (mutation.addedNodes.length > 0) {
                    triggerRender();
                    break;
                }
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    })();
</script>

<style>
    .soal-content {
        unicode-bidi: plaintext;
        visibility: visible !important;
    }
    .katex-display {
        margin: 1.2em 0 !important;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 0.2em 0;
        text-align: center;
    }
</style>
