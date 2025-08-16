<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceArchiver extends Model
{

    protected $fillable = ['invoice_id','user_id', 'booking_id', 'total_amount',
        'paid_amount', 'remaining_amount','invoice_date','status'];


    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}


