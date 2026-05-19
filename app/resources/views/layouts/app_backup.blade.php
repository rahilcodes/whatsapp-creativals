<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'WhatsApp AI') — WA Assistant</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0',
                            300: '#6ee7b7', 400: '#34d399', 500: '#10b981',
                            600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #070d1a; }
        .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.07); }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover { background: rgba(16,185,129,0.1); color: #34d399; }
        .sidebar-link.active { background: rgba(16,185,129,0.15); color: #10b981; border-left: 3px solid #10b981; }
        .pulse-dot { animation: pulse-glow 2s cubic-bezier(0.4,0,0.6,1) infinite; }
        @keyframes pulse-glow {
            0%,100% { opacity: 1; box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            50%      { opacity: .7; box-shadow: 0 0 0 6px rgba(16,185,129,0); }
        }
        .card { background: #0d1627; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; }
        .btn-primary { background: linear-gradient(135deg,#059669,#10b981); transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(16,185,129,0.3); }
        .btn-danger { background: linear-gradient(135deg,#dc2626,#ef4444); transition: all 0.2s; }
        .btn-danger:hover  { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(239,68,68,0.3); }
        .status-connected    { color: #10b981; }
        .status-disconnected { color: #ef4444; }
        .status-connecting   { color: #f59e0b; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        .fade-in { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
        input, textarea, select {
            background: #0a1120 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #e2e8f0 !important;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #10b981 !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15) !important;
        }
    </style>
    @stack('styles')
</head>
<body class="text-slate-200 min-h-screen flex">

{{-- ── SIDEBAR ─────────────────────────────────────────────── --}}
<aside class="w-64 min-h-screen flex flex-col fixed left-0 top-0 z-30" style="background:#080f1e;border-right:1px solid rgba(255,255,255,0.05);">
    {{-- Logo --}}
    <div class="px-6 py-5 border-b" style="border-color:rgba(255,255,255,0.05);">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#059669,#10b981);">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold text-white">WA Assistant</div>
                <div class="text-xs text-slate-500">AI Business Bot</div>
            </div>
        </div>
    </div>

    {{-- Live Status Badge --}}
    <div class="px-4 py-3 mx-3 mt-3 rounded-lg" style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.15);">
        <div class="flex items-center justify-between">
            <span class="text-xs text-slate-400">WhatsApp</span>
            <div id="sidebar-status" class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-slate-500"></div>
                <span class="text-xs text-slate-400">Loading...</span>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 mt-4 space-y-1">
        @php
            $navItems = [
                ['route' => 'dashboard',      'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
                ['route' => 'chats.index',    'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'Chats'],
                ['route' => 'settings.index', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'AI Settings'],
                ['route' => 'business.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Business Memory'],
            ];
        @endphp

        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    {{-- Bot Toggle at Bottom --}}
    <div class="p-4 border-t" style="border-color:rgba(255,255,255,0.05);">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-slate-400">AI Replies</span>
            <button id="bot-toggle-btn" onclick="toggleBot()"
                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none"
                style="background:#374151;">
                <span id="bot-toggle-thumb" class="inline-block h-3.5 w-3.5 rounded-full bg-white transition-transform translate-x-1"></span>
            </button>
        </div>
        <button onclick="reconnectWA(event)"
            class="w-full flex items-center justify-center text-xs py-2 rounded-lg text-slate-400 border border-slate-700 hover:border-brand-500 hover:text-brand-400 transition-all">
            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            <span>Reconnect WhatsApp</span>
        </button>
    </div>
</aside>

{{-- ── MAIN CONTENT ────────────────────────────────────────── --}}
<main class="ml-64 flex-1 min-h-screen">
    {{-- Top Bar --}}
    <header class="sticky top-0 z-20 px-8 py-4 flex items-center justify-between"
            style="background:rgba(7,13,26,0.8);backdrop-filter:blur(12px);border-bottom:1px solid rgba(255,255,255,0.05);">
        <div>
            <h1 class="text-lg font-semibold text-white">@yield('title', 'Dashboard')</h1>
            <p class="text-xs text-slate-500">@yield('subtitle', 'WhatsApp AI Assistant')</p>
        </div>
        <div class="flex items-center gap-3">
            <div id="wa-status-badge" class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium"
                 style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
                <div class="w-2 h-2 rounded-full bg-slate-500"></div>
                <span class="text-slate-400">Checking...</span>
            </div>
            <div class="text-xs text-slate-500" id="last-updated">--</div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mx-8 mt-4 px-4 py-3 rounded-lg text-sm font-medium fade-in"
             style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#34d399;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mx-8 mt-4 px-4 py-3 rounded-lg text-sm font-medium fade-in"
             style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;">
            ✕ {{ session('error') }}
        </div>
    @endif

    <div class="p-8 fade-in">
        @yield('content')
    </div>
</main>

{{-- ── GLOBAL SCRIPTS ───────────────────────────────────────── --}}
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ── Poll bot status every 5s ──────────────────────────────
    async function pollStatus() {
        try {
            const r = await fetch('/api/bot-status');
            const d = await r.json();
            updateStatusUI(d);
        } catch(e) { /* Node offline */ }
    }

    function updateStatusUI(d) {
        const connected  = d.wa_connected;
        const status     = d.wa_status || 'disconnected';
        const botEnabled = d.bot_enabled;

        // Header badge
        const badge = document.getElementById('wa-status-badge');
        const colors = { connected:'#10b981', disconnected:'#ef4444', connecting:'#f59e0b' };
        const labels = { connected:'Connected ✓', disconnected:'Disconnected', connecting:'Connecting...' };
        if (badge) {
            badge.querySelector('div').style.background = colors[status] || '#6b7280';
            badge.querySelector('span').textContent     = labels[status] || status;
            if (connected) badge.querySelector('div').classList.add('pulse-dot');
        }

        // Sidebar status
        const ss = document.getElementById('sidebar-status');
        if (ss) {
            ss.innerHTML = `<div class="w-2 h-2 rounded-full pulse-dot" style="background:${colors[status] || '#6b7280'}"></div>
                            <span class="text-xs" style="color:${colors[status] || '#6b7280'}">${labels[status] || status}</span>`;
        }

        // Bot toggle
        const btn   = document.getElementById('bot-toggle-btn');
        const thumb = document.getElementById('bot-toggle-thumb');
        if (btn && thumb) {
            btn.style.background   = botEnabled ? '#059669' : '#374151';
            thumb.style.transform  = botEnabled ? 'translateX(18px)' : 'translateX(2px)';
        }

        document.getElementById('last-updated').textContent = 'Updated ' + new Date().toLocaleTimeString();
    }

    async function toggleBot() {
        const r = await fetch('/api/bot/toggle', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
        });
        const d = await r.json();
        pollStatus();
    }

    async function reconnectWA(event) {
        const btn = event.currentTarget || event.target;
        const iconSpan = btn.querySelector('svg');
        const textSpan = btn.querySelector('span');
        
        iconSpan.classList.add('animate-spin');
        textSpan.textContent = 'Reconnecting...';
        btn.disabled = true;
        
        await fetch('/api/bot/reconnect', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
        });
        
        setTimeout(() => { 
            iconSpan.classList.remove('animate-spin');
            textSpan.textContent = 'Reconnect WhatsApp'; 
            btn.disabled = false; 
            pollStatus(); 
        }, 3000);
    }

    pollStatus();
    setInterval(pollStatus, 5000);
</script>
@stack('scripts')
</body>
</html>
