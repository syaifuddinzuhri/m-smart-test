@php
    $panelId = filament()->getId();
    $isAdmin = $panelId === 'admin';
    $isSupervisor = $panelId === 'supervisor';
    $isStudent = $panelId === 'student'; // atau sesuaikan dengan ID panel peserta Anda
@endphp

<style>
    /* Sembunyikan Header Default */
    .fi-simple-header {
        display: none !important;
    }

    /* Efek Background Halaman (Berbeda tiap Panel) */
    body {
        @if ($isAdmin)
            background: radial-gradient(circle at top right, #ecfdf5 0%, #f8fafc 50%) !important;
        @elseif ($isSupervisor)
            background: radial-gradient(circle at top right, #eef2ff 0%, #f8fafc 50%) !important;
        @else
            background: radial-gradient(circle at top right, #fff7ed 0%, #f8fafc 50%) !important;
        @endif
    }

    /* Dekorasi Card Login */
    .fi-simple-main-ctn {
        position: relative;
        z-index: 10;
    }

    /* Glow Effect di belakang Card */
    .login-glow {
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.15;
        z-index: -1;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: {{ $isAdmin ? '#10b981' : ($isSupervisor ? '#6366f1' : '#f97316') }};
    }
</style>

<div class="relative flex flex-col items-center justify-center">
    <!-- Glow Background -->
    <div class="login-glow"></div>

    <!-- Container Logo -->
    <div class="relative mb-4">
        <div class="absolute -inset-1 rounded-full blur opacity-25 bg-emerald-400">
        </div>
        <img src="{{ asset('images/logo.webp') }}" alt="Logo"
            class="relative h-16 w-auto transition-transform hover:scale-105 duration-300">
    </div>

    <!-- Judul Aplikasi -->
    <h2 class="text-2xl font-black tracking-tight text-gray-950 dark:text-white uppercase">
        MS <span class="text-emerald-600">Smart Test</span>
    </h2>

    <!-- Badge Penanda Panel -->
    <div
        class="my-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest
        @if($isAdmin)
            bg-emerald-100 text-emerald-700 border border-emerald-200
        @elseif($isSupervisor)
            bg-indigo-100 text-indigo-700 border border-indigo-200
        @else
            bg-orange-100 text-orange-700 border border-orange-200
        @endif">

        <span class="mr-1.5 flex h-2 w-2">
            <span
                class="animate-ping absolute inline-flex h-2 w-2 rounded-full opacity-75
                {{ $isAdmin ? 'bg-emerald-400' : ($isSupervisor ? 'bg-indigo-400' : 'bg-orange-400') }}"></span>
            <span
                class="relative inline-flex rounded-full h-2 w-2
                {{ $isAdmin ? 'bg-emerald-500' : ($isSupervisor ? 'bg-indigo-500' : 'bg-orange-500') }}"></span>
        </span>

        Portal
        @if($isAdmin)
            Administrator
        @elseif($isSupervisor)
            Pengawas
        @else
            Peserta
        @endif
    </div>

    <div class="text-center px-4">
        @if ($isAdmin)
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                Kelola bank soal, sesi ujian, dan pantau aktivitas peserta secara real-time.
            </p>
        @elseif ($isSupervisor)
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                Pantau jalannya ujian, verifikasi kehadiran peserta, dan kendalikan sesi aktif.
            </p>
        @else
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                Persiapkan diri Anda, kerjakan dengan jujur dan teliti.
            </p>
        @endif
    </div>
</div>
