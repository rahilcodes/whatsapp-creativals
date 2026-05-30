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

        $tenant = \App\Models\Tenant::find($this->lead->tenant_id);
        if ($tenant && $tenant->google_sheet_sync_mode === 'smart_read_write') {
            // Keep Row 1 completely clean on visual calendar sheets — AI handles coordinates via update_sheet_ranges
            return;
        }

        $sheetsService->syncLeadToSheet($this->lead);
    }
}
