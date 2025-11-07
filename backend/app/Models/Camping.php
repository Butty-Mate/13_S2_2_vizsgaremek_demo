<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camping extends Model
{
    protected $fillable = [
        'user_id',
        'camping_name',
        'location_id',
        'description',
        'image_url',
        'company_name',
        'tax_id',
        'billing_address'
    ];

    // Kemping tulajdonosa
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Kemping helyszíne
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Kemping helyei
    public function spots()
    {
        return $this->hasMany(CampingSpot::class);
    }

    // Foglalások
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Értékelések/kommentek
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
