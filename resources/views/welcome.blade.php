<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>MS Smart Test - Solusi Ujian Online & CBT Modern</title>
    <meta name="title" content="MS Smart Test - Solusi Ujian Online & CBT Modern">
    <meta name="description"
        content="MS Smart Test adalah sistem ujian online berbasis web yang aman, handal, dan terintegrasi. Dirancang untuk mendukung pelaksanaan Computer Based Test (CBT) yang efektif, efisien, dan akurat bagi berbagai lembaga pendidikan.">
    <meta name="keywords"
        content="MS Smart Test, smart test, ujian online, CBT, computer based test, sistem ujian sekolah, aplikasi ujian online, ujian berbasis komputer, e-learning, ujian digital, CBT nasional, platform ujian online, manajemen ujian">

    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">

    <meta name="google" content="notranslate">

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    {{-- <meta name="robots" content="noindex, nofollow"> --}}

    <link rel="icon" href="/favicon.ico?v=2">
    <link rel="shortcut icon" href="/favicon.ico?v=2">
    <link rel="apple-touch-icon" href="/favicon.ico?v=2">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MS Smart Test">

    <meta name="theme-color" content="#ffffff">

    <!-- Open Graph -->
    <meta property="og:title" content="MS Smart Test - Solusi Ujian Online & CBT Modern">
    <meta property="og:description"
        content="MS Smart Test adalah sistem ujian online berbasis web yang aman, handal, dan terintegrasi. Dirancang untuk mendukung pelaksanaan Computer Based Test (CBT) yang efektif, efisien, dan akurat bagi berbagai lembaga pendidikan.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:image" content="{{ env('APP_URL') }}/images/logo.webp">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="MS Smart Test - Solusi Ujian Online & CBT Modern">
    <meta name="twitter:description"
        content="MS Smart Test adalah sistem ujian online berbasis web yang aman, handal, dan terintegrasi. Dirancang untuk mendukung pelaksanaan Computer Based Test (CBT) yang efektif, efisien, dan akurat bagi berbagai lembaga pendidikan.">
    <meta name="twitter:image" content="{{ env('APP_URL') }}/images/logo.webp">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
</head>

