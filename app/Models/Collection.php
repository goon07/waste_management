<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'resident_id',
        'waste_type_id',
        'collector_company_id',
        'area_id',
        'quantity',
        'priority',
        'notes',
        'feedback_rating',
        'feedback_text',
        'confirmed_by_collector',
        'confirmed_by_resident',
    ];

    public function resident()
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    public function schedules()
    {
        return $this->hasMany(CollectionSchedule::class);
    }

    public function wasteType()
    {
        return $this->belongsTo(WasteType::class);
    }

    public function collectorCompany()
    {
        return $this->belongsTo(CollectorCompany::class);
    }


public function collector()
{
    return $this->belongsTo(User::class, 'assigned_collector_id');
}


    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}