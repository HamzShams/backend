<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    use HasFactory;

    protected $table = 'marks';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'course_id',
        'rate',
    ];

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function Course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
