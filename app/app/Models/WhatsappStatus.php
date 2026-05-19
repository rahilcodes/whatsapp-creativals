<?php

namespace App\Models;

use App\Models\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;

class WhatsappStatus extends Model
{
    use HasTenant;

    protected $table = 'whatsapp_status';

    protected $fillable = [
        'status',
        'session_state',
        'health_score',
        'ban_risk',
        'reconnect_count',
        'last_connected_at',
        'qr_code',
    ];

    protected $casts = [
        'ban_risk'          => 'boolean',
        'health_score'      => 'integer',
        'reconnect_count'   => 'integer',
        'last_connected_at' => 'datetime',
    ];

    // ── Get the status row for the current tenant ────────────
    public static function current(): static
    {
        $tenantId = app()->has('tenant_id') ? app('tenant_id') : 1;
        // firstOrCreate scoped through HasTenant global scope automatically
        return static::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'status'          => 'disconnected',
                'session_state'   => 'idle',
                'health_score'    => 100,
                'ban_risk'        => false,
                'reconnect_count' => 0,
                'tenant_id'       => $tenantId,
            ]
        );
    }

    // ── Update status + all health fields from Node.js ────────
    public static function updateStatus(string $status, array $health = []): void
    {
        $row = static::current();

        $data = ['status' => $status];

        if (isset($health['session_state']))   $data['session_state']   = $health['session_state'];
        if (isset($health['health_score']))    $data['health_score']    = (int) $health['health_score'];
        if (isset($health['ban_risk']))        $data['ban_risk']        = (bool) $health['ban_risk'];
        if (isset($health['reconnect_count'])) $data['reconnect_count'] = (int) $health['reconnect_count'];

        if ($status === 'connected') {
            $data['last_connected_at'] = now();
        }

        if (isset($health['last_connected_at']) && $health['last_connected_at']) {
            $data['last_connected_at'] = $health['last_connected_at'];
        }

        $row->update($data);
    }

    // ── Update QR code ────────────────────────────────────────
    public static function updateQr(?string $qrBase64): void
    {
        static::current()->update(['qr_code' => $qrBase64]);
    }

    // ── Convenience checks ────────────────────────────────────
    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function isBanRisk(): bool
    {
        return (bool) $this->ban_risk;
    }

    public function isPaused(): bool
    {
        return $this->session_state === 'paused';
    }

    // ── Health label for UI ───────────────────────────────────
    public function healthLabel(): string
    {
        $score = $this->health_score ?? 100;
        if ($score >= 80) return 'excellent';
        if ($score >= 60) return 'good';
        if ($score >= 40) return 'warning';
        if ($score >= 20) return 'danger';
        return 'critical';
    }
}
