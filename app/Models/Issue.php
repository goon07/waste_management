<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $table = 'issues';
    public $timestamps = true;

    protected $fillable = ['user_id', 'council_id', 'collector_company_id', 'issue_type', 'description', 'status'];

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