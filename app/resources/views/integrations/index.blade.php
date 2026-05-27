@extends('layouts.app')
@section('title', 'Integrations')
@section('subtitle', 'Connect third-party apps and configure customer payment credentials')

@section('content')
<div class="space-y-6 fade-in max-w-5xl">

    {{-- Google Sheets Integration Segment --}}
    <div class="card p-6">
        <div class="flex items-start justify-between border-b border-slate-800/80 pb-5 mb-5">
            <div class="flex gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:rgba(16,185,129,0.07); border:1.5px solid rgba(16,185,129,0.15);">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75c.621 0 1.125.504 1.125 1.125v12.75c0 .621-.504 1.125-1.125 1.125H5.625a1.125 1.125 0 0 1-1.125-1.125V5.625c0-.621.504-1.125 1.125-1.125Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-white text-base">Google Sheets Integration</h2>
                    <p class="text-xs text-slate-500 mt-1 max-w-xl">
                        Automatically sync all your captured sales leads, scores, stages, and intelligence summaries in real-time. Shared directly with your business email.
                    </p>
                </div>
            </div>
            <div>
                @if($tenant->google_sheet_id)
                    <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        Connected
                    </span>
                @else
                    <span class="px-2.5 py-1 bg-slate-800 text-slate-500 border border-slate-700/50 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        Not Connected
                    </span>
                @endif
            </div>
        </div>

        @if($tenant->google_sheet_id)
            <div class="grid grid-cols-3 gap-6">
                {{-- Left column: Description --}}
                <div class="col-span-1 space-y-4">
                    <div class="p-4 rounded-xl" style="background:rgba(255,255,255,0.015); border:1px solid rgba(255,255,255,0.04);">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Sync Columns</h4>
                        <ul class="text-[11px] text-slate-500 space-y-1.5 font-mono">
                            <li>• Captured Name</li>
                            <li>• WhatsApp Number</li>
                            <li>• Email Address</li>
                            <li>• Lead Score (0 - 100)</li>
                            <li>• Lifecycle Stage</li>
                            <li>• Customer Intent</li>
                            <li>• Intelligence Summary</li>
                        </ul>
                    </div>
                </div>

                {{-- Right column: Connection details --}}
                <div class="col-span-2 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="p-4 rounded-xl border border-slate-800" style="background:rgba(16,185,129,0.03);">
                            <span class="text-[10px] font-bold text-slate-500 uppercase block tracking-wider">CONNECTED GOOGLE SHEET</span>
                            <span class="text-sm font-semibold text-slate-300 block mt-1">
                                @if($tenant->google_sheet_email === 'Manual Integration')
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                        </svg>
                                        Manually Connected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                        </svg>
                                        {{ $tenant->google_sheet_email }}
                                    </span>
                                @endif
                            </span>
                            <span class="text-[10px] text-slate-500 block mt-2 font-mono truncate">Sheet ID: {{ $tenant->google_sheet_id }}</span>

                            @if(str_starts_with($tenant->google_sheet_id, 'mock_') || !$isSheetsConfigured)
                                <div class="mt-3 p-3 rounded-lg bg-amber-500/5 border border-amber-500/20 flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                    <div class="text-[10px] text-amber-400 font-sans leading-relaxed">
                                        <strong>Local Demo Mode Active:</strong> Real Google credentials are not configured on localhost. A mock ID has been generated. Real Google Sheets will be created and shared instantly on production!
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            @if(str_starts_with($tenant->google_sheet_id, 'mock_'))
                                <button disabled
                                        class="px-5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 text-slate-600 border border-slate-700/50 inline-flex items-center gap-2 cursor-not-allowed"
                                        title="Direct opening is disabled for mock Sheets. Deploy to production or configure credentials locally to open real Sheets.">
                                    Open Google Sheet (Demo)
                                </button>
                            @else
                                <a href="https://docs.google.com/spreadsheets/d/{{ $tenant->google_sheet_id }}" 
                                   target="_blank"
                                   class="btn-primary px-5 py-2.5 rounded-xl text-xs font-semibold text-white inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                    </svg>
                                    Open Google Sheet
                                </a>
                            @endif
                            <form method="POST" action="{{ route('integrations.sheets.disconnect') }}" onsubmit="return confirm('Are you sure you want to disconnect this Google Sheet? Leads syncing will pause.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2.5 rounded-xl text-xs font-semibold text-red-400 border border-red-950/40 hover:bg-red-500/10 hover:text-red-300 transition-all">
                                    Disconnect Sheet
                                </button>
                            </form>
                        </div>

                        {{-- New settings configuration form --}}
                        <div class="mt-4 pt-4 border-t border-slate-800/80 space-y-4">
                            <form method="POST" action="{{ route('integrations.sheets.settings') }}" class="space-y-4">
                                @csrf
                                
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1.5 uppercase font-bold tracking-wider">Integration Sync Mode</label>
                                    <select name="google_sheet_sync_mode" onchange="toggleSheetInstructions(this.value)"
                                            class="w-full text-xs px-3.5 py-2.5 rounded-xl text-white bg-slate-950 border border-slate-800 focus:outline-none cursor-pointer">
                                        <option value="leads_only" {{ $tenant->google_sheet_sync_mode === 'leads_only' ? 'selected' : '' }}>
                                            Standard Leads Tracking (Capture Name, WhatsApp, Email, AI Summary)
                                        </option>
                                        <option value="smart_read_write" {{ $tenant->google_sheet_sync_mode === 'smart_read_write' ? 'selected' : '' }}>
                                            Smart AI Reader &amp; Dynamic Writer (Use Custom Instructions)
                                        </option>
                                    </select>
                                </div>

                                <div id="sheet-instructions-wrap" class="{{ $tenant->google_sheet_sync_mode === 'smart_read_write' ? '' : 'hidden' }} space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">Custom AI Instructions</label>
                                        <select onchange="loadPresetInstructions(this.value)" 
                                                class="text-[9px] bg-slate-900 border border-slate-800 text-slate-400 px-2 py-1 rounded focus:outline-none cursor-pointer">
                                            <option value="">-- Load a Preset Template --</option>
                                            <option value="school">School Directory / Student Marks lookup</option>
                                            <option value="ecommerce">Product Stock Catalog &amp; Price lookup</option>
                                            <option value="rsvp">Event Registration / RSVP tracker</option>
                                        </select>
                                    </div>
                                    <textarea name="google_sheet_instructions" id="sheet-instructions-text" rows="5"
                                              class="w-full text-xs px-3.5 py-2.5 rounded-xl text-white bg-slate-950 border border-slate-800 focus:outline-none font-sans leading-relaxed"
                                              placeholder="Explain how the AI should read and write data. Example: 'Sheet1 contains columns: Student Name, Roll Number, Marks. When a user provides their Roll Number, lookup their Marks and Attendance in the sheet...'">{{ $tenant->google_sheet_instructions }}</textarea>
                                    <div class="text-[10px] text-slate-500 font-mono leading-relaxed mt-3 p-3 bg-slate-900/60 rounded-xl border border-slate-800/40 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 1 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.852l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                        </svg>
                                        <span><strong>How to write back:</strong> Tell the AI what column values to capture and write. The system matches the keys in the response to the spreadsheet headers case-insensitively.</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end">
                                    <button type="submit" class="btn-primary px-5 py-2.5 rounded-xl text-xs font-bold text-white">
                                        Save Google Sheets AI Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Option A: Auto-Provision --}}
                <div class="p-5 rounded-2xl border border-slate-800/80 bg-slate-900/10 flex flex-col justify-between space-y-4">
                    <div>
                        <span class="px-2 py-0.5 bg-brand-500/10 text-brand-400 border border-brand-500/20 rounded-full text-[9px] font-bold uppercase tracking-wider">
                            Option A
                        </span>
                        <h3 class="font-bold text-white text-sm mt-2">Auto-Provision Leads Sheet</h3>
                        <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                            The system automatically creates a structured sheet in our Google Cloud workspace and shares it directly with your Gmail.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('integrations.sheets') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-[9px] text-slate-500 block mb-1.5 uppercase font-bold tracking-wider">Your Google Email / Gmail</label>
                            <input type="email" name="google_sheet_email" required
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl text-white border border-slate-800 focus:outline-none"
                                   placeholder="yourbusiness@gmail.com" />
                        </div>
                        <button type="submit" class="w-full btn-primary py-2.5 rounded-xl text-xs font-bold text-white">
                            Provision &amp; Sync Sheet
                        </button>
                    </form>
                </div>

                {{-- Option B: Connect Existing --}}
                <div class="p-5 rounded-2xl border border-slate-800/80 bg-slate-900/10 flex flex-col justify-between space-y-4">
                    <div>
                        <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[9px] font-bold uppercase tracking-wider font-mono">
                            Option B (Highly Recommended)
                        </span>
                        <h3 class="font-bold text-white text-sm mt-2">Connect Existing Google Sheet</h3>
                        <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                            Bypasses Google Cloud storage quota limits by using your personal Google Drive storage space.
                        </p>

                        {{-- Copy Service Account Email --}}
                        <div class="mt-3 p-3 rounded-xl border border-slate-800/80 bg-slate-950/40">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">1. Share your sheet with:</span>
                            <div class="flex items-center justify-between gap-2 mt-1.5 bg-slate-950 p-2 rounded-lg border border-slate-850 font-mono text-[10px]">
                                <span class="text-slate-300 select-all truncate">{{ $serviceAccountEmail ?? 'ichatup-sheet-manager@whatsapp-bot-496805.iam.gserviceaccount.com' }}</span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $serviceAccountEmail ?? 'ichatup-sheet-manager@whatsapp-bot-496805.iam.gserviceaccount.com' }}'); alert('Service account email copied to clipboard!');" 
                                        class="text-[9px] text-emerald-450 hover:text-emerald-300 font-sans font-bold flex-shrink-0 hover:underline">
                                    Copy
                                </button>
                            </div>
                            <span class="text-[9px] text-slate-500 block mt-1.5 leading-relaxed">
                                Give <strong>Editor</strong> access to this email address in your Google Sheet first!
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('integrations.sheets.connect') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-[9px] text-slate-500 block mb-1.5 uppercase font-bold tracking-wider font-mono">2. Google Sheet ID or Full URL</label>
                            <input type="text" name="google_sheet_url_or_id" required
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl text-white border border-slate-800 focus:outline-none"
                                   placeholder="https://docs.google.com/spreadsheets/d/.../edit" />
                        </div>
                        <button type="submit" class="w-full btn-primary py-2.5 rounded-xl text-xs font-bold text-white">
                            Link &amp; Auto-Initialize
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    {{-- Collect Payments Setting Segment --}}
    <div class="grid grid-cols-3 gap-6">
        
        {{-- Predefined payment text details form --}}
        <div class="col-span-2 card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 border-b border-slate-800/80 pb-4 mb-5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                         style="background:rgba(59,130,246,0.07); border:1px solid rgba(59,130,246,0.15)">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6.125-9h16.25c.621 0 1.125.504 1.125 1.125v10.5c0 .621-.504 1.125-1.125 1.125H3.375a1.125 1.125 0 0 1-1.125-1.125V5.625c0-.621.504-1.125 1.125-1.125Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-sm">Predefined Payment Info</h3>
                        <p class="text-[10px] text-slate-500">Add credentials for instant payment queries</p>
                    </div>
                </div>

                <form id="payment-text-form" method="POST" action="{{ route('integrations.payments') }}" class="space-y-4">
                    @csrf
                    
                    {{-- UPI ID --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1.5 uppercase font-bold tracking-wider">UPI ID / VPA</label>
                            <input type="text" name="upi_id" value="{{ old('upi_id', $tenant->upi_id) }}"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl text-white border border-slate-800 focus:outline-none font-mono"
                                   placeholder="merchant@upi" />
                            <p class="text-[9px] text-slate-600 mt-1 font-mono">Example: user@okicici</p>
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1.5 uppercase font-bold tracking-wider">UPI Mobile Number</label>
                            <input type="text" name="upi_number" value="{{ old('upi_number', $tenant->upi_number) }}"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-xl text-white border border-slate-800 focus:outline-none font-mono"
                                   placeholder="9876543210" />
                            <p class="text-[9px] text-slate-600 mt-1 font-mono">Mobile linked to GPay/PhonePe</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-800/60 pt-4 mt-4 space-y-4">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Direct Bank Transfer details</h4>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-1">
                                <label class="text-[10px] text-slate-500 block mb-1.5 uppercase font-semibold">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $tenant->bank_name) }}"
                                       class="w-full text-xs px-3.5 py-2.5 rounded-xl text-white border border-slate-800 focus:outline-none"
                                       placeholder="HDFC Bank" />
                            </div>
                            <div class="col-span-1">
                                <label class="text-[10px] text-slate-500 block mb-1.5 uppercase font-semibold">Account Number</label>
                                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $tenant->bank_account_number) }}"
                                       class="w-full text-xs px-3.5 py-2.5 rounded-xl text-white border border-slate-800 focus:outline-none font-mono"
                                       placeholder="50100234..." />
                            </div>
                            <div class="col-span-1">
                                <label class="text-[10px] text-slate-500 block mb-1.5 uppercase font-semibold">Bank IFSC Code</label>
                                <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $tenant->bank_ifsc) }}"
                                       class="w-full text-xs px-3.5 py-2.5 rounded-xl text-white border border-slate-800 focus:outline-none font-mono"
                                       placeholder="HDFC0000123" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="pt-5 mt-6 border-t border-slate-800/80 flex items-center justify-end">
                <button type="submit" form="payment-text-form"
                        class="btn-primary px-5 py-2.5 rounded-xl text-xs font-bold text-white">
                    Save Predefined Payment Details
                </button>
            </div>
        </div>

        {{-- QR Scanner upload module --}}
        <div class="col-span-1 card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 border-b border-slate-800/80 pb-4 mb-5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                         style="background:rgba(236,72,153,0.07); border:1px solid rgba(236,72,153,0.15)">
                        <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-sm">UPI Scanner QR</h3>
                        <p class="text-[10px] text-slate-500">Upload your payment scanner image</p>
                    </div>
                </div>

                @if($tenant->qr_code_path)
                    {{-- Displays current QR --}}
                    <div class="space-y-4 flex flex-col items-center">
                        <div class="p-3 bg-white rounded-xl shadow-inner relative group overflow-hidden" style="max-width:180px;">
                            <img src="{{ asset($tenant->qr_code_path) }}" alt="Payment QR Code" 
                                 class="w-full h-auto object-contain rounded-lg transition-transform duration-300 group-hover:scale-105" />
                            <div class="absolute inset-0 bg-slate-950/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="text-[10px] font-bold text-white uppercase tracking-wider">Active Scanner</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('integrations.qr.delete') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-300 flex items-center gap-1 hover:underline transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.78 0L9 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                                Delete Scanner Image
                            </button>
                        </form>
                    </div>
                @else
                    {{-- Upload input card --}}
                    <form id="qr-upload-form" method="POST" action="{{ route('integrations.qr.upload') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="border-2 border-dashed border-slate-800 hover:border-brand-500 rounded-2xl p-6 transition-colors flex flex-col items-center justify-center text-center cursor-pointer"
                             onclick="document.getElementById('qr-file-input').click()">
                            <svg class="w-8 h-8 text-slate-500 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/>
                            </svg>
                            <span class="text-xs text-slate-300 font-medium">Click to select photo</span>
                            <span class="text-[10px] text-slate-500 mt-1">Supports PNG, JPG, WEBP (compressed)</span>
                            <input type="file" id="qr-file-input" name="qr_code" accept="image/*" class="hidden" onchange="previewQr(this)" />
                        </div>
                        {{-- Live Preview zone --}}
                        <div id="qr-preview-wrap" class="hidden flex flex-col items-center space-y-2 pt-2 border-t border-slate-900">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Upload Preview</span>
                            <img id="qr-preview-img" src="" class="max-h-[140px] rounded-lg border border-slate-800" />
                        </div>
                    </form>
                @endif
            </div>

            <div class="pt-5 mt-6 border-t border-slate-800/80">
                @if(!$tenant->qr_code_path)
                    <button type="submit" form="qr-upload-form" id="qr-submit-btn" disabled
                            class="w-full btn-primary px-5 py-2.5 rounded-xl text-xs font-bold text-white disabled:opacity-50">
                        Upload &amp; Optimize QR Code
                    </button>
                @else
                    <div class="text-[10px] text-slate-500 text-center font-mono py-2 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Scanner optimized at 75% GD
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>

