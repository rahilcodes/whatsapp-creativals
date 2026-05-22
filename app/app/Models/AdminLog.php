<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $fillable = ['user_id', 'action', 'details', 'ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class)->withoutGlobalScope('tenant');
    }

    public static function log(string $action, ?string $details = null): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
        ]);
    }
}
