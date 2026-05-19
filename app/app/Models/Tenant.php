<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

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
}
