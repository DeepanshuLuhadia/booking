<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'price', 'max_employees', 'features', 'is_active'];
    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    /** A no-cost plan — the trial every new shop starts on. */
    public function isFree(): bool
    {
        return (float) $this->price <= 0;
    }

    /**
     * The top tier, by name rather than by price.
     *
     * Matching on the name is what was asked for, and it survives a price
     * change; the cost is that renaming the plan in the admin Plans screen
     * silently revokes premium-only features from everyone on it. If that
     * becomes a problem, move this to a flag on the plan row.
     */
    public function isPremium(): bool
    {
        return strtolower(trim((string) $this->name)) === 'premium';
    }
}
