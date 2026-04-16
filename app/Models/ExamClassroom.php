<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamClassroom extends Pivot
{
    use HasUuids;

    protected $table = 'exam_classrooms';

    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];
}
