<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'users';
    public $timestamps = true;

    protected $fillable = [
        'id', 'name', 'email', 'password', 'address', 'role', 'council_id',
        'collector_company_id', 'notifications_enabled', 'phone_number',
        'payment_status', 'user_status',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    public function collectorCompany()
    {
        return $this->belongsTo(CollectorCompany::class);
    }

    public function residency()
    {
        return $this->hasOne(Residency::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class, 'user_id');
    }

    public function collectionsAsCollector()
    {
        return $this->hasMany(Collection::class, 'collector_id');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function councilRequests()
    {
        return $this->hasMany(CouncilRequest::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}