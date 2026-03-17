<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WifiPlan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration_minutes',
        'upload_limit',
        'download_limit',
        'data_limit_mb',
        'validity_type',
        'is_active'
    ];
}
