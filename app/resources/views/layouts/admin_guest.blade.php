<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Portal — iChatUp</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter','sans-serif'] } } } };</script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #060d1a; }
        .glass-card { background: rgba(13,22,43,0.8); border: 1px solid rgba(255,255,255,0.08); }
        input, select, textarea {
            background: #0a1428 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #e2e8f0 !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem 1rem !important;
            width: 100%;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #6366f1 !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15) !important;
        }
        input::placeholder { color: #475569 !important; }
        label { color: #94a3b8; font-size: 0.8125rem; font-weight: 500; display: block; margin-bottom: 0.375rem; }
        .error-text { color: #f87171; font-size: 0.75rem; margin-top: 0.25rem; }
        .btn-primary { background: linear-gradient(135deg,#4f46e5,#6366f1); border: none; color: white; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 0.75rem; cursor: pointer; width: 100%; font-size: 0.875rem; transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(99,102,241,0.35); }
        .feature-item { display: flex; align-items: flex-start; gap: 0.75rem; }
        .pulse-dot { animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
    </style>
</head>
<body class="min-h-screen flex">

    {{-- ── LEFT PANEL — Branding ─────────────────────────────── --}}
    <div class="hidden lg:flex flex-col justify-between w-[55%] p-12 relative overflow-hidden"
         style="background:linear-gradient(135deg,#060d1a 0%,#0f0b24 50%,#060d1a 100%);">

        {{-- Background glow --}}
        <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
            <div class="absolute top-20 left-20 w-72 h-72 rounded-full opacity-10" style="background:radial-gradient(circle,#6366f1,transparent);filter:blur(60px);"></div>
            <div class="absolute bottom-40 right-10 w-56 h-56 rounded-full opacity-8" style="background:radial-gradient(circle,#4f46e5,transparent);filter:blur(80px);"></div>
        </div>

        {{-- Logo --}}
        <div class="relative">
            <a href="/" class="flex items-center gap-1.5">
                <img src="{{ asset('ichatup_logo.png') }}" alt="iChatUp Logo" class="w-10 h-10 object-contain rounded-xl" />
                <span class="text-white font-bold text-xl">iChatUp</span>
            </a>
        </div>

        {{-- Main Content --}}
        <div class="relative flex-1 flex flex-col justify-center py-12">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium mb-6 w-fit"
                 style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.25);color:#818cf8;">
                <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 pulse-dot"></div>
                System Command Portal
            </div>
            <h1 class="text-4xl font-extrabold text-white mb-3 leading-tight">
                Global Admin.<br/>
                <span style="background:linear-gradient(135deg,#6366f1,#818cf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Command Center.</span>
            </h1>
            <p class="text-slate-400 text-base mb-10 leading-relaxed max-w-sm">
                Access system settings, manage registered tenant accounts, and monitor active engines.
            </p>

            {{-- Feature bullets --}}
            <div class="space-y-5">
                @php
                $features = [
                    [
                        'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                        'title' => 'Tenant Oversight',
                        'desc' => 'Manage active business accounts and toggle settings.',
                    ],
                    [
                        'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M9 11V7a3 3 0 016 0v4"/><circle cx="12" cy="16" r="1" fill="#6366f1"/></svg>',
                        'title' => 'Emergency AI Controls',
                        'desc' => 'Pause auto-replies globally or per tenant instantly.',
                    ],
                    [
                        'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                        'title' => 'Audit & System Health',
                        'desc' => 'Monitor engine responsiveness and background activities.',
                    ],
                ];
                @endphp
                @foreach($features as $f)
                <div class="feature-item">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);">{!! $f['icon'] !!}</div>
                    <div>
                        <div class="text-white text-sm font-semibold">{{ $f['title'] }}</div>
                        <div class="text-slate-500 text-xs">{{ $f['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Bottom Footer --}}
        <div class="relative glass-card rounded-2xl p-5">
            <p class="text-slate-300 text-xs font-medium">⚠️ Internal administration environment. Unauthorised access attempts are monitored and logged.</p>
        </div>
    </div>

    {{-- ── RIGHT PANEL — Auth Form ───────────────────────────── --}}
    <div class="flex-1 flex flex-col items-center justify-center p-8 min-h-screen" style="background:#070a14;">
        {{-- Mobile logo --}}
        <div class="lg:hidden mb-8">
            <a href="/" class="flex items-center gap-1.5">
                <img src="{{ asset('ichatup_logo.png') }}" alt="iChatUp Logo" class="w-9 h-9 object-contain rounded-xl" />
                <span class="text-white font-bold text-xl">iChatUp</span>
            </a>
        </div>

        <div class="w-full max-w-md">
            {{ $slot }}
        </div>

        <p class="mt-8 text-center text-xs text-slate-700">
            © {{ date('Y') }} iChatUp Admin · <a href="/" class="hover:text-slate-500 transition-colors">Back to Website</a>
        </p>
    </div>

</body>
</html>
