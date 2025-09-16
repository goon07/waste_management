<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Council extends Model
{
    protected $table = 'councils';
    public $timestamps = true;

    protected $fillable = [
        'name', 'region', 'contact_email', 'phone_number', 'address', 'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function councilRequests()
    {
        return $this->hasMany(CouncilRequest::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function residencies()
    {
        return $this->hasMany(Residency::class);
    }

    public function collectorCompanies()
    {
        return $this->belongsToMany(CollectorCompany::class, 'council_collector_companies', 'council_id', 'collector_company_id');
    }
    public function areas()
{
    return $this->hasMany(Area::class);
}

}