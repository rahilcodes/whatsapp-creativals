<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappStatus extends Model
{
    protected $table = 'whatsapp_status';

    protected $fillable = ['status', 'qr_code', 'last_connected_at'];

    protected $casts = [
        'last_connected_at' => 'datetime',
    ];

    // ── Singleton accessor ────────────────────────────────────
    public static function current(): static
    {
        return static::firstOrCreate([], [
            'status' => 'disconnected',
        ]);
    }

    // ── Update status ─────────────────────────────────────────
    public static function updateStatus(string $status, ?string $qr = null): void
    {
        $data = ['status' => $status];
        if ($qr !== null) {
            $data['qr_code'] = $qr;
        }
        if ($status === 'connected') {
            $data['last_connected_at'] = now();
            $data['qr_code'] = null; // Clear QR when connected
        }
        static::query()->update($data);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }
}
