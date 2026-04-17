<script>
    document.addEventListener('click', function(e) {
        const target = e.target;

        // Cek apakah klik gambar di dalam teks soal
        if (target.tagName === 'IMG' && target.closest('.soal-content')) {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('open-media-lightbox', {
                detail: {
                    src: target.src,
                    type: 'image'
                }
            }));
        }
    });
</script>
