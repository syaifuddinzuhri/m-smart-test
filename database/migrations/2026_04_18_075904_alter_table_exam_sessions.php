<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->decimal('total_score', 8, 2)->default(0)->comment('Total Nilai Akhir')->after('remaining_duration');
            $table->decimal('score_essay', 8, 2)->default(0)->comment('Poin Essay')->after('remaining_duration');
            $table->decimal('score_short_answer', 8, 2)->default(0)->comment('Poin Isian Singkat')->after('remaining_duration');
            $table->decimal('score_pg', 8, 2)->default(0)->comment('Poin Pilihan Ganda')->after('remaining_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn('score_pg');
            $table->dropColumn('score_short_answer');
            $table->dropColumn('score_essay');
            $table->dropColumn('total_score');
        });
    }
};
