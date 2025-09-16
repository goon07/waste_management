<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $table = 'collections';
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'council_id', 'collector_company_id', 'waste_type', 'status',
        'collector_id', 'scheduled_date', 'rating', 'feedback',
        'confirmed_by_collector', 'confirmed_by_resident',
    ];

    protected $casts = [
        'confirmed_by_collector' => 'boolean',
        'confirmed_by_resident' => 'boolean',
        'scheduled_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

  public function user()
{
    return $this->belongsTo(User::class, 'user_id', 'id');
}

public function collector()
{
    return $this->belongsTo(User::class, 'collector_id', 'id');
}


    public function collectorCompany()
    {
        return $this->belongsTo(CollectorCompany::class);
    }

    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    public function wasteType()
    {
        return $this->belongsTo(WasteType::class, 'waste_type');
    }
    public function schedules()
{
    return $this->hasMany(CollectionSchedule::class, 'collection_id');
}
}