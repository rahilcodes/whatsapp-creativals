<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IntegrationController extends Controller
{
    public function __construct(
        private GoogleSheetsService $sheetsService
    ) {}

    /**
     * Display the integrations and customer payments settings dashboard.
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;
        $isSheetsConfigured = $this->sheetsService->isConfigured();
        return view('integrations.index', compact('tenant', 'isSheetsConfigured'));
    }

    /**
     * Update predefined payment configurations (UPI and bank details).
     */
    public function updatePayments(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'upi_id'              => 'nullable|string|max:100',
            'upi_number'          => 'nullable|string|max:20',
            'bank_name'           => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc'           => 'nullable|string|max:20',
        ]);

        $tenant->update($validated);

        ActivityLog::record('payments_updated', 'Predefined payment settings updated');

        return back()->with('success', 'Predefined payment details saved successfully!');
    }

    /**
     * Upload and compress QR scanner image using PHP GD.
     */
    public function uploadQrCode(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $request->validate([
            'qr_code' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // max 5MB
        ]);

        try {
            if (!$request->hasFile('qr_code')) {
                return back()->withErrors(['qr_code' => 'No file uploaded.']);
            }

            $file = $request->file('qr_code');
            $imgData = file_get_contents($file->getRealPath());
            if (!$imgData) {
                return back()->withErrors(['qr_code' => 'Could not read uploaded image data.']);
            }

            // GD compression (Phase 2 constraint match)
            $srcImg = @imagecreatefromstring($imgData);
            if (!$srcImg) {
                return back()->withErrors(['qr_code' => 'Invalid image format or GD library error.']);
            }

            $width = imagesx($srcImg);
            $height = imagesy($srcImg);
            $maxWidth = 800;

            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) ($height * ($maxWidth / $width));
                $dstImg = imagescale($srcImg, $newWidth, $newHeight);
                
                ob_start();
                imagejpeg($dstImg, null, 75); // 75% quality compression
                $compressedData = ob_get_clean();
                
                imagedestroy($srcImg);
                imagedestroy($dstImg);
            } else {
                ob_start();
                imagejpeg($srcImg, null, 75);
                $compressedData = ob_get_clean();
                imagedestroy($srcImg);
            }

            // 1. Delete old QR file if exists to prevent space leakage
            if ($tenant->qr_code_path) {
                $oldPath = public_path($tenant->qr_code_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // 2. Prepare directory
            $filename = 'qr_' . time() . '_' . uniqid() . '.jpg';
            $directory = storage_path("app/public/qr_codes/{$tenant->id}");
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // 3. Write file
            $path = "{$directory}/{$filename}";
            file_put_contents($path, $compressedData);

            // 4. Update model
            $publicPath = "storage/qr_codes/{$tenant->id}/{$filename}";
            $tenant->update([
                'qr_code_path' => $publicPath
            ]);

            ActivityLog::record('qr_updated', 'Predefined QR Code scanner updated and compressed');

            return back()->with('success', 'QR Code scanner uploaded and optimized successfully!');

        } catch (\Throwable $e) {
            Log::error("QR scanner upload exception: " . $e->getMessage());
            return back()->withErrors(['qr_code' => 'Error optimizing QR code: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete active QR Code scanner.
     */
    public function deleteQrCode()
    {
        $tenant = Auth::user()->tenant;

        if ($tenant->qr_code_path) {
            $oldPath = public_path($tenant->qr_code_path);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $tenant->update(['qr_code_path' => null]);
            ActivityLog::record('qr_deleted', 'Predefined QR Code scanner deleted');
        }

        return back()->with('success', 'QR Code scanner deleted successfully.');
    }

    /**
     * Connect or provision Google Sheets dynamically.
     */
    public function connectSheets(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $request->validate([
            'google_sheet_email' => 'required|email|max:150',
        ]);

        $shareEmail = $request->input('google_sheet_email');

        // Provision sheet dynamically using existing centralized service
        $sheetId = $this->sheetsService->createSheetForTenant($tenant, $shareEmail);

        if ($sheetId) {
            return back()->with('success', 'Google Sheet provisioned, synced and shared successfully!');
        }

        return back()->withErrors(['google_sheet_email' => 'Failed to provision Google Sheet. Check system logs for credentials.']);
    }

    /**
     * Connect an existing Google Sheet manually using its ID or URL.
     */
    public function connectExistingSheet(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $request->validate([
            'google_sheet_url_or_id' => 'required|string|max:255',
        ]);

        $input = $request->input('google_sheet_url_or_id');
        $sheetId = $this->extractSheetId($input);

        if (empty($sheetId)) {
            return back()->withErrors(['google_sheet_url_or_id' => 'Invalid Google Sheet ID or URL format.']);
        }

        // Initialize sheet headers if it is configureable
        $initialized = $this->sheetsService->initializeSheetHeaders($sheetId);

        if ($initialized) {
            $tenant->update([
                'google_sheet_id' => $sheetId,
                'google_sheet_email' => 'Manual Integration',
            ]);

            ActivityLog::record('sheets_connected_manual', "Manually connected Google Sheet ID {$sheetId}");

            return back()->with('success', 'Google Sheet connected and initialized successfully!');
        }

        return back()->withErrors(['google_sheet_url_or_id' => 'Could not verify or write headers to this Sheet. Make sure you shared it as Editor with the Service Account email first!']);
    }

    /**
     * Disconnect active Google Sheets integration.
     */
    public function disconnectSheets()
    {
        $tenant = Auth::user()->tenant;

        if ($tenant->google_sheet_id) {
            $oldId = $tenant->google_sheet_id;
            $tenant->update([
                'google_sheet_id' => null,
                'google_sheet_email' => null,
            ]);
            ActivityLog::record('sheets_disconnected', "Disconnected Google Sheet ID {$oldId}");
        }

        return back()->with('success', 'Google Sheet disconnected successfully.');
    }

    /**
     * Update Google Sheets integration settings (sync mode and custom instructions).
     */
    public function updateSheetsSettings(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'google_sheet_sync_mode'    => 'required|string|in:leads_only,smart_read_write',
            'google_sheet_instructions' => 'nullable|string|max:5000',
        ]);

        $tenant->update($validated);

        // Clear the sheet cache to pull fresh instructions and values immediately
        \Illuminate\Support\Facades\Cache::forget("tenant_sheet_data_{$tenant->id}");

        ActivityLog::record('sheets_settings_updated', 'Google Sheets custom AI instructions and sync mode updated');

        return back()->with('success', 'Google Sheets AI instructions saved successfully!');
    }

    /**
     * Helper to extract sheet ID from a full Google Sheets URL.
     */
    private function extractSheetId(string $input): string
    {
        if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $input, $matches)) {
            return $matches[1];
        }
        return trim($input);
    }
}
