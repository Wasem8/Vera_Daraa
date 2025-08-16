<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingArchiver extends Model
{
    protected $fillable = ['booking_id', 'booking_date','user_id','service_id','status','note'
    ,'archived_at'];

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
