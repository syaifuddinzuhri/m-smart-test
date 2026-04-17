<script>
    function applyKatex(target = document.body) {
        // Cari semua elemen soal di dalam target
        const containers = target.querySelectorAll('.soal-content');

        containers.forEach((el) => {
            // Cek apakah sudah pernah dirender untuk menghindari double render
            if (el.classList.contains('katex-rendered')) return;

            try {
                renderMathInElement(el, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true },
                        { left: '$', right: '$', display: false },
                        { left: '\\(', right: '\\)', display: false },
                        { left: '\\[', right: '\\]', display: true }
                    ],
                    throwOnError: false
                });
                // Tandai elemen agar tidak dirender ulang
                el.classList.add('katex-rendered');
            } catch (e) {
                console.error("KaTeX error:", e);
            }
        });
    }

    // Jalankan saat halaman siap
    document.addEventListener('DOMContentLoaded', () => {
        applyKatex();

        // Gunakan MutationObserver untuk memantau kapan soal muncul (setelah filter)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    applyKatex();
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    // Integrasi khusus Livewire 3
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('request.cycle.finished', () => {
            setTimeout(() => { applyKatex(); }, 50);
        });
    });
</script>
