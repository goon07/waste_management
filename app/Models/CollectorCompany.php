<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectorCompany extends Model
{
    protected $table = 'collector_companies';
    public $timestamps = true;

    protected $fillable = [
        'name', 'contact_email', 'phone_number', 'address', 'license_number', 'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function councilRequests()
    {
        return $this->hasMany(CouncilRequest::class);
    }

    public function residencies()
    {
        return $this->hasMany(Residency::class);
    }

    public function councils()
    {
        return $this->belongsToMany(Council::class, 'council_collector_companies', 'collector_company_id', 'council_id');
    }

        public function collectors()
    {
        return $this->hasMany(User::class, 'collector_company_id')
                    ->whereIn('role', ['company_admin', 'collector']);
    }

    public function areas()
{
    return $this->belongsToMany(Area::class, 'collector_company_areas', 'collector_company_id', 'area_id');
}

public function companyAdmin()
{
    return $this->hasOne(User::class, 'collector_company_id')
                ->where('role', 'company_admin');
}

}