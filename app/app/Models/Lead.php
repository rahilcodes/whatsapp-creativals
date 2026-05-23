<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasTenant;

    protected $table = 'leads';

    protected $fillable = [
        'phone',
        'captured_name',
        'captured_phone',
        'captured_email',
        'capture_stage',
        'summary',
        'intent',
        'mood',
        'lead_score',
        'human_required',
        'last_activity_at',
    ];

    protected $casts = [
        'human_required' => 'boolean',
        'lead_score' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the lead.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
