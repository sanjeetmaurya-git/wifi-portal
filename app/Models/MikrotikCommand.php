<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikCommand extends Model
{
    protected $fillable = [
        'router_id',
        'command',
        'status',
        'attempts',
        'executed_at',
        'error_log'
    ];
}
