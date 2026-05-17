<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BotSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = BotSetting::allAsMap();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'system_prompt'          => 'required|string|min:20',
            'working_hours_start'    => 'required|date_format:H:i',
            'working_hours_end'      => 'required|date_format:H:i',
            'delay_min'              => 'required|integer|min:1|max:30',
            'delay_max'              => 'required|integer|min:2|max:60',
            'per_user_cooldown'      => 'required|integer|min:5|max:300',
            'max_replies_per_minute' => 'required|integer|min:1|max:60',
            'human_trigger_keywords' => 'required|string',
            'outside_hours_message'  => 'required|string',
            'admin_phones'           => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            BotSetting::set($key, $value);
        }

        ActivityLog::record('settings_updated', 'Bot settings updated from dashboard');

        return back()->with('success', 'Settings saved successfully!');
    }
}
