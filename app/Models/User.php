<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Profile;
use App\Models\Notification;


class User extends Authenticatable implements  MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable,HasApiTokens,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getRoleAttribute()
    {
        return $this->roles()->first()?->name;
    }
    protected $appends = ['role'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Employee::class);

    }

    public function deviceTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function routeNotificationForFcm($notification = null): array
    {
        return $this->deviceTokens()->pluck('token')->toArray();
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->notifications()->whereNull('read_at');
    }

}
