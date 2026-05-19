@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Live overview & controls')

@section('content')
{{-- ── STAT CARDS ─────────────────────────────────────────────── --}}
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
    {{-- ── LEFT COL: Status + QR ─────────────────────────────── --}}
    <div class="col-span-2 space-y-5">

        {{-- ── FAILSAFE BANNER ────────────────────────────────────────── --}}
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
                        {{ $nodeRunning ? '● Running' : '● Offline' }}
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

        {{-- QR Code Card --}}
        <div class="card p-6" id="qr-card" style="{{ $waStatus->isConnected() ? 'display:none' : '' }}">
            <h2 class="font-semibold text-white mb-2">Scan QR Code</h2>
            <p class="text-xs text-slate-500 mb-5">Open WhatsApp → Linked Devices → Scan this code</p>
            <div class="flex items-center justify-center p-6 rounded-xl" style="background:#fff;">
                <div id="qr-container">
                    @if($waStatus->qr_code)
                        <img src="{{ $waStatus->qr_code }}" alt="QR Code" class="w-56 h-56" />
                    @else
                        <div class="w-56 h-56 flex flex-col items-center justify-center gap-4" style="border:2px dashed #e2e8f0;border-radius:8px;">
                            <div id="qr-spinner" class="w-8 h-8 border-2 border-slate-300 border-t-emerald-500 rounded-full hidden animate-spin"></div>
                            <div id="qr-prompt" class="flex flex-col items-center gap-3">
                                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75V16.5zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/></svg>
                                <button id="start-engine-btn" onclick="startEngine()"
                                    class="btn-primary px-4 py-2 rounded-xl text-xs font-semibold text-white flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9"/></svg>
                                    Generate QR Code
                                </button>
                                <p class="text-slate-400 text-xs text-center">Click to start the WhatsApp engine<br>and generate your login QR</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <p class="text-center text-xs text-slate-600 mt-3">QR auto-refreshes every 5 seconds</p>
        </div>
    </div>

    {{-- ── RIGHT COL: Activity Feed ─────────────────────────── --}}
    <div class="card p-5 flex flex-col" style="max-height:calc(100vh - 180px);">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-white text-sm">Activity Feed</h2>
            <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 pulse-dot"></div>
                <span class="text-xs text-slate-500">Live</span>
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
<script>
// Poll QR code
async function pollQR() {
    try {
        const r = await fetch('/api/qr');
        const d = await r.json();
        const qrCard    = document.getElementById('qr-card');
        const container = document.getElementById('qr-container');

        if (d.status === 'connected') {
            // Truly connected — hide the QR card
            if (qrCard) qrCard.style.display = 'none';
        } else {
            // Not connected — always show the QR card
            if (qrCard) qrCard.style.display = '';

            if (d.qr && container) {
                // QR image arrived — show it for scanning
                container.innerHTML = `<img src="${d.qr}" alt="QR Code" class="w-56 h-56" />`;
            } else if (!d.qr && container) {
                // No QR — show the Generate QR button (only if not already showing)
                if (!container.querySelector('#start-engine-btn')) {
                    showGenerateQRPrompt(container);
                }
            }
        }
    } catch(e) {}
}

function showGenerateQRPrompt(container) {
    container.innerHTML = `
        <div class="w-56 h-56 flex flex-col items-center justify-center gap-4" style="border:2px dashed #e2e8f0;border-radius:8px;">
            <div id="qr-spinner" class="w-8 h-8 border-2 border-slate-300 border-t-emerald-500 rounded-full hidden animate-spin"></div>
            <div id="qr-prompt" class="flex flex-col items-center gap-3">
                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75V16.5zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/></svg>
                <button id="start-engine-btn" onclick="startEngine()"
                    class="btn-primary px-4 py-2 rounded-xl text-xs font-semibold text-white flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9"/></svg>
                    Generate QR Code
                </button>
                <p class="text-slate-400 text-xs text-center">Click to connect WhatsApp</p>
            </div>
        </div>`;
}

async function pauseBot() {
    await fetch('/api/bot/pause', { method:'POST', headers:{ 'X-CSRF-TOKEN': CSRF } });
    pollHealth();
}
async function resumeBot() {
    await fetch('/api/bot/resume', { method:'POST', headers:{ 'X-CSRF-TOKEN': CSRF } });
    pollHealth();
}

pollQR();
setInterval(pollQR, 5000);
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
            return `
            <div class="flex gap-2.5 p-2.5 rounded-lg fade-in" style="background:rgba(255,255,255,0.02);">
                <div class="flex-shrink-0 mt-0.5">${style.icon}</div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-300 truncate">${log.description}</p>
                    ${log.phone ? `<p class="text-xs text-slate-600">${log.phone}</p>` : ''}
                    <p class="text-xs text-slate-700 mt-0.5">${log.time}</p>
                </div>
            </div>`;
        }).join('');
    } catch(e) {}
}

