<?php

namespace App\Models;

use App\Enums\ExamTokenType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamToken extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'type' => ExamTokenType::class,
        'expired_at' => 'datetime',
        'used_at' => 'datetime',
        'is_active' => 'boolean',
        'is_single_use' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
