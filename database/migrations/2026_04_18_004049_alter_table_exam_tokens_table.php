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
        Schema::table('exam_tokens', function (Blueprint $table) {
            $table->index('token');
            $table->index('exam_id');
            $table->index('expired_at');
            $table->index(['is_single_use', 'used_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_tokens', function (Blueprint $table) {
            $table->dropIndex(['token']);
            $table->dropIndex(['exam_id']);
            $table->dropIndex(['expired_at']);
            $table->dropIndex(['is_single_use', 'used_at']);
        });
    }
};
