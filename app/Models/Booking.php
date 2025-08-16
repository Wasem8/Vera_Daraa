<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    protected $fillable = ['user_id','service_id','booking_date','notes','status','payment_status'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

  public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
  {
      return $this->belongsTo(Service::class);
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
