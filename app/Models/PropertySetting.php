<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertySetting extends Model
{
    protected $fillable = ['property_id', 'key', 'value'];
}
