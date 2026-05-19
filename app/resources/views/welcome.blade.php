<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ChatooAI — WhatsApp AI Bot for Business</title>
    <meta name="description" content="Automate your WhatsApp business with AI. ChatooAI handles customer queries 24/7, so you don't have to." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857' }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        float: { '0%,100%': { transform: 'translateY(0px)' }, '50%': { transform: 'translateY(-20px)' } },
                        glow:  { from: { boxShadow: '0 0 20px rgba(16,185,129,0.3)' }, to: { boxShadow: '0 0 40px rgba(16,185,129,0.6)' } },
                    }
                }
            }
        };
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #060d1a; color: #e2e8f0; }
        .glass { background: rgba(255,255,255,0.04); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
        .glass-card { background: rgba(13,22,43,0.8); border: 1px solid rgba(255,255,255,0.07); }
        .gradient-text { background: linear-gradient(135deg, #10b981, #34d399, #6ee7b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .gradient-hero { background: radial-gradient(ellipse at 60% 20%, rgba(16,185,129,0.15) 0%, transparent 60%), radial-gradient(ellipse at 10% 80%, rgba(16,185,129,0.08) 0%, transparent 50%), #060d1a; }
        .btn-primary { background: linear-gradient(135deg, #059669, #10b981); transition: all 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(16,185,129,0.4); }
        .btn-ghost { border: 1px solid rgba(255,255,255,0.15); transition: all 0.2s; }
        .btn-ghost:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.05); }
        .feature-card { background: rgba(13,22,43,0.6); border: 1px solid rgba(255,255,255,0.06); transition: all 0.3s; }
        .feature-card:hover { border-color: rgba(16,185,129,0.3); transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        .stat-card { background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.15); }
        .chat-bubble { background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.2); }
        .chat-bubble-user { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }
        .pulse-dot { animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
    </style>
</head>
<body class="gradient-hero min-h-screen">

{{-- ── NAVBAR ──────────────────────────────────────────────────── --}}
<nav class="fixed top-0 left-0 right-0 z-50 glass" style="border-bottom:1px solid rgba(255,255,255,0.06);">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        {{-- Logo --}}
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#059669,#10b981);">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
            </div>
            <span class="text-white font-bold text-lg tracking-tight">ChatooAI</span>
        </div>

        {{-- Nav Links --}}
        <div class="hidden md:flex items-center gap-8 text-sm text-slate-400">
            <a href="#features" class="hover:text-white transition-colors">Features</a>
            <a href="#how-it-works" class="hover:text-white transition-colors">How it works</a>
            <a href="#pricing" class="hover:text-white transition-colors">Pricing</a>
        </div>

        {{-- CTA Buttons --}}
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-primary px-5 py-2 rounded-xl text-sm font-semibold text-white">
                    Open Dashboard →
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost px-5 py-2 rounded-xl text-sm font-medium text-slate-300">
                    Sign In
                </a>
                <a href="{{ route('register') }}" class="btn-primary px-5 py-2 rounded-xl text-sm font-semibold text-white">
                    Start Free →
                </a>
            @endauth
        </div>
    </div>
</nav>

{{-- ── HERO ─────────────────────────────────────────────────────── --}}
<section class="pt-32 pb-20 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row items-center gap-16">
            {{-- Left: Copy --}}
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-medium mb-6" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);color:#34d399;">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 pulse-dot"></div>
                    WhatsApp AI Automation — Multi-Tenant SaaS
                </div>
                <h1 class="text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                    Your Business on<br/>
                    <span class="gradient-text">WhatsApp. On Autopilot.</span>
                </h1>
                <p class="text-lg text-slate-400 mb-8 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    ChatooAI connects an intelligent AI assistant to your WhatsApp number — answering customers, capturing leads, and sending replies 24/7 while you sleep.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary px-8 py-3.5 rounded-xl text-base font-semibold text-white text-center">
                            Open My Dashboard →
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary px-8 py-3.5 rounded-xl text-base font-semibold text-white text-center">
                            Get Started Free →
                        </a>
                        <a href="{{ route('login') }}" class="btn-ghost px-8 py-3.5 rounded-xl text-base font-medium text-slate-300 text-center">
                            Sign In
                        </a>
                    @endauth
                </div>
                <p class="text-xs text-slate-600 mt-4">No credit card required · Free workspace on sign up</p>
            </div>

            {{-- Right: Animated Chat Preview --}}
            <div class="flex-1 w-full max-w-md mx-auto">
                <div class="relative">
                    {{-- Phone frame --}}
                    <div class="glass-card rounded-3xl p-5 shadow-2xl" style="animation: float 6s ease-in-out infinite;">
                        {{-- Chat header --}}
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-800 mb-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:linear-gradient(135deg,#059669,#10b981);">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            </div>
                            <div>
                                <div class="text-white text-sm font-semibold">Business Assistant</div>
                                <div class="flex items-center gap-1 text-xs text-emerald-400">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div> Online
                                </div>
                            </div>
                        </div>
                        {{-- Chat messages --}}
                        <div class="space-y-3 text-sm">
                            <div class="chat-bubble-user rounded-2xl rounded-tl-sm px-4 py-2.5 ml-8">
                                <p class="text-slate-300">Hi, what are your working hours?</p>
                                <p class="text-xs text-slate-600 mt-1 text-right">9:41 AM ✓✓</p>
                            </div>
                            <div class="chat-bubble rounded-2xl rounded-tr-sm px-4 py-2.5 mr-8">
                                <p class="text-slate-200">Hi there! 👋 We're open Monday–Saturday, 9am to 9pm. How can I help you today?</p>
                                <p class="text-xs text-emerald-600 mt-1">ChatooAI · 9:41 AM</p>
                            </div>
                            <div class="chat-bubble-user rounded-2xl rounded-tl-sm px-4 py-2.5 ml-8">
                                <p class="text-slate-300">Do you have the iPhone 15 Pro in stock?</p>
                                <p class="text-xs text-slate-600 mt-1 text-right">9:42 AM ✓✓</p>
                            </div>
                            <div class="chat-bubble rounded-2xl rounded-tr-sm px-4 py-2.5 mr-8">
                                <p class="text-slate-200">Yes! iPhone 15 Pro is in stock in Black and Natural Titanium 🔥 Want me to reserve one for you?</p>
                                <p class="text-xs text-emerald-600 mt-1">ChatooAI · 9:42 AM</p>
                            </div>
                            <div class="flex items-center gap-2 ml-2">
                                <div class="flex gap-1">
                                    <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full pulse-dot"></div>
                                    <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full pulse-dot" style="animation-delay:0.2s"></div>
                                    <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full pulse-dot" style="animation-delay:0.4s"></div>
                                </div>
                                <span class="text-xs text-slate-600">AI is typing...</span>
                            </div>
                        </div>
                    </div>
                    {{-- Floating badge --}}
                    <div class="absolute -top-4 -right-4 glass px-3 py-2 rounded-xl text-xs font-medium text-emerald-400" style="border-color:rgba(16,185,129,0.3);">
                        ⚡ 0.8s avg reply time
                    </div>
                    <div class="absolute -bottom-4 -left-4 glass px-3 py-2 rounded-xl text-xs font-medium text-slate-300">
                        🤖 Powered by GPT-4
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── STATS ────────────────────────────────────────────────────── --}}
<section class="py-16 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([['24/7', 'Always available'], ['< 1s', 'Reply speed'], ['99.9%', 'Uptime SLA'], ['∞', 'Messages / month']] as $stat)
            <div class="stat-card rounded-2xl p-6 text-center">
                <div class="text-3xl font-extrabold text-white mb-1">{{ $stat[0] }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wider">{{ $stat[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── FEATURES ─────────────────────────────────────────────────── --}}
<section id="features" class="py-20 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <div class="inline-block px-4 py-1.5 rounded-full text-xs font-medium text-emerald-400 mb-4" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);">Features</div>
            <h2 class="text-4xl font-bold text-white mb-4">Everything your business needs</h2>
            <p class="text-slate-400 max-w-2xl mx-auto">From AI-powered replies to real-time analytics, ChatooAI gives you full control over your WhatsApp channel.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php $features = [
                ['🤖', 'AI Auto-Replies', 'GPT-powered responses that understand context, tone, and your business information to reply like a human.'],
                ['⚡', 'Instant Setup', 'Scan a QR code and your AI bot is live in under 60 seconds. No coding, no complex integrations.'],
                ['🛡️', 'Anti-Ban Protection', 'Smart rate limiting, session health monitoring, and automated failsafes protect your WhatsApp number.'],
                ['🧠', 'Business Memory', 'Train your bot with your products, FAQs, pricing, and policies. It remembers everything.'],
                ['👥', 'Multi-Tenant', 'Each workspace is fully isolated. Scale to hundreds of businesses on a single platform.'],
                ['📊', 'Live Dashboard', 'Real-time activity feed, message analytics, health scores, and full control from one panel.'],
            ]; @endphp
            @foreach($features as $f)
            <div class="feature-card rounded-2xl p-6">
                <div class="text-3xl mb-4">{{ $f[0] }}</div>
                <h3 class="text-white font-semibold text-lg mb-2">{{ $f[1] }}</h3>
                <p class="text-slate-500 text-sm leading-relaxed">{{ $f[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── HOW IT WORKS ─────────────────────────────────────────────── --}}
<section id="how-it-works" class="py-20 px-6">
    <div class="max-w-4xl mx-auto text-center">
        <div class="inline-block px-4 py-1.5 rounded-full text-xs font-medium text-emerald-400 mb-4" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);">How it works</div>
        <h2 class="text-4xl font-bold text-white mb-4">Live in 3 steps</h2>
        <p class="text-slate-400 mb-16">No developers required.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['01', 'Create Account', 'Sign up with your email or Google. A private workspace is created instantly for you.'],
                ['02', 'Scan QR Code', 'Open your WhatsApp, tap Linked Devices, and scan the QR from your dashboard.'],
                ['03', 'Go Live', 'Your AI bot is now answering messages. Train it with your business info from the dashboard.'],
            ] as $step)
            <div class="glass-card rounded-2xl p-8">
                <div class="text-5xl font-extrabold mb-4" style="color:rgba(16,185,129,0.3);">{{ $step[0] }}</div>
                <h3 class="text-white font-semibold text-lg mb-2">{{ $step[1] }}</h3>
                <p class="text-slate-500 text-sm leading-relaxed">{{ $step[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA BANNER ───────────────────────────────────────────────── --}}
<section class="py-20 px-6">
    <div class="max-w-4xl mx-auto text-center">
        <div class="rounded-3xl p-12" style="background:linear-gradient(135deg,rgba(5,150,105,0.2),rgba(16,185,129,0.1));border:1px solid rgba(16,185,129,0.2);">
            <h2 class="text-4xl font-bold text-white mb-4">Ready to automate your WhatsApp?</h2>
            <p class="text-slate-400 mb-8 max-w-xl mx-auto">Join businesses already using ChatooAI. Set up your workspace in 60 seconds.</p>
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-primary inline-block px-10 py-4 rounded-xl text-base font-semibold text-white">Open My Dashboard →</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary inline-block px-10 py-4 rounded-xl text-base font-semibold text-white">Create Free Workspace →</a>
            @endauth
        </div>
    </div>
</section>

{{-- ── FOOTER ───────────────────────────────────────────────────── --}}
<footer class="border-t py-10 px-6" style="border-color:rgba(255,255,255,0.06);">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:linear-gradient(135deg,#059669,#10b981);">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            </div>
            <span class="text-white font-semibold">ChatooAI</span>
        </div>
        <p class="text-slate-600 text-sm">© {{ date('Y') }} ChatooAI. All rights reserved.</p>
        <div class="flex items-center gap-6 text-sm text-slate-500">
            <a href="{{ route('login') }}" class="hover:text-white transition-colors">Login</a>
            <a href="{{ route('register') }}" class="hover:text-white transition-colors">Sign Up</a>
        </div>
    </div>
</footer>

</body>
</html>
