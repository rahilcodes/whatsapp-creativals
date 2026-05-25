<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix existing tenants that have NULL trial_ends_at.
     * Sets trial to 7 days from their account creation date.
     * This only affects tenants who onboarded before this fix.
     */
    public function up(): void
    {
        DB::table('tenants')
            ->whereNull('trial_ends_at')
            ->where('subscription_status', 'trialing')
            ->orderBy('id')
            ->chunkById(50, function ($tenants) {
                foreach ($tenants as $tenant) {
                    // Give them 7 days from when their account was created
                    // so existing users aren't immediately locked out
                    $trialEnd = \Carbon\Carbon::parse($tenant->created_at)->addDays(7);

                    // But don't give them a trial end date in the past —
                    // if created more than 7 days ago with no trial set,
                    // give them at least 3 more days as grace period
                    if ($trialEnd->isPast()) {
                        $trialEnd = now()->addDays(3);
                    }

                    DB::table('tenants')
                        ->where('id', $tenant->id)
                        ->update(['trial_ends_at' => $trialEnd]);
                }
            });
    }

    public function down(): void
    {
        // Non-destructive — no rollback needed
    }
};
