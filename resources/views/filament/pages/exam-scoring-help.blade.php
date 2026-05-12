<div class="space-y-6">
    <!-- INFO PARAMETER -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border dark:border-gray-700">
            <h4 class="font-bold text-xs uppercase text-blue-600 mb-2">Pilihan Ganda (30)</h4>
            <ul class="text-[11px] space-y-1">
                <li>Benar: <span class="font-bold">+1.00</span></li>
                <li>Salah: <span class="font-bold text-red-500">-0.50</span></li>
                <li>Kosong: <span class="font-bold text-gray-500">-0.20</span></li>
            </ul>
        </div>
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border dark:border-gray-700">
            <h4 class="font-bold text-xs uppercase text-violet-600 mb-2">PG True/False (10)</h4>
            <ul class="text-[11px] space-y-1">
                <li>Benar: <span class="font-bold">+1.00</span></li>
                <li>Salah: <span class="font-bold text-red-500">-0.25</span></li>
                <li>Kosong: <span class="font-bold text-gray-500">-0.10</span></li>
            </ul>
        </div>
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border dark:border-gray-700">
            <h4 class="font-bold text-xs uppercase text-green-600 mb-2">Isian Singkat (5)</h4>
            <ul class="text-[11px] space-y-1">
                <li>Benar: <span class="font-bold">+2.00</span></li>
                <li>Salah: <span class="font-bold">0.00</span></li>
                <li>Kosong: <span class="font-bold text-gray-500">-1.00</span></li>
            </ul>
        </div>
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border dark:border-gray-700">
            <h4 class="font-bold text-xs uppercase text-orange-600 mb-2">Essay (5)</h4>
            <ul class="text-[11px] space-y-1">
                <li>Max Poin: <span class="font-bold">+10.00</span></li>
                <li>Kosong: <span class="font-bold text-red-500">-5.00</span></li>
            </ul>
        </div>
    </div>

    <!-- PENJELASAN MAX SCORE -->
    <div class="text-[11px] bg-blue-50 p-2 rounded border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800">
        <strong>Total Poin Maksimal Mentah (Raw):</strong>
        (30 PG × 1) + (10 TF × 1) + (5 Isian × 2) + (5 Essay × 10) = <strong>100.00</strong>
        <span class="ml-2 text-gray-500">→ Skala 100 = nilai langsung sesuai raw.</span>
    </div>

    <!-- TABEL SIMULASI -->
    <div class="overflow-x-auto border rounded-lg">
        <table class="w-full text-[11px] text-left">
            <thead class="bg-gray-100 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-2">Skenario Kejadian</th>
                    <th class="px-4 py-2 text-center">Detail Skor Mentah</th>
                    <th class="px-4 py-2 text-center bg-blue-50 dark:bg-blue-900/30">Total Akhir (Skala 100)</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <!-- SEMPURNA -->
                <tr>
                    <td class="px-4 py-3">
                        <span class="font-bold text-green-600">Skenario 1: Sempurna</span><br>
                        Semua PG & TF benar, Isian benar, Essay skor maksimal.
                    </td>
                    <td class="px-4 py-3 text-center">
                        PG: 30.00<br>
                        TF: 10.00<br>
                        Isian: 10.00<br>
                        Essay: 50.00
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-lg text-blue-600">100.00</td>
                </tr>

                <!-- MENENGAH -->
                <tr>
                    <td class="px-4 py-3">
                        <span class="font-bold text-amber-600">Skenario 2: Menengah</span><br>
                        PG: 20 Benar, 5 Salah, 5 Kosong<br>
                        TF: 7 Benar, 2 Salah, 1 Kosong<br>
                        Isian: 3 Benar, 2 Kosong<br>
                        Essay: 2 Soal (7.5 poin/soal), 3 Kosong
                    </td>
                    <td class="px-4 py-3 text-center">
                        PG: 16.50 <small>(20 − 2.5 − 1)</small><br>
                        TF: 6.40 <small>(7 − 0.5 − 0.1)</small><br>
                        Isian: 4.00 <small>(6 − 2)</small><br>
                        Essay: 0.00 <small>(15 − 15)</small>
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-lg text-blue-600">26.90</td>
                </tr>

                <!-- KOREKSI MANUAL -->
                <tr class="bg-green-50/50 dark:bg-green-900/10">
                    <td class="px-4 py-3">
                        <span class="font-bold text-green-700">Skenario 3: Koreksi Manual</span><br>
                        PG: 25 Benar, 5 Salah<br>
                        TF: 8 Benar, 2 Salah<br>
                        Isian: 4 Benar, 1 Salah<br>
                        Essay: 5 Soal (dinilai 5.00/soal)
                    </td>
                    <td class="px-4 py-3 text-center">
                        PG: 22.50 <small>(25 − 2.5)</small><br>
                        TF: 7.50 <small>(8 − 0.5)</small><br>
                        Isian: 8.00 <small>(4 × 2)</small><br>
                        Essay: 25.00 <small>(5 × 5)</small>
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-lg text-green-600">63.00</td>
                </tr>

                <!-- BURUK -->
                <tr>
                    <td class="px-4 py-3 text-red-600">
                        <span class="font-bold">Skenario 4: Buruk</span><br>
                        PG: 15 Benar, 15 Kosong<br>
                        TF: semua 10 Kosong<br>
                        Isian & Essay: semua <strong>KOSONG</strong>.
                    </td>
                    <td class="px-4 py-3 text-center text-red-500">
                        PG: 12.00 <small>(15 − 3)</small><br>
                        TF: −1.00 <small>(Pinalti)</small><br>
                        Isian: −5.00 <small>(Pinalti)</small><br>
                        Essay: −25.00 <small>(Pinalti)</small>
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-lg text-gray-400">
                        0.00<br>
                        <span class="text-[9px] font-normal italic">(Raw −19.00 dibulatkan 0)</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- TIPS -->
    <div class="bg-amber-50 p-4 rounded-lg border border-amber-200 dark:bg-amber-900/20 dark:border-amber-800">
        <h5 class="text-xs font-bold text-amber-800 dark:text-amber-400 uppercase mb-1">💡 Tips Pembobotan:</h5>
        <p class="text-[11px] text-amber-700 dark:text-amber-300 leading-relaxed">
            Poin <strong>PG (True/False)</strong> bisa dibedakan dari PG biasa karena soal Benar/Salah umumnya
            lebih mudah tebak. Gunakan pinalti salah yang lebih kecil untuk TF agar tidak terlalu memberatkan.
            Pada <strong>Skenario 4</strong>, nilai menjadi 0 karena "hutang" pinalti Essay yang kosong (−25 poin)
            jauh melampaui poin PG yang dijawab (+12 poin). Pastikan <strong>Poin Kosong</strong> tidak terlalu
            besar dibandingkan poin benar.
        </p>
    </div>
</div>
