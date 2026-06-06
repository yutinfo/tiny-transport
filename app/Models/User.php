<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';
    public const ROLE_DRIVER = 'driver';

    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_STAFF,
            self::ROLE_DRIVER,
        ];
    }

    public static function roleLabels(): array
    {
        return [
            self::ROLE_ADMIN => 'ผู้ดูแลระบบ',
            self::ROLE_STAFF => 'พนักงาน',
            self::ROLE_DRIVER => 'คนขับรถ',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role_name === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role_name === self::ROLE_STAFF;
    }

    public function isDriver(): bool
    {
        return $this->role_name === self::ROLE_DRIVER;
    }

    protected $table = 'users';

    protected $dates = [
        'username_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'username',
        'password',
        'name',
        'last_name',
        'email',
        'status',
        'role_name',
        'username_verified_at',
        'remember_token'
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function assignedTrips()
    {
        return $this->hasMany(Trip::class, 'driver_user_id');
    }
}
