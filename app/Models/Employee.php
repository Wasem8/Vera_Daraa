<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'user_id',
        'department_id',
        'phone',
        'image',
        'specialty',
        'hire_date',
        'archived_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function supervisor()
    {
        return $this->hasOne(Employee::class, 'supervisor_id');
    }
}
