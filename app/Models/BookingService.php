<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{



    protected $fillable = [
        'booking_id','service_id','staff_id','price','duration','start_time','end_time'
    ];


    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];


    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(Booking::class); }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(Service::class); }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
