<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'postcode',
        'county',
        'city',
        'street',
        'street_number'
    ];

    // Egy helyszínhez tartozhatnak kempingek
    public function campings()
    {
        return $this->hasMany(Camping::class);
    }
}
