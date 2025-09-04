<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteType extends Model
{
    protected $table = 'waste_types';
    public $timestamps = true;

    protected $fillable = ['name', 'description'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function collections()
    {
        return $this->hasMany(Collection::class, 'waste_type');
    }
}