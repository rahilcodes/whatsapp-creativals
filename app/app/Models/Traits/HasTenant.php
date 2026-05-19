<?php

namespace App\Models\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait HasTenant
{
    /**
     * Boot the trait.
     */
    protected static function bootHasTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            // Only apply if tenant is bound in container (meaning it's set by middleware)
            if (app()->has('tenant_id')) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', app('tenant_id'));
            }
        });

        static::creating(function ($model) {
            if (app()->has('tenant_id') && !$model->tenant_id) {
                $model->tenant_id = app('tenant_id');
            }
        });
    }

    /**
     * Relationship to the tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
