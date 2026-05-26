<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
        // Billing fields
        'plan',
        'trial_ends_at',
        'subscription_status',
        'razorpay_customer_id',
        'razorpay_subscription_id',
        'has_support_addon',
        'google_sheet_id',
        'google_sheet_email',
        'upi_id',
        'upi_number',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'qr_code_path',
        'google_sheet_instructions',
        'google_sheet_sync_mode',
    ];

    protected $casts = [
        'trial_ends_at'    => 'datetime',
        'has_support_addon' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class)->withoutGlobalScope('tenant');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->withoutGlobalScope('tenant');
    }

    public function whatsappStatus()
    {
        return $this->hasOne(WhatsappStatus::class)->withoutGlobalScope('tenant');
    }

    // ── Trial / Subscription Helpers ──────────────────────────

    /**
     * How many full days of trial remain (0 if expired or no trial set).
     */
    public function trialDaysLeft(): int
    {
        if (!$this->trial_ends_at || $this->subscription_status !== 'trialing') {
            return 0;
        }
        $diff = now()->diffInDays($this->trial_ends_at, false);
        return max(0, (int) $diff);
    }

    /**
     * How many hours of trial remain (used for sub-24h display).
     */
    public function trialHoursLeft(): int
    {
        if (!$this->trial_ends_at || $this->subscription_status !== 'trialing') {
            return 0;
        }
        $diff = now()->diffInHours($this->trial_ends_at, false);
        return max(0, (int) $diff);
    }

    /**
     * Is the trial still active (not expired)?
     */
    public function isTrialActive(): bool
    {
        return $this->subscription_status === 'trialing'
            && $this->trial_ends_at
            && now()->lt($this->trial_ends_at);
    }

    /**
     * Is the tenant on a paid active subscription?
     */
    public function isSubscribed(): bool
    {
        return $this->subscription_status === 'active';
    }

    /**
     * Single truth: can this tenant's bot generate AI replies?
     */
    public function canUseAI(): bool
    {
        return $this->isSubscribed() || $this->isTrialActive();
    }
}
