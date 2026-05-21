<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>iChatUp</title>
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
            border-color: #10b981 !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15) !important;
        }
        input::placeholder { color: #475569 !important; }
        label { color: #94a3b8; font-size: 0.8125rem; font-weight: 500; display: block; margin-bottom: 0.375rem; }
        .error-text { color: #f87171; font-size: 0.75rem; margin-top: 0.25rem; }
        .btn-primary { background: linear-gradient(135deg,#059669,#10b981); border: none; color: white; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 0.75rem; cursor: pointer; width: 100%; font-size: 0.875rem; transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(16,185,129,0.35); }
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
         style="background:linear-gradient(135deg,#060d1a 0%,#0a1428 50%,#060d1a 100%);">

        {{-- Background glow --}}
        <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
            <div class="absolute top-20 left-20 w-72 h-72 rounded-full opacity-10" style="background:radial-gradient(circle,#10b981,transparent);filter:blur(60px);"></div>
            <div class="absolute bottom-40 right-10 w-56 h-56 rounded-full opacity-8" style="background:radial-gradient(circle,#059669,transparent);filter:blur(80px);"></div>
        </div>

        {{-- Logo --}}
        <div class="relative">
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#059669,#10b981);">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                </div>
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

            {{-- Feature bullets --}}
            <div class="space-y-5">
                @php
                $features = [
                    [
                        'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
                        'title' => 'Instant Setup',
                        'desc' => 'Live in under 60 seconds with a QR scan',
                    ],
                    [
                        'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M9 11V7a3 3 0 016 0v4"/><circle cx="12" cy="16" r="1" fill="#10b981"/></svg>',
                        'title' => 'AI Auto-Replies',
                        'desc' => 'GPT-4 replies that understand your business',
                    ],
                    [
                        'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                        'title' => 'Anti-Ban Safety',
                        'desc' => 'Smart protection for your WhatsApp number',
                    ],
                    [
                        'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>',
                        'title' => 'Live Analytics',
                        'desc' => 'Real-time dashboard to monitor everything',
                    ],
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

        {{-- Bottom testimonial --}}
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

    {{-- ── RIGHT PANEL — Auth Form ───────────────────────────── --}}
    <div class="flex-1 flex flex-col items-center justify-center p-8 min-h-screen" style="background:#070e1f;">
        {{-- Mobile logo --}}
        <div class="lg:hidden mb-8">
            <a href="/" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#059669,#10b981);">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                </div>
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
</html>
