<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MediaCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up compressed receipts and transaction screenshots older than 3 months (90 days) to save disk space and enforce privacy retention policies.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = now()->subMonths(3);
        $this->info("🧹 Running media cleanup. Deleting all screenshots older than: " . $cutoff->format('Y-m-d H:i:s'));

        // Query messages across ALL tenants
        $messages = \App\Models\Message::withoutGlobalScopes()
            ->whereNotNull('image_path')
            ->where('created_at', '<', $cutoff)
            ->get();

        $count = 0;
        foreach ($messages as $msg) {
            $relativePath = $msg->image_path; // e.g. "storage/receipts/18/receipt_123.jpg"
            
            // Map "storage/" to "app/public/" in storage path
            $fullPath = storage_path("app/public/" . str_replace('storage/', '', $relativePath));

            if (file_exists($fullPath)) {
                try {
                    unlink($fullPath);
                    $this->line("🗑️ Deleted file: " . $relativePath);
                } catch (\Throwable $e) {
                    $this->error("⚠️ Could not delete file: " . $fullPath . " - " . $e->getMessage());
                }
            } else {
                $this->warn("❓ File already missing on disk: " . $relativePath);
            }

            // Clear column in database
            $msg->image_path = null;
            $msg->save();
            $count++;
        }

        $this->info("✅ Media cleanup finished. Removed {$count} old media screenshots.");
    }
}
