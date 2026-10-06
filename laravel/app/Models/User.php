<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role_id',
        'employee_id',
        'customer_id',
        'is_active',
        'verification_code',
        'verification_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function websiteRating()
    {
        return $this->hasOne(WebsiteRating::class);
    }

    public function isOwner(): bool
    {
        return $this->role && strtoupper($this->role->role_name) === 'OWNER';
    }

    public function isAdmin(): bool
    {
        return $this->role && strtoupper($this->role->role_name) === 'ADMIN';
    }

    public function isStaff(): bool
    {
        return $this->role && strtoupper($this->role->role_name) === 'STAFF';
    }

    public function isCustomer(): bool
    {
        return $this->role && strtoupper($this->role->role_name) === 'CUSTOMER';
    }

    public function isOwnerOrAdmin(): bool
    {
        return $this->isOwner() || $this->isAdmin();
    }
}
