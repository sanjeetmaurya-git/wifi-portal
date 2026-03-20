<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'wifi_plan_id', 'order_id', 'payment_id', 'amount', 'status'];
    //in step 22
    public function user()
    {
        return $this->belongsTo(WifiUser::class, 'user_id');
    }

    public function plan()
    {
        return $this->belongsTo(WifiPlan::class, 'wifi_plan_id');
    }
}
