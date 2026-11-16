<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use BelongsToProperty;

    protected $guarded = ['id'];
}
