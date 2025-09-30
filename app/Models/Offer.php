<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
        protected $fillable = [
            'title',
            'start_date',
            'end_date',
            'discount_percentage',
            'is_active',
        ];


        public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
        {
            return $this->belongsToMany(Service::class)->withPivot('discounted_price');
        }


    public function scopeStatus($query, $status)
    {
        $now = now();
        return match ($status) {
            'active'   => $query->where('is_active', 1),
            'inactive' => $query->where('is_active', 0),
            'expired'  => $query->where('end_date', '<', $now),
            default    => $query,
        };
    }



}
