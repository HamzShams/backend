<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'image',
        'course_name',
        'teacher_name',
        'cost',
        'total_student',
        'curr_student',
        'total_hours',
        'description',
        'super_student',
        'state',
    ];

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function Reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
    
    public function Marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }
}