<script>
function previewQr(input) {
    const wrap = document.getElementById('qr-preview-wrap');
    const img = document.getElementById('qr-preview-img');
    const btn = document.getElementById('qr-submit-btn');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            wrap.classList.remove('hidden');
            if (btn) btn.disabled = false;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleSheetInstructions(value) {
    const wrap = document.getElementById('sheet-instructions-wrap');
    if (value === 'smart_read_write') {
        wrap.classList.remove('hidden');
    } else {
        wrap.classList.add('hidden');
    }
}

function loadPresetInstructions(preset) {
    const text = document.getElementById('sheet-instructions-text');
    const presets = {
        school: "We are a high school. The connected Google Sheet contains a directory of students with columns: Roll Number, Student Name, Attendance, Math Marks, Science Marks.\n\n" +
                "RULES FOR AI:\n" +
                "1. If a student asks for their marks or attendance, politely ask for their Roll Number first.\n" +
                "2. Once they provide it, lookup the roll number in the connected sheet data. Verify their name, and output their attendance and scores.\n" +
                "3. If they want to submit a leave request or mark themselves present, ask for their Roll Number and Reason, and record it by outputting:\n" +
                "   WRITE_ACTION: {\"type\": \"write_sheet_row\", \"data\": {\"Roll Number\": \"[roll]\", \"Student Name\": \"[name]\", \"Attendance\": \"Absent - Leave Request\", \"Marks\": \"[reason]\"}}",
        
        ecommerce: "We are a premium digital tech store. The spreadsheet contains our live inventory with columns: Product Name, Stock, Price, Buy Link.\n\n" +
                   "RULES FOR AI:\n" +
                   "1. Look up items in the sheet to confirm if they are in stock and state the price.\n" +
                   "2. If an item is in stock and the customer wants to buy, provide the exact 'Buy Link' value from the table.\n" +
                   "3. If they place a pre-order or request stock alerts, gather their name and product preference, and save it by returning:\n" +
                   "   WRITE_ACTION: {\"type\": \"write_sheet_row\", \"data\": {\"Product Name\": \"[product]\", \"Price\": \"Pre-Order Request\", \"Stock\": \"[customer phone]\"}}",
        
        rsvp: "We are organizing a developers tech conference. The spreadsheet tracks event registrations with columns: Participant Name, Email, Ticket Type, Food Preference.\n\n" +
              "RULES FOR AI:\n" +
              "1. When a user asks to register or buy a ticket, progressively ask for their Participant Name, Email, Ticket Type (e.g. Regular, VIP), and Food Preference (Veg, Non-Veg).\n" +
              "2. Once all 4 fields are collected, save their registration by outputting this action block at the end:\n" +
              "   WRITE_ACTION: {\"type\": \"write_sheet_row\", \"data\": {\"Participant Name\": \"[name]\", \"Email\": \"[email]\", \"Ticket Type\": \"[ticket]\", \"Food Preference\": \"[food]\"}}\n" +
              "3. Confirm their seat has been booked successfully."
    };

    if (preset && presets[preset]) {
        if (confirm("Are you sure you want to load this template? It will overwrite your current instructions text.")) {
            text.value = presets[preset];
        }
    }
}
</script>
@endsection
