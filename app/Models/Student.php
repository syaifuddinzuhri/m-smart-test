<?php

namespace App\Models;

use App\Enums\GenderType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'gender' => GenderType::class,
    ];

    protected static function booted()
    {
        static::deleted(function (Student $student) {
            $student->user()->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
