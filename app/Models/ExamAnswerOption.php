<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot; // WAJIB PIVOT

class ExamAnswerOption extends Pivot
{
    protected $table = 'exam_answer_options';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
