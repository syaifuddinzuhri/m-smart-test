<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamCategory extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
