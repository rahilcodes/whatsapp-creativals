<!DOCTYPE html>
<html lang="en" id="html-root" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'iChatUp' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <style>
        :root{--bg:#F8FAFF;--bg2:#EEF2FF;--card:#FFFFFF;--card2:#F1F5FE;--border:rgba(0,0,0,0.07);--border2:rgba(16,185,129,0.3);--text:#0B0F1A;--text2:#374151;--text3:#6B7280;--brand:#059669;--brand2:#10b981;--brand3:#34d399;--glow:rgba(16,185,129,0.1);--nav-bg:rgba(248,250,255,0.88);--shadow:0 4px 24px rgba(0,0,0,0.08);}
        .dark{--bg:#070C18;--bg2:#0D1425;--card:rgba(13,20,37,0.85);--card2:rgba(17,24,39,0.6);--border:rgba(255,255,255,0.07);--border2:rgba(16,185,129,0.3);--text:#F1F5F9;--text2:#CBD5E1;--text3:#64748B;--brand:#10b981;--brand2:#34d399;--brand3:#6ee7b7;--glow:rgba(16,185,129,0.09);--nav-bg:rgba(7,12,24,0.88);--shadow:0 4px 32px rgba(0,0,0,0.4);}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        html{font-family:'Plus Jakarta Sans',sans-serif;scroll-behavior:smooth;}
        body{background:var(--bg);color:var(--text);transition:background .4s,color .4s;overflow-x:hidden;}
        a{text-decoration:none;color:inherit;}
        ::-webkit-scrollbar{width:5px;} ::-webkit-scrollbar-track{background:var(--bg);} ::-webkit-scrollbar-thumb{background:var(--brand);border-radius:10px;}
        .container{max-width:1000px;margin:0 auto;padding:0 24px;}
        
        /* Background Glow Orbs */
        .hero-bg{position:fixed;inset:0;pointer-events:none;z-index:-1;}
        .orb{position:absolute;border-radius:50%;filter:blur(90px);animation:orbf 10s ease-in-out infinite;}
        .orb1{width:600px;height:600px;background:radial-gradient(circle,rgba(16,185,129,.10),transparent);top:-160px;right:-100px;}
        .orb2{width:400px;height:400px;background:radial-gradient(circle,rgba(5,150,105,.06),transparent);bottom:-80px;left:-100px;animation-delay:-4s;}
        @keyframes orbf{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(20px,-20px) scale(1.04)}66%{transform:translate(-16px,16px) scale(.96)}}
        
        /* NAV */
        .navbar{position:fixed;top:0;left:0;right:0;z-index:1000;padding:16px 0;transition:all .3s;}
        .navbar.scrolled{background:var(--nav-bg);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);box-shadow:var(--shadow);}
        .nav-inner{display:flex;align-items:center;justify-content:space-between;max-width:1180px;margin:0 auto;padding:0 24px;}
        .logo{display:flex;align-items:center;gap:5px;}
        .logo-icon{width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        .logo-text{font-size:20px;font-weight:800;color:var(--text);}
        .nav-right{display:flex;align-items:center;gap:10px;}
        .theme-btn{width:38px;height:38px;border-radius:11px;border:1.5px solid var(--border);background:var(--card2);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text3);transition:all .2s;}
        .theme-btn:hover{border-color:var(--brand);color:var(--brand);}
        .nav-login{font-size:14px;font-weight:600;color:var(--text2);padding:9px 20px;border-radius:12px;border:1.5px solid var(--border);transition:all .2s;}
        .nav-login:hover{color:var(--brand);border-color:var(--brand);}
        .nav-cta{font-size:14px;font-weight:700;color:#fff;padding:9px 20px;border-radius:12px;background:linear-gradient(135deg,#059669,#10b981);transition:all .2s;}
        .nav-cta:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(16,185,129,.35);}
        
        /* CONTENT CONTAINER */
        .content-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px;
            margin-top: 120px;
            margin-bottom: 80px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(20px);
        }
        
        /* TYPOGRAPHY */
        .doc-title {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--text);
        }
        .doc-meta {
            font-size: 14px;
            color: var(--text3);
            margin-bottom: 40px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 20px;
        }
        .doc-body h2 {
            font-size: 20px;
            font-weight: 700;
            margin-top: 32px;
            margin-bottom: 14px;
            color: var(--text);
        }
        .doc-body p {
            font-size: 15px;
            line-height: 1.7;
            color: var(--text2);
            margin-bottom: 18px;
        }
        .doc-body ul {
            margin-bottom: 20px;
            padding-left: 20px;
            color: var(--text2);
            font-size: 15px;
            line-height: 1.7;
        }
        .doc-body li {
            margin-bottom: 8px;
        }
        
        /* FOOTER */
        .footer{padding:56px 0 28px;border-top:1px solid var(--border);background:var(--bg2);position:relative;z-index:2;}
        .footer-inner {max-width:1180px;margin:0 auto;padding:0 24px;}
        .fg{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:44px;margin-bottom:44px;}
        .fbrand p{font-size:14px;color:var(--text3);line-height:1.7;margin-top:10px;max-width:250px;}
        .fcol h4{font-size:12px;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.07em;margin-bottom:14px;}
        .fcol ul{list-style:none;display:flex;flex-direction:column;gap:9px;}
        .fcol ul li a{font-size:14px;color:var(--text3);transition:color .2s;}
        .fcol ul li a:hover{color:var(--brand2);}
        .fbot{display:flex;align-items:center;justify-content:space-between;padding-top:24px;border-top:1px solid var(--border);font-size:13px;color:var(--text3);}

        @media(max-width:900px){
            .fg{grid-template-columns:1fr 1fr;gap:28px;}
            .fbot{flex-direction:column;gap:10px;text-align:center;}
            .content-card { padding: 28px; margin-top: 100px; }
            .doc-title { font-size: 28px; }
        }
        @media(max-width:480px){.fg{grid-template-columns:1fr;}}
    </style>
