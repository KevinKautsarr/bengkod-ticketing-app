<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventHistory extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'action',
        'status',
        'keterangan',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
