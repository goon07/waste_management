<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperadminLog extends Model
{
    protected $fillable = ['action', 'details'];
}