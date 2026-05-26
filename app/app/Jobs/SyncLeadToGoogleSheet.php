<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncLeadToGoogleSheet implements ShouldQueue
{
    use Queueable;

    private \App\Models\Lead $lead;

    /**
     * Create a new job instance.
     */
    public function __construct(\App\Models\Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\GoogleSheetsService $sheetsService): void
    {
        // Scope the session to the lead's tenant to pass correct model parameters
        app()->instance('tenant_id', $this->lead->tenant_id);

        $sheetsService->syncLeadToSheet($this->lead);
    }
}
