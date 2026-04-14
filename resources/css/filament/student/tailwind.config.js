import preset from "../../../../vendor/filament/support/tailwind.config.preset";

export default {
    presets: [preset],
    content: [
        "./app/Filament/Student/**/*.php",
        "./resources/views/filament/student/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
    ],
    // Jika warna tetap tidak muncul, Anda bisa paksa di sini
    theme: {
        extend: {
            colors: {
                // Tailwind 4 biasanya sudah include semua warna,
                // tapi jika v3 pastikan tidak ter-overwrite
            },
        },
    },
};
