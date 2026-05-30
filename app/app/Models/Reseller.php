<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'slug',
        'support_email',
        'contact_name',
        'logo_path',
        'favicon_path',
        'primary_color',
        'sidebar_color',
        'stripe_key',
        'stripe_secret',
        'stripe_webhook_secret',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_from_address',
        'smtp_from_name',
        'license_type',
        'license_fee',
        'license_expires_at',
        'max_clients',
        'status',
    ];

    protected $casts = [
        'license_expires_at' => 'date',
        'license_fee'        => 'integer',
        'smtp_port'          => 'integer',
        'max_clients'        => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────

    /**
     * All tenant clients that belong to this reseller.
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * All users that are admins/owners of this reseller.
     */
    public function adminUsers()
    {
        return $this->hasMany(User::class)->where('role', 'reseller_admin');
    }

    // ── Helpers ────────────────────────────────────────────────

    /**
     * Count of how many tenant client slots are used.
     */
    public function usedClientSlots(): int
    {
        return $this->tenants()->count();
    }

    /**
     * Remaining client slots available.
     */
    public function remainingClientSlots(): int
    {
        return max(0, $this->max_clients - $this->usedClientSlots());
    }

    /**
     * Is the reseller's license currently valid?
     */
    public function isLicenseValid(): bool
    {
        if ($this->license_type === 'lifetime') {
            return true;
        }
        return $this->license_expires_at && now()->lte($this->license_expires_at);
    }

    /**
     * Is the reseller active and usable?
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->isLicenseValid();
    }
}