<body class="antialiased bg-gray-50 text-gray-900">

    <section class="min-h-[80vh] flex items-center justify-center py-20 px-4">
        <div class="max-w-4xl w-full">

            <div class="text-center mb-16" data-aos="fade-down">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-3xl shadow-xl shadow-emerald-500/10 border border-emerald-50 mb-6 group hover:rotate-6 transition-transform duration-500">
                    <img src="{{ asset('images/logo.webp') }}" alt="MS Smart Test" class="w-12 h-12 object-contain">
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">
                    Selamat Datang di <span class="text-emerald-600">MS Smart Test</span>
                </h2>
                <p class="text-gray-500 max-w-md mx-auto font-medium">
                    Silakan pilih akses login sesuai dengan peran Anda untuk melanjutkan ke dashboard sistem.
                </p>
            </div>

            @guest
                <div class="grid md:grid-cols-2 gap-8">
                    <a href="/student"
                        class="group relative bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-orange-500/10 transition-all duration-500 overflow-hidden"
                        data-aos="fade-left" data-aos-delay="200">
                        <div
                            class="absolute top-0 right-0 p-8 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity">
                            <svg class="w-32 h-32 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z" />
                            </svg>
                        </div>

                        <div class="relative z-10">
                            <div
                                class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-orange-600 group-hover:text-white transition-all duration-500">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 mb-2">Peserta Ujian</h3>
                            <p class="text-gray-500 text-sm leading-relaxed mb-6 font-medium">
                                Mulai pengerjaan ujian dengan aman dan nyaman menggunakan antarmuka modern yang responsif.
                            </p>
                            <div class="inline-flex items-center text-orange-600 font-bold text-sm gap-2">
                                Mulai Ujian Sekarang <span
                                    class="group-hover:translate-x-2 transition-transform">&rarr;</span>
                            </div>
                        </div>
                    </a>

                    <a href="/admin"
                        class="group relative bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-500 overflow-hidden"
                        data-aos="fade-right" data-aos-delay="100">
                        <div
                            class="absolute top-0 right-0 p-8 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity">
                            <svg class="w-32 h-32 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                            </svg>
                        </div>

                        <div class="relative z-10">
                            <div
                                class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 mb-2">Portal Admin & Pengajar</h3>
                            <p class="text-gray-500 text-sm leading-relaxed mb-6 font-medium">
                                Kelola bank soal, atur jadwal ujian, dan pantau hasil statistik nilai siswa secara
                                real-time.
                            </p>
                            <div class="inline-flex items-center text-emerald-600 font-bold text-sm gap-2">
                                Masuk Dashboard <span class="group-hover:translate-x-2 transition-transform">&rarr;</span>
                            </div>
                        </div>
                    </a>
                </div>
            @else
                @php
                    $isStudent = auth()->user()->role->value === \App\Enums\UserRole::STUDENT->value;
                    $targetUrl = $isStudent ? '/student' : '/admin';
                    $colorClass = $isStudent ? 'text-green-600' : 'text-emerald-600';
                    $btnColor = $isStudent
                        ? 'bg-green-600 hover:bg-green-700 shadow-green-500/20'
                        : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20';
                @endphp

                <div class="max-w-md mx-auto" data-aos="zoom-in">
                    <div
                        class="bg-white p-10 rounded-[3rem] border border-gray-100 shadow-2xl shadow-gray-200/50 text-center relative overflow-hidden">
                        <div
                            class="absolute -top-10 -right-10 w-32 h-32 {{ $isStudent ? 'bg-green-50' : 'bg-emerald-50' }} rounded-full opacity-50">
                        </div>

                        <div class="relative z-10">
                            <div
                                class="w-20 h-20 mx-auto {{ $isStudent ? 'bg-green-100 text-green-600' : 'bg-emerald-100 text-emerald-600' }} rounded-full flex items-center justify-center mb-6 border-4 border-white shadow-lg">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>

                            <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-gray-400 mb-2">Sesi Aktif</h3>
                            <h2 class="text-2xl font-black text-gray-900 mb-1">{{ auth()->user()->name }}</h2>
                            <p class="text-gray-500 font-medium mb-8">
                                Anda masuk sebagai <span
                                    class="{{ $colorClass }} font-bold">{{ auth()->user()->role->getLabel() }}</span>
                            </p>

                            <div class="flex flex-col gap-3">
                                <a href="{{ $targetUrl }}"
                                    class="w-full py-4 {{ $btnColor }} text-white rounded-2xl font-bold transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                                    Buka Dashboard
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>

                                <form action="{{ route('logout') }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit"
                                        class="w-full py-3 text-gray-400 hover:text-red-500 font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Keluar Sesi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endguest
        </div>
    </section>

    <footer class="py-6 border-t border-gray-100 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">

                <div class="flex flex-col md:flex-row items-center gap-3">
                    <div class="w-10 h-10 flex items-center justify-center shadow-sm">
                        <img src="{{ asset('images/logo.webp') }}" />
                    </div>

                    <div class="text-center md:text-left">
                        <div class="text-[14px] text-gray-700 font-bold tracking-tight">
                            &copy; {{ date('Y') }}
                            <span class="font-bold tracking-tight uppercase">
                                MS <span class="text-green-600">Smart Test</span>
                            </span>
                        </div>
                        <div class="text-[10px] text-gray-400 uppercase tracking-widest leading-tight">
                            Advanced Examination System
                        </div>
                    </div>
                </div>

                <div
                    class="flex flex-col items-center md:items-end border-t md:border-t-0 pt-4 md:pt-0 border-gray-100 w-full md:w-auto">
                    <div class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">
                        Powered by
                    </div>
                    <div class="text-[12px] font-medium text-gray-500">
                        Developed by <span class="font-bold text-gray-700">Syaifuddin Zuhri</span>
                    </div>
                </div>

            </div>
        </div>
    </footer>

</body>

</html>
