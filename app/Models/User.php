<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'balance',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function Courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function Events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function Shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
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
