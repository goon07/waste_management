<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouncilRequest extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = true;
//    protected $keyType = 'bigInteger';

    protected $fillable = [
        'user_id', 'council_id', 'status', 'requested_at',
    ];

    protected $casts = [
        'user_id' => 'string', // Treat as UUID
        'requested_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function council()
    {
        return $this->belongsTo(Council::class);
    }
}