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
            $table->dateTime('expires_at')->nullable()->after('started_at');
            $table->json('extension_log')->nullable()->after('expires_at');
            $table->dropColumn('remaining_duration');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->json('extension_log')->nullable()->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn('extension_log');
            $table->dropColumn('expires_at');
            $table->integer('remaining_duration')->nullable()->after('finished_at');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('extension_log');
        });
    }
};
