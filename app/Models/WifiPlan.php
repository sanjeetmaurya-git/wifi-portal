<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WifiPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'plan_type',       // 'daily', 'unlimited', 'datapack'
        'price',
        'duration_minutes',
        'limit_bytes',
        'daily_data_mb',   // MB per day (daily plans only)
        'upload_limit',
        'download_limit',
        'data_limit_mb',
        'validity_type',
        'is_active',
        'is_free',
        'profile_name'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_free'   => 'boolean',
    ];

    /** Daily data plan: X MB per day, resets at midnight */
    public function isDailyPlan(): bool
    {
        return $this->plan_type === 'daily';
    }

    /** Unlimited plan: speed-limited but no data cap */
    public function isUnlimited(): bool
    {
        return $this->plan_type === 'unlimited';
    }

    /** Data pack: one-time top-up that stacks on an active daily plan */
    public function isDataPack(): bool
    {
        return $this->plan_type === 'datapack';
    }

    public function getValidityLabelAttribute(): string
    {
        $days = intdiv($this->duration_minutes, 1440);
        if ($days >= 1) return $days . ' Day' . ($days > 1 ? 's' : '');
        $hours = intdiv($this->duration_minutes, 60);
        if ($hours >= 1) return $hours . ' Hour' . ($hours > 1 ? 's' : '');
        return $this->duration_minutes . ' Min' . ($this->duration_minutes > 1 ? 's' : '');
    }

    /** Human-readable data limit label */
    public function getDataLabelAttribute(): string
    {
        if ($this->isUnlimited()) return 'Unlimited';
        if ($this->isDataPack()) {
            return ($this->limit_bytes ?? 0) . ' MB Pack';
        }
        if ($this->daily_data_mb) {
            return $this->daily_data_mb . ' MB/Day';
        }
        return ($this->limit_bytes ?? '?') . ' MB';
    }
}
