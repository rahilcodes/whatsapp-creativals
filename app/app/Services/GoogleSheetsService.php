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
        
        if (!file_exists($jsonPath) || app()->environment('testing')) {
            Log::info("Google Sheets Service Account: Running in Mock / Standby Mode due to missing key or testing environment.");
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

            // Disable SSL verification on localhost to prevent Windows cURL error 60
            if (config('app.env') === 'local') {
                $guzzleClient = new \GuzzleHttp\Client([
                    'verify' => false
                ]);
                $client->setHttpClient($guzzleClient);
            }

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
            $sheetName = $this->getFirstSheetTitle($sheetId);

            // Fetch all current values in column B (Phone) to locate existing lead
            $range = "{$sheetName}!A:I";
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
                $updateRange = "{$sheetName}!A{$leadRowIndex}:I{$leadRowIndex}";
                $sheetsService->spreadsheets_values->update($sheetId, $updateRange, $body, [
                    'valueInputOption' => 'RAW'
                ]);
                Log::info("Google Sheets: Updated row {$leadRowIndex} for lead {$lead->phone} in sheet {$sheetId}.");
            } else {
                // Append new row
                $appendRange = "{$sheetName}!A1";
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

    /**
     * Initialize an existing sheet by writing the headers if they are missing.
     */
    public function initializeSheetHeaders(string $sheetId): bool
    {
        if (!$this->isConfigured) {
            return true;
        }

        try {
            $sheetsService = new \Google\Service\Sheets($this->client);
            $sheetName = $this->getFirstSheetTitle($sheetId);
            
            // Check if headers exist
            $range = "{$sheetName}!A1:I1";
            $response = $sheetsService->spreadsheets_values->get($sheetId, $range);
            $values = $response->getValues();

            if (empty($values) || empty($values[0])) {
                $headerValues = [
                    ['Captured Name', 'Phone Number', 'Email Address', 'Lead Score', 'Lifecycle Stage', 'Customer Intent', 'Customer Mood', 'Last Activity', 'Key Details / Summary']
                ];

                $body = new \Google\Service\Sheets\ValueRange([
                    'values' => $headerValues
                ]);

                $sheetsService->spreadsheets_values->update($sheetId, "{$sheetName}!A1:I1", $body, [
                    'valueInputOption' => 'RAW'
                ]);
            }
            return true;
        } catch (\Throwable $e) {
            Log::error("Google Sheets initializeSheetHeaders Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve headers and rows dynamically from Sheet1.
     */
    public function getSheetValues(string $sheetId): array
    {
        if (!$this->isConfigured) {
            // Mock data if in standby mode or testing
            return [
                'headers' => ['Roll Number', 'Student Name', 'Attendance', 'Marks'],
                'rows' => [
                    ['101', 'Rahul Kumar', '95%', '88'],
                    ['102', 'Sarah Jones', '98%', '94'],
                ]
            ];
        }

        try {
            $sheetsService = new \Google\Service\Sheets($this->client);
            $sheetName = $this->getFirstSheetTitle($sheetId);
            $response = $sheetsService->spreadsheets_values->get($sheetId, "{$sheetName}!A:Z");
            $values = $response->getValues() ?? [];

            if (empty($values)) {
                return [];
            }

            $headers = array_map('trim', $values[0]);
            $rows = [];
            for ($i = 1; $i < count($values); $i++) {
                $rows[] = $values[$i];
            }

            return [
                'headers' => $headers,
                'rows' => $rows
            ];
        } catch (\Throwable $e) {
            Log::error("Google Sheets getSheetValues Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Append a dynamic row to Sheet1 matching column headers.
     */
    public function appendDynamicRow(string $sheetId, array $rowData): bool
    {
        if (!$this->isConfigured) {
            Log::info("Mock Sheets: Simulating dynamic append row for {$sheetId}: " . json_encode($rowData));
            return true;
        }

        try {
            $sheetsService = new \Google\Service\Sheets($this->client);
            $sheetName = $this->getFirstSheetTitle($sheetId);
            
            // 1. Fetch current header row to map columns
            $response = $sheetsService->spreadsheets_values->get($sheetId, "{$sheetName}!A1:Z1");
            $values = $response->getValues() ?? [];
            
            if (empty($values) || empty($values[0])) {
                // No headers exist, let's treat the keys of $rowData as headers
                $headers = array_keys($rowData);
                
                // Write headers
                $body = new \Google\Service\Sheets\ValueRange([
                    'values' => [$headers]
                ]);
                $sheetsService->spreadsheets_values->update($sheetId, "{$sheetName}!A1", $body, [
                    'valueInputOption' => 'RAW'
                ]);
            } else {
                $headers = array_map('trim', $values[0]);
            }

            // 2. Map rowData keys to matching header indices
            $rowValues = [];
            foreach ($headers as $header) {
                // Try case-insensitive matching
                $foundValue = '';
                foreach ($rowData as $key => $val) {
                    if (strtolower(trim($key)) === strtolower($header)) {
                        $foundValue = $val;
                        break;
                    }
                }
                $rowValues[] = $foundValue;
            }

            // 3. Append to Sheet1
            $body = new \Google\Service\Sheets\ValueRange([
                'values' => [$rowValues]
            ]);

            $sheetsService->spreadsheets_values->append($sheetId, "{$sheetName}!A1", $body, [
                'valueInputOption' => 'RAW',
                'insertDataOption' => 'INSERT_ROWS'
            ]);

            Log::info("Google Sheets: Appended dynamic row to sheet {$sheetId}");
            return true;

        } catch (\Throwable $e) {
            Log::error("Google Sheets appendDynamicRow Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve the raw sheet grid including row numbers and column letters for AI context.
     */
    public function getRawSheetGrid(string $sheetId): array
    {
        if (!$this->isConfigured) {
            // Mock grid representing the user's custom sheet for local development
            return [
                'columns' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'],
                'grid_text' => "Row 1: [\"Captured Name\", \"Phone Number\", \"Email Address\", \"Lead Score\", \"Lifecycle Stage\", \"Customer Intent\", \"Customer Mood\", \"Last Activity\", \"Key Details / Summary\"]\n" .
                               "Row 2: [\"Date\", \"Twin Valley R\", \"Twin Valley L\", \"Nubra\", \"Araku\", \"Silent Valley\", \"Sangla\", \"Spiti\", \"Solang\"]\n" .
                               "Row 3: []\n" .
                               "Row 4: [\"\", \"1 May\"]\n" .
                               "Row 5: [\"\", \"Friday\"]\n" .
                               "Row 6: [\"Name\", \"\", \"Vinayak Behera\", \"Vinayak Behera\", \"Hasmukh Sharma\", \"Rashmita Manoj\"]\n" .
                               "Row 7: [\"Phone number\", \"\", \"+91 80100 55462\", \"+91 80100 55462\"]\n" .
                               "Row 8: [\"No. of people\", \"\", \"4A 1k\", \"4A 1k\"]\n" .
                               "Row 9: [\"Age of kids\"]\n" .
                               "Row 10: [\"Complimentary\"]\n" .
                               "Row 11: [\"Add on\"]\n" .
                               "Row 12: [\"Price\", \"\", \"25,500\", \"25,500\"]\n" .
                               "Row 13: [\"Advance paid\", \"\", \"14,000\", \"14,000\"]\n" .
                               "Row 14: [\"Comments\"]"
            ];
        }

        try {
            $sheetsService = new \Google\Service\Sheets($this->client);
            $sheetName = $this->getFirstSheetTitle($sheetId);
            $response = $sheetsService->spreadsheets_values->get($sheetId, "{$sheetName}!A1:Z500");
            $values = $response->getValues() ?? [];

            if (empty($values)) {
                return [];
            }

            $gridText = "";
            foreach ($values as $index => $row) {
                $rowNumber = $index + 1;
                $gridText .= "Row {$rowNumber}: " . json_encode($row) . "\n";
            }

            // Map columns to letters (up to Z)
            $columns = [];
            for ($i = 0; $i < 26; $i++) {
                $columns[] = chr(65 + $i); // A to Z
            }

            return [
                'columns' => $columns,
                'grid_text' => trim($gridText)
            ];
        } catch (\Throwable $e) {
            Log::error("Google Sheets getRawSheetGrid Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update arbitrary cells/ranges in the spreadsheet.
     * $rangesData is an associative array of range => values array, e.g. ["Sheet1!C6:C14" => [["Name"], ["Phone"], ...]]
     */
    public function updateSheetRanges(string $sheetId, array $rangesData): bool
    {
        if (!$this->isConfigured) {
            Log::info("Mock Sheets: Simulating dynamic update ranges for {$sheetId}: " . json_encode($rangesData));
            return true;
        }

        try {
            $sheetsService = new \Google\Service\Sheets($this->client);
            
            $data = [];
            foreach ($rangesData as $range => $values) {
                $data[] = new \Google\Service\Sheets\ValueRange([
                    'range' => $range,
                    'values' => $values
                ]);
            }

            $requestBody = new \Google\Service\Sheets\BatchUpdateValuesRequest([
                'valueInputOption' => 'RAW',
                'data' => $data
            ]);

            $sheetsService->spreadsheets_values->batchUpdate($sheetId, $requestBody);
            Log::info("Google Sheets: Batch updated ranges in {$sheetId}");
            return true;
        } catch (\Throwable $e) {
            Log::error("Google Sheets updateSheetRanges Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse column B to build an index mapping of all pre-populated dates
     * and their exact row numbers to completely eliminate math hallucinations in the AI.
     */
    public function getLayoutIndexMap(string $sheetId): string
    {
        if (!$this->isConfigured) {
            return "Date \"1 May\" begins at Row 4 (Name Row = 6, Phone Row = 7, ..., Comments Row = 14)\n" .
                   "Date \"2 May\" begins at Row 16 (Name Row = 18, Phone Row = 19, ..., Comments Row = 26)\n" .
                   "Date \"3 May\" begins at Row 28 (Name Row = 30, Phone Row = 31, ..., Comments Row = 38)";
        }

        try {
            $sheetsService = new \Google\Service\Sheets($this->client);
            $sheetName = $this->getFirstSheetTitle($sheetId);
            $response = $sheetsService->spreadsheets_values->get($sheetId, "{$sheetName}!B1:B500");
            $values = $response->getValues() ?? [];

            $indexLines = [];
            foreach ($values as $index => $row) {
                if (empty($row) || empty($row[0])) continue;
                $cellVal = trim($row[0]);
                
                // Match dates like "1 May", "2 May", "15 June", "24 December"
                if (preg_match('/^\d+\s+[A-Za-z]+$/', $cellVal)) {
                    $rowNum = $index + 1;
                    $nameRow = $rowNum + 2;
                    $phoneRow = $rowNum + 3;
                    $peopleRow = $rowNum + 4;
                    $ageRow = $rowNum + 5;
                    $compRow = $rowNum + 6;
                    $addonRow = $rowNum + 7;
                    $priceRow = $rowNum + 8;
                    $advanceRow = $rowNum + 9;
                    $commentsRow = $rowNum + 10;
                    
                    $indexLines[] = "Date \"{$cellVal}\" begins at Row {$rowNum} (" .
                                    "Name Row = {$nameRow}, " .
                                    "Phone Row = {$phoneRow}, " .
                                    "No Of People Row = {$peopleRow}, " .
                                    "Age Of Kids Row = {$ageRow}, " .
                                    "Complimentary Row = {$compRow}, " .
                                    "Add On Row = {$addonRow}, " .
                                    "Price Row = {$priceRow}, " .
                                    "Advance Paid Row = {$advanceRow}, " .
                                    "Comments Row = {$commentsRow})";
                }
            }

            return empty($indexLines) ? "No pre-populated date rows found in Column B." : implode("\n", $indexLines);

        } catch (\Throwable $e) {
            Log::warning("Google Sheets getLayoutIndexMap Exception: " . $e->getMessage());
            return "Failed to auto-index layout coordinates: " . $e->getMessage();
        }
    }

    /**
     * Resolves the name of the first tab/sheet in the spreadsheet dynamically.
     * Defaults to 'Sheet1' if it fails.
     */
    private function getFirstSheetTitle(string $sheetId): string
    {
        try {
            $sheetsService = new \Google\Service\Sheets($this->client);
            $spreadsheet = $sheetsService->spreadsheets->get($sheetId);
            $sheets = $spreadsheet->getSheets();
            if (!empty($sheets) && isset($sheets[0])) {
                $title = $sheets[0]->getProperties()->getTitle();
                Log::info("Google Sheets: Resolved first sheet tab title dynamically as '{$title}' for spreadsheet {$sheetId}.");
                return $title;
            }
        } catch (\Throwable $e) {
            Log::warning("Google Sheets: Failed to resolve first sheet title for {$sheetId}, falling back to 'Sheet1': " . $e->getMessage());
        }
        return 'Sheet1';
    }
}
