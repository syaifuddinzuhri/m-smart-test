<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'question_type' => QuestionType::class,
        'correct_answer' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($question) {
            if ($question->examQuestions()->exists()) {
                throw new \Exception("Soal ini tidak bisa dihapus karena sudah digunakan dalam Ujian.");
            }
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questionCategory(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function attachments()
    {
        return $this->morphMany(QuestionAttachment::class, 'attachable');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'question_id');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
            ->withPivot('order')
            ->withTimestamps();
    }

    public function questionOrders(): HasMany
    {
        return $this->hasMany(ExamQuestionOrder::class, 'question_id');
    }
}
