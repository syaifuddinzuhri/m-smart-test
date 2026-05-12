<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->decimal('point_true_false', 5, 2)->default(1.00)->after('point_pg_null');
            $table->decimal('point_true_false_wrong', 5, 2)->default(0.00)->after('point_true_false');
            $table->decimal('point_true_false_null', 5, 2)->default(0.00)->after('point_true_false_wrong');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['point_true_false', 'point_true_false_wrong', 'point_true_false_null']);
        });
    }
};
