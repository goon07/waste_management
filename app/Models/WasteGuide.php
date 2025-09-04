<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteGuide extends Model
{
    protected $fillable = ['item_name', 'category', 'description'];
}