async function pollHealth() {
    try {
        const r = await fetch('/api/bot/health');
        const d = await r.json();
        
        // Update Health Score
        const scoreVal = document.getElementById('health-score-val');
        const ring = document.getElementById('health-ring');
        if (scoreVal) scoreVal.innerText = d.health_score;
        if (ring) {
            const dashArray = `${d.health_score}, 100`;
            ring.setAttribute('stroke-dasharray', dashArray);
            if (d.health_score > 70) ring.setAttribute('class', 'text-emerald-500 transition-all duration-1000');
            else if (d.health_score > 40) ring.setAttribute('class', 'text-amber-500 transition-all duration-1000');
            else ring.setAttribute('class', 'text-red-500 transition-all duration-1000');
        }

        // Update Reconnects and Delivery Rate
        const reconnectsVal = document.getElementById('reconnects-val');
        if (reconnectsVal) reconnectsVal.innerText = d.reconnect_count;
        
        const deliveryVal = document.getElementById('delivery-rate-val');
        if (deliveryVal) deliveryVal.innerText = d.success_rate;
        
        const lastSeen = document.getElementById('last-seen-val');
        if (lastSeen) lastSeen.innerText = d.last_seen;

        // Failsafe Banner & Ban Risk
        const banner = document.getElementById('failsafe-banner');
        const badge  = document.getElementById('health-badge');
        const title  = document.getElementById('failsafe-title');
        const msg    = document.getElementById('failsafe-msg');

        const notConnected = ['disconnected','reconnecting','paused','logged_out','idle'].includes(d.session_state);

        if (d.ban_risk) {
            if (badge)  badge.classList.remove('hidden');
            if (banner) {
                banner.classList.remove('hidden');
                title.innerText = 'Ban Risk Detected';
                msg.innerText   = 'Automation has been paused to protect your number. Manual reconnect required.';
            }
        } else if (notConnected) {
            if (badge)  badge.classList.add('hidden');
            if (banner) {
                if (d.session_state === 'paused' || d.session_state === 'logged_out') {
                    banner.classList.remove('hidden');
                    title.innerText = d.session_state === 'logged_out' ? 'WhatsApp Logged Out' : 'Session Paused';
                    msg.innerText   = 'Please click Generate QR Code below to reconnect.';
                } else if (d.session_state === 'disconnected' || d.session_state === 'reconnecting') {
                    banner.classList.remove('hidden');
                    title.innerText = 'Session Disconnected';
                    msg.innerText   = 'Automated replies are paused. Reconnecting...';
                } else {
                    banner.classList.add('hidden');
                }
            }
        } else {
            if (badge)  badge.classList.add('hidden');
            if (banner) banner.classList.add('hidden');
        }

        // Main Status Badge Update
        const waStatusText = document.getElementById('wa-status-text');
        const waMainStatus = document.getElementById('wa-main-status');
        if (waStatusText) waStatusText.innerText = d.session_state.charAt(0).toUpperCase() + d.session_state.slice(1);
        
        if (waMainStatus) {
            waMainStatus.className = 'flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold';
            const dot = waMainStatus.querySelector('div');
            
            if (d.session_state === 'connected') {
                waMainStatus.classList.add('text-emerald-400');
                waMainStatus.style.background = 'rgba(16,185,129,0.1)';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-emerald-400 pulse-dot';
            } else if (d.session_state === 'connecting' || d.session_state === 'reconnecting') {
                waMainStatus.classList.add('text-amber-400');
                waMainStatus.style.background = 'rgba(245,158,11,0.1)';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-amber-400 pulse-dot';
            } else {
                waMainStatus.classList.add('text-red-400');
                waMainStatus.style.background = 'rgba(239,68,68,0.1)';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-red-500';
            }
        }
    } catch(e) {}
}

pollHealth();
setInterval(pollHealth, 5000);

pollActivity();
setInterval(pollActivity, 5000);

async function startEngine() {
    const btn     = document.getElementById('start-engine-btn');
    const spinner = document.getElementById('qr-spinner');
    const prompt  = document.getElementById('qr-prompt');

    // Show loading state
    if (prompt)  prompt.classList.add('hidden');
    if (spinner) spinner.classList.remove('hidden');
    if (btn)     btn.disabled = true;

    try {
        const r = await fetch('/api/bot/start', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
        });
        const d = await r.json();

        if (d.status === 'already_running') {
            // Already up — just wait for QR to come in via pollQR
        } else if (d.status === 'error') {
            alert('Could not start engine: ' + (d.message || 'Unknown error'));
            if (prompt)  prompt.classList.remove('hidden');
            if (spinner) spinner.classList.add('hidden');
            return;
        }
        // status === 'starting' — engine is booting, QR will appear via pollQR shortly
    } catch(e) {
        alert('Failed to connect to the server. Is Laravel running?');
        if (prompt)  prompt.classList.remove('hidden');
        if (spinner) spinner.classList.add('hidden');
    }
}
</script>
@endpush
