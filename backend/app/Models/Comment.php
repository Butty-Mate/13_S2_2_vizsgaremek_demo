<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'camping_id',
        'user_id',
        'parent_id',
        'rating',
        'comment'
    ];

    protected $casts = [
        'rating' => 'integer'
    ];

    // Melyik kempinghez
    public function camping()
    {
        return $this->belongsTo(Camping::class);
    }

    // Ki írta
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Szülő komment (ha válasz)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Válaszok erre a kommentre
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
