<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @php
        $reseller   = app()->bound('active_reseller') ? app('active_reseller') : null;
        $appName    = $reseller?->name ?? 'iChatUp';
        $brandColor = $reseller?->primary_color ?? '#10b981';
        $faviconUrl = $reseller?->favicon_path
                        ? Storage::url($reseller->favicon_path)
                        : asset('favicon.png');
        $logoUrl    = $reseller?->logo_path
                        ? Storage::url($reseller->logo_path)
                        : null;
    @endphp
    <title>{{ $appName }}</title>
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter','sans-serif'] } } } };</script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #060d1a; }
        :root { --brand: {{ $brandColor }}; }
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
            border-color: var(--brand) !important;
            outline: none !important;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 20%, transparent) !important;
        }
        input::placeholder { color: #475569 !important; }
        label { color: #94a3b8; font-size: 0.8125rem; font-weight: 500; display: block; margin-bottom: 0.375rem; }
        .error-text { color: #f87171; font-size: 0.75rem; margin-top: 0.25rem; }
        .btn-primary {
            background: var(--brand);
            border: none; color: white; font-weight: 600;
            padding: 0.75rem 1.5rem; border-radius: 0.75rem;
            cursor: pointer; width: 100%; font-size: 0.875rem; transition: all 0.2s;
        }
        .btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); box-shadow: 0 8px 25px color-mix(in srgb, var(--brand) 35%, transparent); }
        .feature-item { display: flex; align-items: flex-start; gap: 0.75rem; }
        .pulse-dot { animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
    </style>
</head>

@if($reseller)
{{-- ═══════════════════════════════════════════════════════════
     RESELLER DOMAIN — Clean minimal centered login only.
     Zero iChatUp branding. Just the reseller's identity + form.
     ═══════════════════════════════════════════════════════════ --}}
<body class="min-h-screen flex items-center justify-center p-6" style="background: radial-gradient(ellipse at top, #0a1428 0%, #060d1a 60%);">

    {{-- Subtle brand glow behind the card --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 w-[500px] h-[500px] rounded-full opacity-10"
             style="background: radial-gradient(circle, {{ $brandColor }}, transparent); filter: blur(80px);"></div>
    </div>

    <div class="relative w-full max-w-sm">

        {{-- Brand identity --}}
        <div class="flex flex-col items-center mb-8">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-12 object-contain mb-3" />
            @else
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-white text-xl mb-3"
                     style="background: linear-gradient(135deg, {{ $brandColor }}, color-mix(in srgb, {{ $brandColor }} 70%, #000));">
                    {{ strtoupper(substr($appName, 0, 1)) }}
                </div>
            @endif
            <h1 class="text-xl font-bold text-white">{{ $appName }}</h1>
            <p class="text-slate-500 text-xs mt-0.5 text-center">Sign in to your dashboard</p>
        </div>

        {{-- Form card --}}
        <div class="glass-card rounded-2xl p-7 shadow-2xl">
            {{ $slot }}
        </div>

        <p class="mt-6 text-center text-xs text-slate-700">
            © {{ date('Y') }} {{ $appName }}
        </p>
    </div>

</body>

@else
{{-- ═══════════════════════════════════════════════════════════
     MAIN DOMAIN (ichatup.com) — Full split-panel marketing layout
     ═══════════════════════════════════════════════════════════ --}}
<body class="min-h-screen flex">

    {{-- LEFT PANEL — iChatUp Branding --}}
    <div class="hidden lg:flex flex-col justify-between w-[55%] p-12 relative overflow-hidden"
         style="background:linear-gradient(135deg,#060d1a 0%,#0a1428 50%,#060d1a 100%);">

        {{-- Background glow --}}
        <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
            <div class="absolute top-20 left-20 w-72 h-72 rounded-full opacity-10" style="background:radial-gradient(circle,#10b981,transparent);filter:blur(60px);"></div>
            <div class="absolute bottom-40 right-10 w-56 h-56 rounded-full opacity-8" style="background:radial-gradient(circle,#059669,transparent);filter:blur(80px);"></div>
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
                 style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);color:#34d399;">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 pulse-dot"></div>
                WhatsApp AI — Always Online
            </div>
            <h1 class="text-4xl font-extrabold text-white mb-3 leading-tight">
                Your WhatsApp.<br/>
                <span style="background:linear-gradient(135deg,#10b981,#34d399);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Powered by AI.</span>
            </h1>
            <p class="text-slate-400 text-base mb-10 leading-relaxed max-w-sm">
                Set up your AI business assistant in minutes. Scan a QR code and go live instantly.
            </p>

            <div class="space-y-5">
                @php
                $features = [
                    ['icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>', 'title' => 'Instant Setup', 'desc' => 'Live in under 60 seconds with a QR scan'],
                    ['icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M9 11V7a3 3 0 016 0v4"/><circle cx="12" cy="16" r="1" fill="#10b981"/></svg>', 'title' => 'AI Auto-Replies', 'desc' => 'GPT-4 replies that understand your business'],
                    ['icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>', 'title' => 'Anti-Ban Safety', 'desc' => 'Smart protection for your WhatsApp number'],
                    ['icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>', 'title' => 'Live Analytics', 'desc' => 'Real-time dashboard to monitor everything'],
                ];
                @endphp
                @foreach($features as $f)
                <div class="feature-item">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.15);">{!! $f['icon'] !!}</div>
                    <div>
                        <div class="text-white text-sm font-semibold">{{ $f['title'] }}</div>
                        <div class="text-slate-500 text-xs">{{ $f['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Testimonial --}}
        <div class="relative glass-card rounded-2xl p-5">
            <p class="text-slate-300 text-sm italic mb-3">"iChatUp handles 80% of our WhatsApp inquiries automatically. It's like having a full-time support agent for free."</p>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background:linear-gradient(135deg,#059669,#10b981);">R</div>
                <div>
                    <div class="text-white text-xs font-semibold">Rahil A.</div>
                    <div class="text-slate-500 text-xs">E-commerce Business Owner</div>
                </div>
                <div class="ml-auto text-yellow-400 text-sm">★★★★★</div>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL — Auth Form --}}
    <div class="flex-1 flex flex-col items-center justify-center p-8 min-h-screen" style="background:#070e1f;">
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
            © {{ date('Y') }} iChatUp · <a href="/" class="hover:text-slate-500 transition-colors">Back to Home</a>
        </p>
    </div>

</body>
@endif

</html>
