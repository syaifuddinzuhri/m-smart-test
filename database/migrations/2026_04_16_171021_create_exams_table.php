<?php

use App\Enums\ExamStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_category_id')->constrained('exam_categories')->restrictOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->restrictOnDelete();

            $table->enum('status', ExamStatus::values())->default(ExamStatus::DRAFT->value);

            $table->string('title')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration');

            // Poin Benar
            $table->decimal('point_pg', 5, 2)->default(1.00);
            $table->decimal('point_short_answer', 5, 2)->default(1.00);
            $table->decimal('point_essay_max', 5, 2)->default(10.00);

            // Poin Salah (Penalty)
            $table->decimal('point_pg_wrong', 5, 2)->default(0.00);
            $table->decimal('point_short_answer_wrong', 5, 2)->default(0.00);

            // Poin Kosong (Null)
            $table->decimal('point_pg_null', 5, 2)->default(0.00);
            $table->decimal('point_short_answer_null', 5, 2)->default(0.00);
            $table->decimal('point_essay_null', 5, 2)->default(0.00);

            // Pengaturan Ujian
            $table->tinyInteger('random_question_type')->default(0)->comment('0:Off, 1:Individu, 2:Massal');
            $table->tinyInteger('random_option_type')->default(0);
            $table->boolean('can_resume')->default(true);
            $table->boolean('is_graded')->default(false);
            $table->boolean('show_result_to_student')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
