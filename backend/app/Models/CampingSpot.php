<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampingSpot extends Model
{
    protected $fillable = [
        'camping_id',
        'name',
        'type',
        'capacity',
        'price_per_night',
        'is_available',
        'row',
        'column',
        'rating',
        'tags',
        'services'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price_per_night' => 'decimal:2',
        'rating' => 'decimal:2',
        'tags' => 'array',
        'services' => 'array'
    ];

    // Melyik kempinghez tartozik
    public function camping()
    {
        return $this->belongsTo(Camping::class);
    }

    // Foglalások erre a helyre
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
