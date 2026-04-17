<?php

use App\Enums\ExamTokenType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('token', 10)->unique();
            $table->enum('type', ExamTokenType::values())->default(ExamTokenType::ACCESS->value)->index();
            $table->boolean('is_single_use')->default(true);
            $table->integer('used_count')->default(0);
            $table->dateTime('used_at')->nullable();
            $table->dateTime('expired_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_tokens');
    }
};
