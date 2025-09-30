<?php

namespace App\Models;

use App\Services\InvoiceArchivedService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = ['user_id', 'booking_id', 'total_amount',
        'paid_amount', 'remaining_amount','invoice_date','status'];


    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }

    public function archivedInvoice()
    {
        return $this->hasOne(InvoiceArchivedService::class);
    }

    public function updateAmounts()
    {
        $this->paid_amount = $this->payments()->sum('amount');
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
        $this->status = match (true){
            $this->remaining_amount <= 0 => 'paid',
            $this->paid_amount >0 => 'partial',
            default => 'unpaid'
        };
        $this->save();
    }


}
