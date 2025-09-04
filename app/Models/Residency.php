<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Residency extends Model
{
    protected $table = 'residency';
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'council_id', 'collector_company_id', 'household_size',
        'waste_collection_frequency', 'billing_address', 'latitude', 'longitude',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    public function collectorCompany()
    {
        return $this->belongsTo(CollectorCompany::class);
    }
}