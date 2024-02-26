<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $table = 'shipments';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'image',
        'amount',
        'state',
    ];

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
