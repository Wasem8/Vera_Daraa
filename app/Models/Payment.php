<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public function bookings()
    {
        return $this->belongsTo(Booking::class);
    }
}
