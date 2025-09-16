<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    public $timestamps = true;

    protected $fillable = [
        'council_id', 'name', 'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    public function residencies()
    {
        return $this->hasMany(Residency::class);
    }

    public function collectorCompanies()
    {
        return $this->belongsToMany(CollectorCompany::class, 'collector_company_areas', 'area_id', 'collector_company_id');
    }
}
