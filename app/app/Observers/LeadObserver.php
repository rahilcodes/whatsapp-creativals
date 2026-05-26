<?php

namespace App\Observers;

use App\Models\Lead;

class LeadObserver
{
    public function created(Lead $lead): void
    {
        \App\Jobs\SyncLeadToGoogleSheet::dispatch($lead);
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        \App\Jobs\SyncLeadToGoogleSheet::dispatch($lead);
    }

    /**
     * Handle the Lead "deleted" event.
     */
    public function deleted(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "restored" event.
     */
    public function restored(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "force deleted" event.
     */
    public function forceDeleted(Lead $lead): void
    {
        //
    }
}
