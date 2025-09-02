<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    protected $fillable = ['user_id','service_id','offer_id','booking_date','notes','status','payment_status'];

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class);
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

    public function offer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }


    public function getFinalPriceAttribute()
    {
        $basePrice = $this->service ? $this->service->price : 0;

        if ($this->offer) {
            $discount = $this->offer->discount_percentage ?? 0;
            return $basePrice - ($basePrice * $discount / 100);
        }

        return $basePrice;
    }


}
