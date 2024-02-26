<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'image',
        'event_name',
        'place',
        'date',
        'description',
    ];

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
