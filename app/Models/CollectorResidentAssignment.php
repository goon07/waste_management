<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CollectorResidentAssignment extends Model
{
    use HasFactory;

    protected $table = 'collector_resident_assignments';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'collector_id',
        'resident_id',
        'collector_company_id',
    ];

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function resident()
    {
        return $this->belongsTo(User::class, 'resident_id');
    }
    public function collectorCompany()
{
    return $this->belongsTo(CollectorCompany::class, 'collector_company_id');
}
}
