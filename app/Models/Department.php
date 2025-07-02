<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{

    public function services()
    {
        return $this->hasMany(Service::class);
    }

   protected $fillable = ['name','suite_no','supervisor_id','description'];
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }


}
