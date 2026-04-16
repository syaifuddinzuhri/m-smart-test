<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasUuids;
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function attachments()
    {
        return $this->morphMany(QuestionAttachment::class, 'attachable');
    }
}
