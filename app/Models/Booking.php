<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    protected $fillable = ['user_id','services','booking_date','notes','status','payment_status','client_name','client_phone'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_services')
            ->withTimestamps();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);

    }

    public function archive()
    {
        return $this->hasOne(BookingArchiver::class);
    }


   public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne
   {
       return $this->hasOne(Invoice::class);
   }



}