</head>
<body>

    <div class="hero-bg">
        <div class="orb orb1"></div>
        <div class="orb orb2"></div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="nav-inner">
            <a href="/" class="logo">
                <img src="{{ asset('ichatup_logo.png') }}" alt="iChatUp Logo" style="height:32px;width:auto;" />
                <span class="logo-text">iChatUp</span>
            </a>
            <div class="nav-right">
                <button class="theme-btn" id="theme-toggle" title="Toggle theme">
                    <svg id="icon-moon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3a7 7 0 0010 9.79z"/></svg>
                    <svg id="icon-sun" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                @auth
                    <a href="{{ url('/dashboard') }}" class="nav-cta">Dashboard →</a>
                @else
                    <a href="{{ route('login') }}" class="nav-login">Sign In</a>
                    <a href="{{ route('register') }}" class="nav-cta">Start Free →</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- CONTENT -->
    <main class="container">
        <div class="content-card">
            {{ $slot }}
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="fg">
                <div class="fbrand">
                    <a href="/" class="logo">
                        <img src="{{ asset('ichatup_logo.png') }}" alt="iChatUp Logo" style="height:32px;width:auto;" />
                        <span class="logo-text">iChatUp</span>
                    </a>
                    <p>Advanced AI automation for WhatsApp. Connect in 2 minutes, automate 80% of support conversations.</p>
                </div>
                <div class="fcol">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="/#how-it-works">How it works</a></li>
                        <li><a href="/#features">Features</a></li>
                        <li><a href="/#pricing">Pricing</a></li>
                        <li><a href="/#faq">FAQ</a></li>
                    </ul>
                </div>
                <div class="fcol">
                    <h4>Security</h4>
                    <ul>
                        <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}">Terms of Service</a></li>
                        <li><a href="{{ route('refunds') }}">Refund Policy</a></li>
                    </ul>
                </div>
                <div class="fcol">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="mailto:support@ichatup.com">Contact Support</a></li>
                        <li><a href="#">Partner Program</a></li>
                        <li><a href="#">Documentation</a></li>
                    </ul>
                </div>
            </div>
            <div class="fbot">
                <p>© {{ date('Y') }} iChatUp. All rights reserved.</p>
                <div style="display:flex;gap:16px;">
                    <a href="{{ route('terms') }}">Terms</a>
                    <a href="{{ route('privacy') }}">Privacy</a>
                    <a href="{{ route('refunds') }}">Refunds</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const htmlRoot = document.getElementById('html-root');
        const themeBtn = document.getElementById('theme-toggle');
        const iconMoon = document.getElementById('icon-moon');
        const iconSun = document.getElementById('icon-sun');

        let isDark = localStorage.getItem('theme') !== 'light';
        updateThemeUI();

        themeBtn.addEventListener('click', () => {
            isDark = !isDark;
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeUI();
        });

        function updateThemeUI() {
            if (isDark) {
                htmlRoot.classList.add('dark');
                iconMoon.style.display = 'none';
                iconSun.style.display = 'block';
            } else {
                htmlRoot.classList.remove('dark');
                iconMoon.style.display = 'block';
                iconSun.style.display = 'none';
            }
        }

        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
