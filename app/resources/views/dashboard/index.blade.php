@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Live overview & controls')

@section('content')
{{-- â”€â”€ STAT CARDS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    @php
        $stats = [
            ['label'=>'Total Messages',  'value'=>$totalMsgs,  'icon'=>'<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 16.5A2.5 2.5 0 0118.5 19H5.5L2 22.5V5.5A2.5 2.5 0 014.5 3h14A2.5 2.5 0 0121 5.5v11z"/></svg>', 'color'=>'#3b82f6'],
            ['label'=>'Total Chats',     'value'=>$totalChats, 'icon'=>'<svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>', 'color'=>'#8b5cf6'],
            ['label'=>'Today Messages',  'value'=>$todayMsgs,  'icon'=>'<svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>', 'color'=>'#10b981'],
            ['label'=>'WA Status',       'value'=>ucfirst($waStatus->status), 'icon'=>'<svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>',
             'color'=>$waStatus->isConnected() ? '#10b981' : '#ef4444'],
        ];
    @endphp
    @foreach ($stats as $s)
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
             style="background:{{ $s['color'] }}1a;">{!! $s['icon'] !!}</div>
        <div>
            <div class="text-2xl font-bold text-white">{{ $s['value'] }}</div>
            <div class="text-xs text-slate-500 mt-0.5">{{ $s['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-3 gap-6">
    {{-- â”€â”€ LEFT COL: Status + QR â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="col-span-2 space-y-5">

        {{-- â”€â”€ FAILSAFE BANNER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div id="failsafe-banner" class="hidden mb-5 p-4 rounded-xl flex items-center justify-between" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h3 id="failsafe-title" class="text-red-400 font-bold text-sm">Session Disconnected</h3>
                    <p id="failsafe-msg" class="text-red-300 text-xs mt-0.5">Automated replies are paused to prevent bans.</p>
                </div>
            </div>
            <button onclick="reconnectWA(event)" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-lg transition-colors">
                Reconnect Now
            </button>
        </div>

        {{-- WhatsApp Status & Health Card --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <h2 class="font-semibold text-white">Device Health</h2>
                    <div id="health-badge" class="hidden px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-red-500/20 text-red-400 border border-red-500/30">
                        Ban Risk
                    </div>
                </div>
                <div id="wa-main-status" class="flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold
                    {{ $waStatus->isConnected() ? 'text-emerald-400' : ($waStatus->status === 'connecting' ? 'text-amber-400' : 'text-red-400') }}"
                    style="background:{{ $waStatus->isConnected() ? 'rgba(16,185,129,0.1)' : ($waStatus->status === 'connecting' ? 'rgba(245,158,11,0.1)' : 'rgba(239,68,68,0.1)') }};">
                    <div class="w-2 h-2 rounded-full {{ $waStatus->isConnected() ? 'bg-emerald-400 pulse-dot' : ($waStatus->status === 'connecting' ? 'bg-amber-400 pulse-dot' : 'bg-red-500') }}"></div>
                    <span id="wa-status-text">{{ ucfirst($waStatus->status) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-5">
                <!-- Health Score Ring -->
                <div class="flex flex-col items-center justify-center p-4 rounded-xl" style="background:rgba(255,255,255,0.02);">
                    <div class="relative w-16 h-16 flex items-center justify-center mb-2">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-700" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path id="health-ring" class="text-emerald-500 transition-all duration-1000" stroke-dasharray="100, 100" stroke-width="3" stroke="currentColor" fill="none" stroke-linecap="round" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center flex-col">
                            <span id="health-score-val" class="text-lg font-bold text-white">{{ $waStatus->health_score ?? 100 }}</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Health Score</span>
                </div>

                <!-- Message Delivery -->
                <div class="flex flex-col justify-center p-4 rounded-xl" style="background:rgba(255,255,255,0.02);">
                    <div class="text-slate-400 text-xs mb-1">Delivery Success</div>
                    <div class="flex items-baseline gap-1">
                        <span id="delivery-rate-val" class="text-2xl font-bold text-white">--</span>
                        <span class="text-sm text-slate-500">%</span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-1">Last 100 messages</div>
                </div>

                <!-- Reconnects -->
                <div class="flex flex-col justify-center p-4 rounded-xl" style="background:rgba(255,255,255,0.02);">
                    <div class="text-slate-400 text-xs mb-1">Session Drops</div>
                    <div class="flex items-baseline gap-1">
                        <span id="reconnects-val" class="text-2xl font-bold text-white">{{ $waStatus->reconnect_count ?? 0 }}</span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-1">Total reconnects</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="p-3 rounded-lg" style="background:rgba(255,255,255,0.03);">
                    <div class="text-slate-500 text-xs mb-1">Node.js Engine</div>
                    <div class="{{ $nodeRunning ? 'text-emerald-400' : 'text-red-400' }} font-medium text-xs">
                        {{ $nodeRunning ? 'â— Running' : 'â— Offline' }}
                    </div>
                </div>
                <div class="p-3 rounded-lg" style="background:rgba(255,255,255,0.03);">
                    <div class="text-slate-500 text-xs mb-1">Last Connected</div>
                    <div id="last-seen-val" class="text-white font-medium text-xs truncate">
                        {{ $waStatus->last_connected_at ? $waStatus->last_connected_at->diffForHumans() : 'Never' }}
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button onclick="pauseBot()" id="pause-btn"
                    class="flex items-center justify-center flex-1 py-2 rounded-lg text-sm font-medium border border-slate-700 text-slate-300 hover:border-red-500 hover:text-red-400 transition-all">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Pause Bot
                </button>
                <button onclick="resumeBot()"
                    class="flex items-center justify-center flex-1 py-2 rounded-lg text-sm font-medium border border-slate-700 text-slate-300 hover:border-emerald-500 hover:text-emerald-400 transition-all">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Resume Bot
                </button>
            </div>
        </div>

        {{-- â”€â”€ QR Code Card (v2) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div class="card p-6" id="qr-card" style="{{ $waStatus->isConnected() ? 'display:none' : '' }}">
            <div class="flex items-center justify-between mb-1">
                <h2 class="font-semibold text-white">Scan QR Code</h2>
                <button id="clear-session-btn" onclick="clearSession()"
                    class="text-[10px] font-semibold text-slate-500 hover:text-red-400 border border-slate-700 hover:border-red-500/40 px-2 py-1 rounded-lg transition-all"
                    title="Clear saved session and force a fresh QR">
                    â†º Clear &amp; Re-scan
                </button>
            </div>
            <p class="text-xs text-slate-500 mb-4">Open WhatsApp â†’ Linked Devices â†’ Link a Device â†’ Scan</p>

            {{-- â”€â”€ Inner QR zone â”€â”€ --}}
            <div class="rounded-xl overflow-hidden" style="background:#fff;">
                <div id="qr-container" class="flex items-center justify-center" style="min-height:320px;">

                    {{-- State 1: QR image (shown when bot gives us a QR) --}}
                    <div id="qr-image-wrap" class="relative" style="display:none;">
                        <img id="qr-img" src="{{ $waStatus->qr_code ?? '' }}" alt="QR Code" style="width:320px;height:320px;display:block;">
                        {{-- Scan-line animation --}}
                        <div id="qr-scanline" style="position:absolute;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#10b981,transparent);animation:qrScan 2.5s ease-in-out infinite;"></div>
                        {{-- Countdown overlay --}}
                        <div id="qr-countdown-wrap" style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,0.55);border-radius:20px;padding:3px 10px;">
                            <span id="qr-countdown" style="font-size:11px;font-weight:700;color:#10b981;">20s</span>
                        </div>
                    </div>

                    {{-- State 2: Generate QR prompt (bot idle) --}}
                    <div id="qr-prompt" class="flex flex-col items-center gap-4 py-8 px-6">
                        <div style="width:56px;height:56px;border-radius:50%;background:rgba(16,185,129,0.08);border:1.5px solid rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;">
                            <svg style="width:28px;height:28px;color:#10b981;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                        </div>
                        <button id="start-engine-btn" onclick="startEngine()"
                            class="btn-primary px-5 py-2.5 rounded-xl text-sm font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9"/></svg>
                            Generate QR Code
                        </button>
                        <p class="text-slate-500 text-xs text-center">Click to start the WhatsApp engine<br>and generate your login QR</p>
                    </div>

                    {{-- State 3: Connecting (QR scanned, handshake in progress) --}}
                    <div id="qr-connecting" style="display:none;" class="flex flex-col items-center gap-4 py-8">
                        <div style="width:64px;height:64px;border-radius:50%;background:rgba(16,185,129,0.1);display:flex;align-items:center;justify-content:center;animation:pulse 1.5s ease-in-out infinite;">
                            <svg style="width:32px;height:32px;" viewBox="0 0 175.216 175.552" fill="none">
                                <path d="M87.184 0C39.072 0 0 39.072 0 87.184c0 15.312 4.016 29.68 11.056 42.096L0 175.552l47.536-12.464C59.632 170.128 73.12 174.4 87.184 174.4c48.112 0 87.184-39.072 87.184-87.216C174.368 39.072 135.296 0 87.184 0z" fill="#25D366"/>
                                <path d="M130.08 104.992c-2.24-1.12-13.248-6.544-15.296-7.28-2.064-.736-3.552-1.12-5.04 1.12-1.504 2.224-5.776 7.28-7.088 8.784-1.296 1.488-2.608 1.664-4.848.544-2.24-1.12-9.456-3.488-18.016-11.12-6.656-5.936-11.152-13.264-12.464-15.52-1.296-2.224-.144-3.424.976-4.528.992-.992 2.224-2.592 3.328-3.888 1.12-1.296 1.488-2.224 2.24-3.728.752-1.488.368-2.784-.192-3.904-.544-1.12-5.04-12.128-6.912-16.608-1.808-4.368-3.664-3.776-5.04-3.84-1.296-.064-2.784-.08-4.272-.08-1.488 0-3.904.56-5.952 2.784-2.048 2.224-7.824 7.648-7.824 18.64 0 10.992 8.016 21.616 9.136 23.104 1.12 1.504 15.776 24.096 38.24 33.808 5.344 2.304 9.52 3.68 12.768 4.72 5.36 1.712 10.24 1.472 14.096.896 4.304-.64 13.248-5.408 15.12-10.64 1.872-5.232 1.872-9.712 1.312-10.64-.544-.896-2.032-1.44-4.272-2.56z" fill="white"/>
                            </svg>
                        </div>
                        <p class="text-white font-semibold text-sm">Verifying your scanâ€¦</p>
                        <p class="text-slate-500 text-xs">Establishing WhatsApp connection</p>
                        <div style="width:180px;height:3px;background:rgba(255,255,255,0.05);border-radius:3px;overflow:hidden;">
                            <div style="height:100%;background:linear-gradient(90deg,#10b981,#34d399);animation:slideBar 1.5s ease-in-out infinite;"></div>
                        </div>
                    </div>

                    {{-- State 4: Starting spinner (after clicking Generate QR) --}}
                    <div id="qr-starting" style="display:none;" class="flex flex-col items-center gap-3 py-8">
                        <div class="w-10 h-10 border-2 border-slate-700 border-t-emerald-500 rounded-full animate-spin"></div>
                        <p class="text-slate-400 text-xs">Starting WhatsApp engineâ€¦</p>
                    </div>

                    {{-- State 5: Bot offline error --}}
                    <div id="qr-offline" style="display:none;" class="flex flex-col gap-3 px-4 py-6 w-full">
                        <div class="flex items-center gap-3">
                            <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg style="width:18px;height:18px;color:#ef4444;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <p class="text-red-400 font-bold text-sm">Bot Engine is Offline</p>
                                <p class="text-slate-500 text-xs">The Node.js process isn't running</p>
                            </div>
                        </div>
                        <div style="background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:12px;">
                            <p class="text-slate-500 text-[10px] uppercase tracking-wider font-semibold mb-2">Run this in your terminal</p>
                            <div class="flex items-center justify-between gap-2">
                                <code id="bot-cmd" style="font-size:12px;color:#10b981;font-family:monospace;">node src/index.js</code>
                                <button onclick="copyBotCmd()" style="font-size:10px;color:#64748b;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:6px;padding:3px 8px;cursor:pointer;" title="Copy command">Copy</button>
                            </div>
                            <p class="text-slate-600 text-[10px] mt-2">ðŸ“  Run inside the <strong style="color:#94a3b8;">bot/</strong> directory</p>
                        </div>
                        <p style="font-size:11px;color:#64748b;">Or use PM2 for auto-restart: <code style="color:#10b981;">pm2 start pm2.config.js</code></p>
                        <button onclick="retryEngine()" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2 self-start">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Retry Connection
                        </button>
                    </div>

                </div>
            </div>

            {{-- Footer: countdown label / SSE indicator --}}
            <div class="flex items-center justify-between mt-3">
                <p class="text-xs text-slate-600" id="qr-footer-msg">Real-time Â· updates instantly</p>
                <div class="flex items-center gap-1.5">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"  style="animation:pulse 2s ease-in-out infinite;"></div>
                    <span class="text-[10px] text-slate-600" >Auto-refresh</span>
                </div>
            </div>
        </div>
    </div>

    {{-- â”€â”€ RIGHT COL: Activity Feed â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="card p-5 flex flex-col" style="max-height:calc(100vh - 180px);">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-white text-sm">Activity Feed</h2>
            <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 pulse-dot"></div>
                <span class="text-xs text-slate-500">Auto-refresh</span>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto space-y-2" id="activity-feed">
            @forelse ($logs as $log)
            @php
                $icons = [
                    'message_received' => ['icon'=>'<svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>','color'=>'#3b82f6'],
                    'message_sent'     => ['icon'=>'<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>','color'=>'#10b981'],
                    'ai_reply'         => ['icon'=>'<svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>','color'=>'#8b5cf6'],
                    'safety_block'     => ['icon'=>'<svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>','color'=>'#f59e0b'],
                    'human_flag'       => ['icon'=>'<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>','color'=>'#ef4444'],
                    'human_takeover'   => ['icon'=>'<svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>','color'=>'#ec4899'],
                    'status_change'    => ['icon'=>'<svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>','color'=>'#06b6d4'],
                    'error'            => ['icon'=>'<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>','color'=>'#ef4444'],
                ];
                $style = $icons[$log->type] ?? ['icon'=>'<svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>','color'=>'#6b7280'];
            @endphp
            <div class="flex gap-2.5 p-2.5 rounded-lg fade-in" style="background:rgba(255,255,255,0.02);">
                <div class="flex-shrink-0 mt-0.5">{!! $style['icon'] !!}</div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-300 truncate">{{ $log->description }}</p>
                    @if($log->phone)
                    <p class="text-xs text-slate-600">{{ $log->phone }}</p>
                    @endif
                    <p class="text-xs text-slate-700 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-40 text-slate-600">
                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                <p class="text-xs">No activity yet</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
@keyframes qrScan {
    0%   { top: 0;    opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
@keyframes slideBar {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(200%); }
}
#qr-image-wrap { position: relative; }
#qr-scanline   { position: absolute; }
</style>
<script>
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// QR MODULE â€” Fast 1.5s polling (SSE removed â€” incompatible with SQLite
//             + php artisan serve single-thread model)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

let wasShowingQr   = false;
let countdownVal   = 20;
let countdownTimer = null;
let notifRequested = false;
let lastQrSrc      = null;
let pollActive     = true;

// â”€â”€ Show only one QR state at a time â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function showQrState(state) {
    const states = {
        prompt:     document.getElementById('qr-prompt'),
        starting:   document.getElementById('qr-starting'),
        image:      document.getElementById('qr-image-wrap'),
        connecting: document.getElementById('qr-connecting'),
        offline:    document.getElementById('qr-offline'),
    };
    Object.entries(states).forEach(([key, el]) => {
        if (el) el.style.display = key === state ? (key === 'image' ? 'block' : 'flex') : 'none';
    });
    if (state !== 'image') stopCountdown();

    const footer = document.getElementById('qr-footer-msg');
    if (footer) {
        footer.textContent = {
            prompt:     'Click Generate QR to begin',
            starting:   'Starting engine â€” hold onâ€¦',
            image:      'Scan with WhatsApp now Â· refreshing every 1.5s',
            connecting: 'Verifying scanâ€¦',
            offline:    'Node.js engine is not running',
        }[state] || '';
    }
}

// â”€â”€ 20-second QR countdown â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function startCountdown() {
    stopCountdown();
    countdownVal = 20;
    updateCountdown();
    countdownTimer = setInterval(() => {
        countdownVal--;
        updateCountdown();
        if (countdownVal <= 0) stopCountdown();
    }, 1000);
}
function stopCountdown() {
    clearInterval(countdownTimer);
    countdownTimer = null;
}
function updateCountdown() {
    const el = document.getElementById('qr-countdown');
    if (!el) return;
    el.textContent = countdownVal + 's';
    el.style.color = countdownVal <= 7 ? '#ef4444' : '#10b981';
}

// â”€â”€ Handle QR/status update â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function handleQrData(data) {
    const qrCard = document.getElementById('qr-card');

    if (data.status === 'connected') {
        if (qrCard) qrCard.style.display = 'none';
        wasShowingQr = false;
        stopCountdown();
        fireBrowserNotification();
        document.title = 'âœ… Connected â€” Dashboard';
        setTimeout(() => { document.title = 'Dashboard'; }, 5000);
        return;
    }

    if (qrCard) qrCard.style.display = '';

    if (data.qr) {
        const img = document.getElementById('qr-img');
        if (img && data.qr !== lastQrSrc) {
            lastQrSrc = data.qr;
            img.src   = data.qr;
            startCountdown();
            requestNotificationPermission();
        }
        showQrState('image');
        wasShowingQr = true;

    } else if (wasShowingQr) {
        wasShowingQr = false;
        showQrState('connecting');

    } else {
        // Only reset to 'prompt' if we aren't in starting/offline state
        const starting = document.getElementById('qr-starting');
        const offline  = document.getElementById('qr-offline');
        const isStartingVisible = starting && starting.style.display !== 'none';
        const isOfflineVisible  = offline  && offline.style.display  !== 'none';
        if (!isStartingVisible && !isOfflineVisible) {
            showQrState('prompt');
        }
    }
}

// â”€â”€ Fast poll: every 1.5 seconds â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function pollQR() {
    if (!pollActive) return;
    try {
        const r = await fetch('/api/qr');
        const d = await r.json();
        handleQrData(d);
    } catch {}
    // Always reschedule (self-healing)
    setTimeout(pollQR, 1500);
}

// Kick off polling immediately
pollQR();


// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ENGINE CONTROLS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

async function startEngine() {
    showQrState('starting');
    const btn = document.getElementById('start-engine-btn');
    if (btn) btn.disabled = true;

    try {
        const r = await fetch('/api/bot/start', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
        });
        const d = await r.json();
        if (d.status === 'error') {
            showQrState('offline');
        }
        // status === 'starting' â€” pollQR will pick up QR when bot pushes it
    } catch {
        showQrState('offline');
    }
}

async function retryEngine() {
    showQrState('starting');
    await startEngine();
}

async function clearSession() {
    if (!confirm('This will disconnect the current WhatsApp session and force a new QR scan. Continue?')) return;
    try {
        await fetch('/api/qr/clear',        { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
        await fetch('/api/bot/reconnect',   { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
        showQrState('starting');
        setTimeout(() => startEngine(), 1500);
    } catch {
        alert('Could not clear session. Is Laravel running?');
    }
}

function copyBotCmd() {
    navigator.clipboard.writeText('node src/index.js').then(() => {
        const btn  = event.target;
        const orig = btn.textContent;
        btn.textContent  = 'Copied!';
        btn.style.color  = '#10b981';
        setTimeout(() => { btn.textContent = orig; btn.style.color = ''; }, 2000);
    });
}


// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// HEALTH + ACTIVITY POLLING (unchanged)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

async function pauseBot() {
    await fetch('/api/bot/pause', { method:'POST', headers:{ 'X-CSRF-TOKEN': CSRF } });
    pollHealth();
}
async function resumeBot() {
    await fetch('/api/bot/resume', { method:'POST', headers:{ 'X-CSRF-TOKEN': CSRF } });
    pollHealth();
}

const icons = {
    'message_received': {icon:'<svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',color:'#3b82f6'},
    'message_sent':     {icon:'<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>',color:'#10b981'},
    'ai_reply':         {icon:'<svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',color:'#8b5cf6'},
    'safety_block':     {icon:'<svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',color:'#f59e0b'},
    'human_flag':       {icon:'<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>',color:'#ef4444'},
    'human_takeover':   {icon:'<svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',color:'#ec4899'},
    'status_change':    {icon:'<svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',color:'#06b6d4'},
    'error':            {icon:'<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',color:'#ef4444'},
};

async function pollActivity() {
    try {
        const r = await fetch('/api/activity');
        const d = await r.json();
        const feed = document.getElementById('activity-feed');
        if (!feed) return;
        if (d.logs.length === 0) {
            feed.innerHTML = `<div class="flex flex-col items-center justify-center h-40 text-slate-600"><svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg><p class="text-xs">No activity yet</p></div>`;
            return;
        }
        feed.innerHTML = d.logs.map(log => {
            const style = icons[log.type] || {icon:'<svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>',color:'#6b7280'};
            return `<div class="flex gap-2.5 p-2.5 rounded-lg fade-in" style="background:rgba(255,255,255,0.02);"><div class="flex-shrink-0 mt-0.5">${style.icon}</div><div class="min-w-0"><p class="text-xs text-slate-300 truncate">${log.description}</p>${log.phone ? `<p class="text-xs text-slate-600">${log.phone}</p>` : ''}<p class="text-xs text-slate-700 mt-0.5">${log.time}</p></div></div>`;
        }).join('');
    } catch(e) {}
}

async function pollHealth() {
    try {
        const r = await fetch('/api/bot/health');
        const d = await r.json();
        const scoreVal = document.getElementById('health-score-val');
        const ring = document.getElementById('health-ring');
        if (scoreVal) scoreVal.innerText = d.health_score;
        if (ring) {
            ring.setAttribute('stroke-dasharray', `${d.health_score}, 100`);
            if (d.health_score > 70) ring.setAttribute('class', 'text-emerald-500 transition-all duration-1000');
            else if (d.health_score > 40) ring.setAttribute('class', 'text-amber-500 transition-all duration-1000');
            else ring.setAttribute('class', 'text-red-500 transition-all duration-1000');
        }
        const reconnectsVal = document.getElementById('reconnects-val');
        if (reconnectsVal) reconnectsVal.innerText = d.reconnect_count;
        const deliveryVal = document.getElementById('delivery-rate-val');
        if (deliveryVal) deliveryVal.innerText = d.success_rate;
        const lastSeen = document.getElementById('last-seen-val');
        if (lastSeen) lastSeen.innerText = d.last_seen;

        const banner = document.getElementById('failsafe-banner');
        const badge  = document.getElementById('health-badge');
        const title  = document.getElementById('failsafe-title');
        const msg    = document.getElementById('failsafe-msg');
        const notConnected = ['disconnected','reconnecting','paused','logged_out','idle'].includes(d.session_state);
        if (d.ban_risk) {
            if (badge)  badge.classList.remove('hidden');
            if (banner) { banner.classList.remove('hidden'); title.innerText = 'Ban Risk Detected'; msg.innerText = 'Automation paused to protect your number.'; }
        } else if (notConnected) {
            if (badge) badge.classList.add('hidden');
            if (banner) {
                if (['paused','logged_out'].includes(d.session_state)) {
                    banner.classList.remove('hidden'); title.innerText = d.session_state === 'logged_out' ? 'WhatsApp Logged Out' : 'Session Paused'; msg.innerText = 'Click Generate QR Code to reconnect.';
                } else if (['disconnected','reconnecting'].includes(d.session_state)) {
                    banner.classList.remove('hidden'); title.innerText = 'Session Disconnected'; msg.innerText = 'Automated replies paused. Reconnecting...';
                } else { banner.classList.add('hidden'); }
            }
        } else {
            if (badge)  badge.classList.add('hidden');
            if (banner) banner.classList.add('hidden');
        }

        const waStatusText = document.getElementById('wa-status-text');
        const waMainStatus = document.getElementById('wa-main-status');
        if (waStatusText) waStatusText.innerText = d.session_state.charAt(0).toUpperCase() + d.session_state.slice(1);
        if (waMainStatus) {
            waMainStatus.className = 'flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold';
            const dot = waMainStatus.querySelector('div');
            if (d.session_state === 'connected') {
                waMainStatus.classList.add('text-emerald-400'); waMainStatus.style.background = 'rgba(16,185,129,0.1)';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-emerald-400 pulse-dot';
            } else if (['connecting','reconnecting'].includes(d.session_state)) {
                waMainStatus.classList.add('text-amber-400'); waMainStatus.style.background = 'rgba(245,158,11,0.1)';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-amber-400 pulse-dot';
            } else {
                waMainStatus.classList.add('text-red-400'); waMainStatus.style.background = 'rgba(239,68,68,0.1)';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-red-500';
            }
        }
    } catch(e) {}
}

pollHealth();
setInterval(pollHealth, 5000);
pollActivity();
setInterval(pollActivity, 5000);


// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// BROWSER NOTIFICATIONS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

function requestNotificationPermission() {
    if (notifRequested || !('Notification' in window)) return;
    notifRequested = true;
    if (Notification.permission === 'default') Notification.requestPermission();
}

function fireBrowserNotification() {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    new Notification('âœ… WhatsApp Connected!', {
        body: 'Your AI assistant is now live and handling customers.',
        icon: '/ichatup_logo.png',
        tag:  'wa-connected',
        renotify: false,
    });
}
</script>
@endpush

