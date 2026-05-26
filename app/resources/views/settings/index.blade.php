@extends('layouts.app')
@section('title', 'AI Settings')
@section('subtitle', 'Configure bot behaviour')

@section('content')
<form action="{{ route('settings.update') }}" method="POST" class="space-y-5 max-w-3xl">
    @csrf

    {{-- System Prompt --}}
    <div class="card p-6">
        <h2 class="font-semibold text-white mb-1"><svg class="w-5 h-5 mr-2 inline text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>System Prompt</h2>
        <p class="text-xs text-slate-500 mb-4">This defines how the AI behaves. Be specific about tone and what to avoid.</p>
        <textarea name="system_prompt" rows="6" required
            class="w-full rounded-xl px-4 py-3 text-sm font-mono resize-none"
            style="background:#060c1a;border:1px solid rgba(255,255,255,0.08);color:#e2e8f0;line-height:1.6;">{{ $settings['system_prompt'] ?? '' }}</textarea>
        @error('system_prompt') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Working Hours --}}
    <div class="card p-6">
        <h2 class="font-semibold text-white mb-1"><svg class="w-5 h-5 mr-2 inline text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Working Hours</h2>
        <p class="text-xs text-slate-500 mb-4">Bot will only reply within these hours. Outside hours → polite fallback message.</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-slate-400 mb-2">Start Time</label>
                <input type="time" name="working_hours_start" required
                    value="{{ $settings['working_hours_start'] ?? '00:00' }}"
                    class="w-full rounded-xl px-4 py-2.5 text-sm" />
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-2">End Time</label>
                <input type="time" name="working_hours_end" required
                    value="{{ $settings['working_hours_end'] ?? '23:59' }}"
                    class="w-full rounded-xl px-4 py-2.5 text-sm" />
            </div>
        </div>
    </div>

    {{-- Delay & Rate Limits --}}
    <div class="card p-6">
        <h2 class="font-semibold text-white mb-1"><svg class="w-5 h-5 mr-2 inline text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Delay & Rate Limits</h2>
        <p class="text-xs text-slate-500 mb-4">Human-like delays prevent spam flags. Cooldown prevents bombarding a user.</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-slate-400 mb-2">Min Delay (seconds)</label>
                <input type="number" name="delay_min" min="1" max="30" required
                    value="{{ $settings['delay_min'] ?? 3 }}"
                    class="w-full rounded-xl px-4 py-2.5 text-sm" />
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-2">Max Delay (seconds)</label>
                <input type="number" name="delay_max" min="2" max="60" required
                    value="{{ $settings['delay_max'] ?? 15 }}"
                    class="w-full rounded-xl px-4 py-2.5 text-sm" />
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-2">Per-User Cooldown (seconds)</label>
                <input type="number" name="per_user_cooldown" min="5" max="300" required
                    value="{{ $settings['per_user_cooldown'] ?? 5 }}"
                    class="w-full rounded-xl px-4 py-2.5 text-sm" />
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-2">Max Replies / Minute (global)</label>
                <input type="number" name="max_replies_per_minute" min="1" max="60" required
                    value="{{ $settings['max_replies_per_minute'] ?? 20 }}"
                    class="w-full rounded-xl px-4 py-2.5 text-sm" />
            </div>
        </div>
    </div>

    {{-- Human Trigger Keywords --}}
    <div class="card p-6">
        <h2 class="font-semibold text-white mb-1"><svg class="w-5 h-5 mr-2 inline text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>Human Takeover Keywords</h2>
        <p class="text-xs text-slate-500 mb-4">Comma-separated. If a user message contains any of these, AI stops and flags for human.</p>
        <input type="text" name="human_trigger_keywords" required
            value="{{ $settings['human_trigger_keywords'] ?? 'call,urgent,complaint,manager,refund,legal' }}"
            class="w-full rounded-xl px-4 py-2.5 text-sm"
            placeholder="call,urgent,complaint,manager,refund" />
    </div>

    {{-- Admin Notifications --}}
    <div class="card p-6">
        <h2 class="font-semibold text-white mb-1"><svg class="w-5 h-5 mr-2 inline text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>Admin Notifications (Hot Leads)</h2>
        <p class="text-xs text-slate-500 mb-4">Comma-separated phone numbers (with country code, no + or spaces). These numbers will receive WhatsApp alerts when a user triggers a human takeover.</p>
        <input type="text" name="admin_phones"
            value="{{ $settings['admin_phones'] ?? '' }}"
            class="w-full rounded-xl px-4 py-2.5 text-sm"
            placeholder="e.g. 917997001700,919573142847" />
    </div>

    {{-- Outside Hours Message --}}
    <div class="card p-6">
        <h2 class="font-semibold text-white mb-1"><svg class="w-5 h-5 mr-2 inline text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>Outside Hours Message</h2>
        <p class="text-xs text-slate-500 mb-4">Sent automatically when a user messages outside working hours.</p>
        <textarea name="outside_hours_message" rows="3" required
            class="w-full rounded-xl px-4 py-3 text-sm resize-none">{{ $settings['outside_hours_message'] ?? '' }}</textarea>
    </div>

    <button type="submit" class="btn-primary px-8 py-3 rounded-xl text-sm font-semibold text-white">
        <svg class="w-5 h-5 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg> Save Settings
    </button>
</form>
@endsection
