<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'camping_id',
        'camping_spot_id',
        'arrival_date',
        'departure_date',
        'status'
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date'
    ];

    // Ki foglalta
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Melyik kemping
    public function camping()
    {
        return $this->belongsTo(Camping::class);
    }

    // Melyik hely
    public function campingSpot()
    {
        return $this->belongsTo(CampingSpot::class);
    }
}
