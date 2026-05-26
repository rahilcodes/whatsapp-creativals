<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Lead;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    private ?\Google\Client $client = null;
    private bool $isConfigured = false;

    public function __construct()
    {
        $jsonPath = storage_path('app/google-service-account.json');
        
        if (!file_exists($jsonPath)) {
            Log::warning("Google Sheets Service Account credentials not found at: {$jsonPath}. Running in Mock / Standby Mode.");
            return;
        }

        try {
            $config = json_decode(file_get_contents($jsonPath), true);
            if (empty($config) || ($config['project_id'] ?? '') === 'ichatup-local') {
                Log::info("Google Sheets Service Account is configured with local mock keys. Standby Mode active.");
                return;
            }

            $client = new \Google\Client();
            $client->setAuthConfig($jsonPath);
            $client->addScope([\Google\Service\Sheets::SPREADSHEETS, \Google\Service\Drive::DRIVE]);
            $client->setAccessType('offline');

            $this->client = $client;
            $this->isConfigured = true;
            Log::info("Google Sheets Service Account authenticated successfully.");
        } catch (\Throwable $e) {
            Log::error("Google Sheets authentication exception: " . $e->getMessage());
        }
    }

    /**
     * Is the Google Client fully configured and authenticated?
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Create a brand new leads sheet for a tenant and share it with their email.
     */
    public function createSheetForTenant(Tenant $tenant, string $shareEmail): ?string
    {
        if (!$this->isConfigured) {
            Log::info("Mock Sheets: Simulating dynamic sheet creation for Tenant {$tenant->id} shared with {$shareEmail}.");
            $mockId = 'mock_sheet_' . uniqid();
            $tenant->update([
                'google_sheet_id' => $mockId,
                'google_sheet_email' => $shareEmail,
            ]);
            return $mockId;
        }

        try {
            $driveService = new \Google\Service\Drive($this->client);
            $sheetsService = new \Google\Service\Sheets($this->client);

            // 1. Create the spreadsheet
            $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
                'properties' => [
                    'title' => "iChatUp Leads — " . ($tenant->name ?? "My Business")
                ]
            ]);

            $spreadsheet = $sheetsService->spreadsheets->create($spreadsheet, [
                'fields' => 'spreadsheetId'
            ]);

            $sheetId = $spreadsheet->spreadsheetId;
            Log::info("Google Sheets: Spreadsheet created successfully with ID: {$sheetId}");

            // 2. Write the Header Row
            $headerValues = [
                ['Captured Name', 'Phone Number', 'Email Address', 'Lead Score', 'Lifecycle Stage', 'Customer Intent', 'Customer Mood', 'Last Activity', 'Key Details / Summary']
            ];

            $body = new \Google\Service\Sheets\ValueRange([
                'values' => $headerValues
            ]);

            $sheetsService->spreadsheets_values->update($sheetId, 'Sheet1!A1:I1', $body, [
                'valueInputOption' => 'RAW'
            ]);

            // 3. Share sheet with client's business email
            $permission = new \Google\Service\Drive\Permission([
                'type' => 'user',
                'role' => 'writer',
                'emailAddress' => $shareEmail,
            ]);

            // Baileys Drive API v3 permissions create
            $driveService->permissions->create($sheetId, $permission, [
                'sendNotificationEmail' => true
            ]);

            Log::info("Google Sheets: Shared spreadsheet {$sheetId} with {$shareEmail} successfully.");

            // 4. Update the Tenant database record
            $tenant->update([
                'google_sheet_id' => $sheetId,
                'google_sheet_email' => $shareEmail,
            ]);

            ActivityLog::record('sheets_connected', "Connected Google Sheet ID {$sheetId} and shared with {$shareEmail}", null);

            return $sheetId;

        } catch (\Throwable $e) {
            Log::error("Google Sheets createSheetForTenant Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Sync/update a specific lead to the Google Sheet.
     */
    public function syncLeadToSheet(Lead $lead): bool
    {
        $tenant = Tenant::find($lead->tenant_id);
        if (!$tenant || empty($tenant->google_sheet_id)) {
            return false;
        }

        $sheetId = $tenant->google_sheet_id;

        if (!$this->isConfigured) {
            Log::info("Mock Sheets: Simulating row sync for Lead {$lead->phone} to sheet {$sheetId}.");
            return true;
        }

        try {
            $sheetsService = new \Google\Service\Sheets($this->client);

            // Fetch all current values in column B (Phone) to locate existing lead
            $range = 'Sheet1!A:I';
            $response = $sheetsService->spreadsheets_values->get($sheetId, $range);
            $rows = $response->getValues() ?? [];

            $leadRowIndex = -1;
            // Phone is at index 1 (Column B)
            foreach ($rows as $index => $row) {
                if (isset($row[1]) && $row[1] === $lead->phone) {
                    $leadRowIndex = $index + 1; // 1-indexed row number
                    break;
                }
            }

            // Prepare values to write
            $rowValues = [[
                $lead->captured_name ?? 'N/A',
                $lead->phone,
                $lead->captured_email ?? '',
                $lead->lead_score ?? 0,
                $lead->capture_stage ?? 'new',
                $lead->intent ?? 'N/A',
                $lead->mood ?? 'N/A',
                $lead->last_activity_at ? $lead->last_activity_at->format('Y-m-d H:i:s') : 'N/A',
                $lead->summary ?? '',
            ]];

            $body = new \Google\Service\Sheets\ValueRange([
                'values' => $rowValues
            ]);

            if ($leadRowIndex !== -1) {
                // Update existing row
                $updateRange = "Sheet1!A{$leadRowIndex}:I{$leadRowIndex}";
                $sheetsService->spreadsheets_values->update($sheetId, $updateRange, $body, [
                    'valueInputOption' => 'RAW'
                ]);
                Log::info("Google Sheets: Updated row {$leadRowIndex} for lead {$lead->phone} in sheet {$sheetId}.");
            } else {
                // Append new row
                $appendRange = 'Sheet1!A1';
                $sheetsService->spreadsheets_values->append($sheetId, $appendRange, $body, [
                    'valueInputOption' => 'RAW',
                    'insertDataOption' => 'INSERT_ROWS'
                ]);
                Log::info("Google Sheets: Appended new row for lead {$lead->phone} in sheet {$sheetId}.");
            }

            return true;

        } catch (\Throwable $e) {
            Log::error("Google Sheets syncLeadToSheet Exception: " . $e->getMessage());
            return false;
        }
    }
}
