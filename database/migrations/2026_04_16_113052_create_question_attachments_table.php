<?php

use App\Enums\QuestionAttachmentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('attachable');
            $table->string('file_path');
            $table->enum('type', QuestionAttachmentType::values())->default(QuestionAttachmentType::FILE->value)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_attachments');
    }
};
