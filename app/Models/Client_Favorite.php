<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client_Favorite extends Model
{
    protected $fillable = ['user_id','service_id'];
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }
}
