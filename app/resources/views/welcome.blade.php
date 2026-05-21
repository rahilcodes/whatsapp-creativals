<!DOCTYPE html>
<html lang="en" id="html-root" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>iChatUp — WhatsApp AI Bot. Live in 2 Minutes.</title>
    <meta name="description" content="Connect AI to your WhatsApp in 2 minutes. No API. No WhatsApp deletion. Unlimited messages. Pre-built business memory. Start free today." />
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
        .container{max-width:1180px;margin:0 auto;padding:0 24px;}
        .gradient-text{background:linear-gradient(135deg,var(--brand),var(--brand2),var(--brand3));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
        .pill{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:100px;font-size:12px;font-weight:600;background:var(--glow);border:1px solid var(--border2);color:var(--brand2);}
        .pill .dot{width:6px;height:6px;border-radius:50%;background:var(--brand2);animation:pdot 2s infinite;}
        @keyframes pdot{0%,100%{opacity:1}50%{opacity:.4}}
        .btn-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:14px;font-size:15px;font-weight:700;color:#fff;background:linear-gradient(135deg,#059669,#10b981);border:none;cursor:pointer;transition:all .3s;}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(16,185,129,.4);}
        .btn-ghost{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:14px;font-size:15px;font-weight:600;color:var(--text2);background:transparent;border:1.5px solid var(--border);cursor:pointer;transition:all .3s;}
        .btn-ghost:hover{border-color:var(--brand);color:var(--brand);background:var(--glow);}
        .reveal{opacity:0;transform:translateY(28px);transition:opacity .7s,transform .7s;}
        .reveal.visible{opacity:1;transform:none;}
        .reveal-left{opacity:0;transform:translateX(-36px);transition:opacity .7s,transform .7s;}
        .reveal-left.visible{opacity:1;transform:none;}
        .reveal-right{opacity:0;transform:translateX(36px);transition:opacity .7s,transform .7s;}
        .reveal-right.visible{opacity:1;transform:none;}
        .d1{transition-delay:.1s;}.d2{transition-delay:.2s;}.d3{transition-delay:.3s;}.d4{transition-delay:.4s;}

        /* NAV */
        .navbar{position:fixed;top:0;left:0;right:0;z-index:1000;padding:16px 0;transition:all .3s;}
        .navbar.scrolled{background:var(--nav-bg);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);box-shadow:var(--shadow);}
        .nav-inner{display:flex;align-items:center;justify-content:space-between;}
        .logo{display:flex;align-items:center;gap:10px;}
        .logo-icon{width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        .logo-text{font-size:20px;font-weight:800;color:var(--text);}
        .nav-links{display:flex;align-items:center;gap:32px;}
        .nav-links a{font-size:14px;font-weight:500;color:var(--text3);transition:color .2s;}
        .nav-links a:hover{color:var(--brand2);}
        .nav-right{display:flex;align-items:center;gap:10px;}
        .theme-btn{width:38px;height:38px;border-radius:11px;border:1.5px solid var(--border);background:var(--card2);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text3);transition:all .2s;}
        .theme-btn:hover{border-color:var(--brand);color:var(--brand);}
        .nav-login{font-size:14px;font-weight:600;color:var(--text2);padding:9px 20px;border-radius:12px;border:1.5px solid var(--border);transition:all .2s;}
        .nav-login:hover{color:var(--brand);border-color:var(--brand);}
        .nav-cta{font-size:14px;font-weight:700;color:#fff;padding:9px 20px;border-radius:12px;background:linear-gradient(135deg,#059669,#10b981);transition:all .2s;}
        .nav-cta:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(16,185,129,.35);}

        /* HERO */
        .hero{height:100vh;min-height:620px;display:flex;flex-direction:column;justify-content:center;padding:0;position:relative;overflow:clip;}
        .hero-bg{position:absolute;inset:0;pointer-events:none;}
        .orb{position:absolute;border-radius:50%;filter:blur(90px);animation:orbf 10s ease-in-out infinite;}
        .orb1{width:600px;height:600px;background:radial-gradient(circle,rgba(16,185,129,.14),transparent);top:-160px;right:-100px;}
        .orb2{width:400px;height:400px;background:radial-gradient(circle,rgba(5,150,105,.10),transparent);bottom:-80px;left:-100px;animation-delay:-4s;}
        .orb3{width:260px;height:260px;background:radial-gradient(circle,rgba(52,211,153,.12),transparent);top:45%;left:38%;animation-delay:-6s;}
        /* hero grid line accent */
        .hero-bg::after{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(16,185,129,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(16,185,129,0.03) 1px,transparent 1px);background-size:60px 60px;pointer-events:none;}
        @keyframes orbf{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(20px,-20px) scale(1.04)}66%{transform:translate(-16px,16px) scale(.96)}}
        .hero-inner{display:flex;align-items:center;width:100%;gap:0;}
        .hero-left{flex:1;min-width:0;padding-right:40px;}
        .hero-right{width:420px;flex-shrink:0;display:flex;justify-content:flex-end;align-items:center;}
        .hero-eyebrow{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:100px;font-size:12px;font-weight:600;background:var(--glow);border:1px solid var(--border2);color:var(--brand2);margin-bottom:20px;}
        .hero-eyebrow .dot{width:6px;height:6px;border-radius:50%;background:var(--brand2);animation:pdot 2s infinite;}
        .hero-title{font-size:clamp(32px,3.8vw,52px);font-weight:900;line-height:1.08;letter-spacing:-0.02em;margin-bottom:14px;}
        .hero-sub{font-size:16px;line-height:1.65;color:var(--text3);margin-bottom:20px;max-width:460px;}
        .benefit-chips{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:22px;}
        .chip{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:100px;font-size:11.5px;font-weight:600;background:var(--glow);border:1px solid var(--border2);color:var(--brand2);}
        .hero-ctas{display:flex;gap:11px;flex-wrap:wrap;margin-bottom:18px;align-items:center;}
        .proof-row{display:flex;align-items:center;gap:11px;font-size:12.5px;color:var(--text3);}
        .proof-avs{display:flex;}
        .pav{width:26px;height:26px;border-radius:50%;border:2px solid var(--bg);margin-left:-7px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;}
        .pav:first-child{margin-left:0;}
        /* hero stat pills at bottom */
        .hero-stats{display:flex;gap:0;border-top:1px solid var(--border);margin-top:24px;padding-top:18px;}
        .hstat{display:flex;align-items:center;gap:8px;padding-right:24px;margin-right:24px;border-right:1px solid var(--border);}
        .hstat:last-child{border-right:none;padding-right:0;margin-right:0;}
        .hstat-num{font-size:18px;font-weight:800;color:var(--text);}
        .hstat-lbl{font-size:11px;color:var(--text3);line-height:1.3;}
        .hstat-icon{width:32px;height:32px;border-radius:9px;background:var(--glow);border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;flex-shrink:0;}

        /* PHONE MOCKUP */
        .phone-wrap{display:flex;justify-content:flex-end;position:relative;padding:30px 0 30px 30px;}
        .phone-shell{
            width:272px;
            height:430px;
            display:flex;
            flex-direction:column;
            background:#ffffff;
            border:1.5px solid rgba(0,0,0,0.10);
            border-radius:34px;
            padding:14px 14px 18px;
            box-shadow:0 24px 64px rgba(0,0,0,0.14),0 6px 20px rgba(0,0,0,0.08),0 0 0 1px rgba(0,0,0,0.04);
            animation:pfloat 6s ease-in-out infinite;
            position:relative;z-index:2;
            box-sizing:border-box;
        }
        .dark .phone-shell{
            background:rgba(10,16,32,0.95);
            border:1.5px solid rgba(255,255,255,0.10);
            box-shadow:0 32px 80px rgba(0,0,0,.5),0 0 0 1px rgba(255,255,255,0.05);
        }
        @keyframes pfloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
        .chat-area{
            flex:1;
            overflow-y:auto;
            display:flex;
            flex-direction:column;
            gap:8px;
            padding-right:2px;
            scroll-behavior:smooth;
        }
        .chat-area::-webkit-scrollbar { display: none; }
        .chat-area { -ms-overflow-style: none; scrollbar-width: none; }
        .cmsg {
            display:none;
            opacity:0;
            transition:opacity .3s, transform .3s;
            transform:translateY(8px);
        }
        div[id^="c"][id$="t"] {
            display:none;
            opacity:0;
            transition:opacity .3s, transform .3s;
            transform:translateY(8px);
            align-self:flex-start;
        }
        .phone-notch{width:60px;height:4px;background:#e2e8f0;border-radius:2px;margin:0 auto 12px;}
        .dark .phone-notch{background:var(--border);}
        .phone-status{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #e2e8f0;}
        .dark .phone-status{border-bottom-color:var(--border);}
        .phone-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(16,185,129,0.3);}
        .phone-info{flex:1;margin-left:9px;}
        .phone-name{font-size:12px;font-weight:700;color:#0f172a;}
        .dark .phone-name{color:var(--text);}
        .online{font-size:10px;color:var(--brand2);display:flex;align-items:center;gap:3px;}
        .online::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--brand2);display:inline-block;animation:pdot 1.5s infinite;}
        .phone-actions{display:flex;gap:8px;}
        .phone-action-btn{width:26px;height:26px;border-radius:8px;background:#f1f5f9;border:1px solid rgba(0,0,0,0.08);display:flex;align-items:center;justify-content:center;}
        .dark .phone-action-btn{background:var(--card2);border-color:var(--border);}
        .cmsg {
            margin-bottom:7px;
            width:100%;
            display:none;
            flex-direction:column;
        }
        .cmsg.user {
            align-items:flex-end;
        }
        .cmsg.bot {
            align-items:flex-start;
        }
        .cbubble {
            display:flex;
            flex-direction:column;
            padding:9px 12px 6px;
            border-radius:14px;
            font-size:11.5px;
            line-height:1.45;
            max-width:88%;
            box-sizing:border-box;
        }
        /* Light Mode User Bubble */
        .cmsg.user .cbubble {
            background:#f1f5f9;
            border:1px solid #e2e8f0;
            color:#0f172a;
            border-radius:14px 14px 2px 14px;
        }
        /* Light Mode Bot Bubble */
        .cmsg.bot .cbubble {
            background:rgba(16,185,129,0.1);
            border:1px solid rgba(16,185,129,0.2);
            color:#065f46;
            border-radius:14px 14px 14px 2px;
        }
        /* Dark Mode User Bubble */
        .dark .cmsg.user .cbubble {
            background:#1e293b;
            border:1px solid #334155;
            color:#f8fafc;
        }
        /* Dark Mode Bot Bubble */
        .dark .cmsg.bot .cbubble {
            background:rgba(16,185,129,0.15);
            border:1px solid rgba(16,185,129,0.3);
            color:#ecfdf5;
        }
        .c-text {
            font-size:11.5px;
            line-height:1.45;
        }
        .c-meta-user {
            display:flex;
            align-items:center;
            justify-content:flex-end;
            gap:3px;
            font-size:9px;
            color:#64748b;
            margin-top:4px;
        }
        .dark .c-meta-user {
            color:#94a3b8;
        }
        .c-meta-bot {
            font-size:9px;
            color:#059669;
            margin-top:4px;
            font-weight:600;
            text-align:left;
        }
        .dark .c-meta-bot {
            color:#10b981;
        }
        .typing{display:flex;gap:3px;align-items:center;padding:7px 11px;background:#f1f5f9;border:1px solid rgba(0,0,0,0.07);border-radius:12px 12px 12px 3px;width:fit-content;}
        .dark .typing{background:var(--card2);border:1px solid var(--border);}
        .tdot{width:5px;height:5px;border-radius:50%;background:#94a3b8;animation:tdot 1.2s infinite;}
        .tdot:nth-child(2){animation-delay:.2s;}.tdot:nth-child(3){animation-delay:.4s;}
        @keyframes tdot{0%,80%,100%{transform:scale(.7);opacity:.5}40%{transform:scale(1);opacity:1}}
        /* floating badges */
        .fbadge{position:absolute;border-radius:12px;padding:8px 13px;font-size:11px;font-weight:600;white-space:nowrap;display:flex;align-items:center;gap:6px;animation:pfloat 6s ease-in-out infinite;z-index:10;}
        /* light mode badge */
        .fbadge.glass{background:rgba(255,255,255,0.96);border:1px solid rgba(0,0,0,0.10);backdrop-filter:blur(16px);box-shadow:0 8px 32px rgba(0,0,0,.12),0 2px 8px rgba(0,0,0,.08);color:#374151;}
        /* dark mode badge override */
        .dark .fbadge.glass{background:rgba(10,16,32,0.92);border:1px solid rgba(255,255,255,0.12);box-shadow:0 8px 32px rgba(0,0,0,.4);color:#CBD5E1;}
        .fb-speed{top:-24px;right:16px;animation-delay:-2s;}
        .fb-ai{bottom:-22px;left:-10px;animation-delay:-4s;}
        .fb-dot{width:8px;height:8px;border-radius:50%;background:var(--brand2);flex-shrink:0;box-shadow:0 0 0 3px rgba(16,185,129,0.2);}

        /* STATS MARQUEE */
        .stats-bar{padding:28px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);background:var(--bg2);overflow:hidden;}
        .mtrack{display:flex;gap:60px;width:max-content;animation:mq 28s linear infinite;}
        .stats-bar:hover .mtrack{animation-play-state:paused;}
        @keyframes mq{from{transform:translateX(0)}to{transform:translateX(-50%)}}
        .sitem{display:flex;align-items:center;gap:12px;white-space:nowrap;flex-shrink:0;}
        .sicon{width:36px;height:36px;border-radius:10px;background:var(--glow);border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;font-size:16px;}
        .snum{font-size:20px;font-weight:800;color:var(--text);}
        .slbl{font-size:12px;color:var(--text3);font-weight:500;}

        /* STEPS */
        .steps-sec{padding:100px 0;}
        .steps-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:start;margin-top:56px;}
        .step-item{display:flex;flex-direction:column;align-items:flex-start;gap:6px;padding:20px 0 20px 28px;border-left:2px solid var(--border);position:relative;cursor:pointer;transition:all .3s;}
        .step-item::before{content:'';position:absolute;left:-7px;top:26px;width:12px;height:12px;border-radius:50%;background:var(--border);border:2px solid var(--bg);transition:all .3s;}
        .step-item.active::before{background:var(--brand);box-shadow:0 0 0 4px rgba(16,185,129,.2);}
        .step-item.active{border-left-color:var(--brand);}
        .step-num{font-size:11px;font-weight:700;color:var(--brand2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px;}
        .step-title{font-size:17px;font-weight:700;color:var(--text);margin-bottom:5px;}
        .step-desc{font-size:14px;color:var(--text3);line-height:1.6;}
        .step-time{display:inline-flex;align-items:center;gap:4px;margin-top:6px;padding:3px 10px;border-radius:100px;font-size:11px;font-weight:600;background:rgba(16,185,129,.1);color:var(--brand2);}
        .demo-box{background:var(--card);border:1px solid var(--border);border-radius:24px;padding:28px;min-height:340px;display:flex;flex-direction:column;justify-content:center;align-items:center;box-shadow:var(--shadow);}
        .demo-panel{display:none;width:100%;animation:fiu .4s ease;flex-direction:column;align-items:center;gap:14px;}
        .demo-panel.active{display:flex;}
        @keyframes fiu{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}

        /* FEATURES */
        .feat-sec{padding:100px 0;}
        .bento{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:56px;}
        .bcard{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:26px;transition:all .35s;position:relative;overflow:hidden;}
        .bcard::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--brand),transparent);opacity:0;transition:opacity .3s;}
        .bcard:hover::before{opacity:1;}
        .bcard:hover{border-color:var(--border2);box-shadow:0 20px 48px rgba(16,185,129,.1);transform:translateY(-4px);}
        .bcard.wide{grid-column:span 2;}
        .bicon{width:46px;height:46px;border-radius:13px;background:var(--glow);border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:14px;}
        .btitle{font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px;}
        .bdesc{font-size:14px;color:var(--text3);line-height:1.65;}
        .vs-tbl{width:100%;margin-top:14px;border-collapse:collapse;}
        .vs-tbl th{font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text3);letter-spacing:.07em;padding:6px 8px;text-align:left;}
        .vs-tbl td{font-size:13px;padding:7px 8px;border-top:1px solid var(--border);color:var(--text2);}
        .vs-tbl .yes{color:var(--brand2);font-weight:600;}
        .vs-tbl .no{color:#ef4444;font-weight:600;}

        /* TESTIMONIALS */
        .testi-sec{padding:100px 0;background:var(--bg2);overflow:hidden;}
        .ttrack{display:flex;gap:18px;width:max-content;animation:mq 32s linear infinite;margin-top:44px;}
        .testi-sec:hover .ttrack{animation-play-state:paused;}
        .tcard{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:22px;width:295px;flex-shrink:0;transition:border-color .3s;}
        .tcard:hover{border-color:var(--border2);}
        .tstars{color:#F59E0B;font-size:13px;margin-bottom:10px;}
        .ttext{font-size:14px;color:var(--text2);line-height:1.65;margin-bottom:14px;font-style:italic;}
        .tauthor{display:flex;align-items:center;gap:10px;}
        .tav{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#fff;flex-shrink:0;}
        .tname{font-size:13px;font-weight:600;color:var(--text);}
        .tbiz{font-size:12px;color:var(--text3);}

        /* PRICING */
        .price-sec{padding:100px 0;}
        .ptoggle{display:flex;align-items:center;gap:12px;justify-content:center;margin:28px 0 44px;}
        .plbl{font-size:14px;font-weight:500;color:var(--text3);transition:color .2s;}
        .plbl.active{color:var(--text);font-weight:600;}
        .pswitch{width:46px;height:25px;border-radius:13px;background:var(--brand);cursor:pointer;position:relative;transition:background .3s;}
        .pswitch::after{content:'';position:absolute;top:3px;left:3px;width:19px;height:19px;border-radius:50%;background:#fff;transition:transform .3s;}
        .pswitch.on::after{transform:translateX(21px);}
        .dbadge{background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:100px;}
        .pgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
        .pcard{background:var(--card);border:1px solid var(--border);border-radius:24px;padding:30px;position:relative;transition:all .3s;}
        .pcard.pop{border-color:var(--brand);box-shadow:0 0 0 1px var(--brand),0 24px 48px rgba(16,185,129,.15);}
        .pcard:hover{transform:translateY(-4px);}
        .popbadge{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:11px;font-weight:700;padding:3px 14px;border-radius:100px;white-space:nowrap;}
        .pname{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:7px;}
        .pprice{font-size:42px;font-weight:900;color:var(--text);line-height:1;margin-bottom:3px;}
        .pprice span{font-size:17px;font-weight:500;color:var(--text3);}
        .ptagline{font-size:13px;color:var(--text3);margin-bottom:24px;}
        .pdiv{height:1px;background:var(--border);margin:18px 0;}
        .pfeats{list-style:none;display:flex;flex-direction:column;gap:9px;margin-bottom:24px;}
        .pfeats li{display:flex;align-items:flex-start;gap:7px;font-size:14px;color:var(--text2);}
        .pck{color:var(--brand2);font-size:14px;flex-shrink:0;}
        .pcx{color:#9CA3AF;font-size:12px;flex-shrink:0;margin-top:2px;}
        .pbtn{display:block;text-align:center;padding:12px;border-radius:12px;font-size:14px;font-weight:700;transition:all .25s;}
        .pbtn.out{border:1.5px solid var(--border);color:var(--text2);}
        .pbtn.out:hover{border-color:var(--brand);color:var(--brand);background:var(--glow);}
        .pbtn.sol{background:linear-gradient(135deg,#059669,#10b981);color:#fff;}
        .pbtn.sol:hover{box-shadow:0 8px 24px rgba(16,185,129,.35);transform:translateY(-1px);}

        /* FAQ */
        .faq-sec{padding:100px 0;background:var(--bg2);}
        .faq-list{max-width:720px;margin:52px auto 0;display:flex;flex-direction:column;gap:8px;}
        .fitem{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;transition:border-color .3s;}
        .fitem.open{border-color:var(--border2);}
        .fq{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;cursor:pointer;font-size:15px;font-weight:600;color:var(--text);gap:14px;}
        .ficon{width:24px;height:24px;border-radius:50%;background:var(--glow);border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--brand2);font-size:16px;transition:transform .3s;}
        .fitem.open .ficon{transform:rotate(45deg);}
        .fa{max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s;font-size:14px;color:var(--text3);line-height:1.7;padding:0 22px;}
        .fitem.open .fa{max-height:200px;padding-bottom:18px;}

        /* CTA */
        .cta-sec{padding:100px 0;}
        .cta-box{border-radius:32px;padding:80px 44px;text-align:center;background:linear-gradient(135deg,rgba(5,150,105,.15),rgba(16,185,129,.07));border:1px solid var(--border2);position:relative;overflow:hidden;}
        .cta-box::before{content:'';position:absolute;top:-80px;right:-80px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,.2),transparent);filter:blur(40px);pointer-events:none;}
        .cta-title{font-size:clamp(30px,4vw,50px);font-weight:900;color:var(--text);margin-bottom:14px;position:relative;z-index:1;}
        .cta-sub{font-size:17px;color:var(--text3);margin-bottom:36px;position:relative;z-index:1;}
        .cta-pulse{position:relative;display:inline-block;z-index:1;}
        .cta-pulse::before{content:'';position:absolute;inset:-8px;border-radius:22px;background:rgba(16,185,129,.18);animation:cpulse 2s ease-in-out infinite;}
        @keyframes cpulse{0%,100%{transform:scale(1);opacity:.5}50%{transform:scale(1.05);opacity:1}}

        /* FOOTER */
        .footer{padding:56px 0 28px;border-top:1px solid var(--border);}
        .fg{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:44px;margin-bottom:44px;}
        .fbrand p{font-size:14px;color:var(--text3);line-height:1.7;margin-top:10px;max-width:250px;}
        .fcol h4{font-size:12px;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.07em;margin-bottom:14px;}
        .fcol ul{list-style:none;display:flex;flex-direction:column;gap:9px;}
        .fcol ul li a{font-size:14px;color:var(--text3);transition:color .2s;}
        .fcol ul li a:hover{color:var(--brand2);}
        .fbot{display:flex;align-items:center;justify-content:space-between;padding-top:24px;border-top:1px solid var(--border);font-size:13px;color:var(--text3);}

        /* RESPONSIVE */
        @media(max-width:900px){
            .hero-inner{flex-direction:column;gap:36px;}
            .hero-left{padding-right:0;text-align:center;}
            .hero-right{width:100%;justify-content:center;}
            .hero-sub,.proof-row,.hero-ctas,.benefit-chips,.hero-stats{margin-left:auto;margin-right:auto;justify-content:center;}
            .hero-stats{flex-wrap:wrap;gap:12px;}
            .hstat{border-right:none;padding-right:12px;margin-right:12px;border-right:1px solid var(--border);}
            .hstat:last-child{border-right:none;}
            .fbadge{display:none;}
            .steps-grid{grid-template-columns:1fr;gap:44px;text-align:center;}
            .bento{grid-template-columns:1fr;}.bcard.wide{grid-column:span 1;}
            .pgrid{grid-template-columns:1fr;max-width:380px;margin:0 auto;}
            .fg{grid-template-columns:1fr 1fr;gap:28px;}
            .fbot{flex-direction:column;gap:10px;text-align:center;}
            .nav-links,.nav-login{display:none;}
        }
        @media(max-width:480px){.fg{grid-template-columns:1fr;}.cta-box{padding:48px 20px;}.hero{height:auto;min-height:100vh;padding:80px 0 40px;}}
    </style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<nav class="navbar" id="navbar">
    <div class="container nav-inner">
        <a href="/" class="logo">
            <div class="logo-icon">
                <svg width="20" height="20" fill="white" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            </div>
            <span class="logo-text">iChatUp</span>
        </a>
        <div class="nav-links">
            <a href="#how-it-works">How it works</a>
            <a href="#features">Features</a>
            <a href="#pricing">Pricing</a>
            <a href="#faq">FAQ</a>
        </div>
        <div class="nav-right">
            <button class="theme-btn" id="theme-toggle" title="Toggle theme">
                <svg id="icon-moon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3a7 7 0 0010 9.79z"/></svg>
                <svg id="icon-sun" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
            @auth
                <a href="{{ url('/dashboard') }}" class="nav-cta">Open Dashboard →</a>
            @else
                <a href="{{ route('login') }}" class="nav-login">Sign In</a>
                <a href="{{ route('register') }}" class="nav-cta">Start Free →</a>
            @endauth
        </div>
    </div>
</nav>

<!-- ══ HERO ══ -->
<section class="hero" id="hero">
    <div class="hero-bg"><div class="orb orb1"></div><div class="orb orb2"></div><div class="orb orb3"></div></div>
    <div class="container" style="width:100%;">
        <div class="hero-inner">

            {{-- LEFT COLUMN --}}
            <div class="hero-left reveal-left">
                <!-- eyebrow pill -->
                <div class="hero-eyebrow"><span class="dot"></span>WhatsApp AI · No API Required · Live in 2 min</div>

                <!-- headline -->
                <h1 class="hero-title">
                    Your WhatsApp.<br/>
                    <span class="gradient-text">On Autopilot.</span>
                </h1>

                <!-- subheadline -->
                <p class="hero-sub">Connect AI to your existing WhatsApp in 2 minutes. No number deletion. No Business API. No per-message fees.</p>

                <!-- chips -->
                <div class="benefit-chips">
                    <span class="chip"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>No deletion</span>
                    <span class="chip"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>Zero API fees</span>
                    <span class="chip"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>Unlimited msgs</span>
                    <span class="chip"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>Pre-built memory</span>
                    <span class="chip"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>Anti-ban safety</span>
                </div>

                <!-- CTAs -->
                <div class="hero-ctas">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary">Open My Dashboard <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary">Connect WhatsApp Free <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
                        <a href="#how-it-works" class="btn-ghost"><svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg> See how it works</a>
                    @endauth
                </div>

                <!-- Social proof -->
                <div class="proof-row">
                    <div class="proof-avs">
                        <div class="pav" style="background:linear-gradient(135deg,#f59e0b,#ef4444)">R</div>
                        <div class="pav" style="background:linear-gradient(135deg,#3b82f6,#8b5cf6)">S</div>
                        <div class="pav" style="background:linear-gradient(135deg,#059669,#10b981)">A</div>
                        <div class="pav" style="background:linear-gradient(135deg,#ec4899,#f43f5e)">M</div>
                    </div>
                    <span>500+ businesses on autopilot</span>
                    <span style="color:#F59E0B;letter-spacing:1px;">★★★★★</span>
                </div>

                <!-- Mini stats bar inside hero -->
                <div class="hero-stats">
                    <div class="hstat">
                        <div class="hstat-icon"><svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
                        <div><div class="hstat-num">2 min</div><div class="hstat-lbl">To go live</div></div>
                    </div>
                    <div class="hstat">
                        <div class="hstat-icon"><svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg></div>
                        <div><div class="hstat-num">∞</div><div class="hstat-lbl">Messages/mo</div></div>
                    </div>
                    <div class="hstat">
                        <div class="hstat-icon"><svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                        <div><div class="hstat-num">99.9%</div><div class="hstat-lbl">Uptime SLA</div></div>
                    </div>
                    <div class="hstat">
                        <div class="hstat-icon"><svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
                        <div><div class="hstat-num">500+</div><div class="hstat-lbl">Businesses</div></div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN — Phone Mockup --}}
            <div class="hero-right reveal-right d2">
                <div class="phone-wrap">
                    <!-- floating speed badge -->
                    <div class="fbadge glass fb-speed">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        <span style="color:var(--brand2);">0.8s</span> avg reply
                    </div>

                    <!-- phone shell -->
                    <div class="phone-shell">
                        <div class="phone-notch"></div>

                        <!-- status bar -->
                        <div class="phone-status">
                            <!-- Spice Garden Logo -->
                            <div class="phone-av" style="background:linear-gradient(135deg,#ff6b35,#f7931e);">
                                <svg width="18" height="18" fill="white" viewBox="0 0 24 24">
                                    <path d="M12 2C8 2 5 5 5 9c0 2.4 1.1 4.6 2.8 6l.7 4h7l.7-4A7 7 0 0019 9c0-4-3-7-7-7z" opacity=".3"/>
                                    <path d="M12 3C9.2 3 7 5.2 7 8c0 1.8.9 3.4 2.3 4.4l.5 2.6h4.4l.5-2.6A5.2 5.2 0 0017 8c0-2.8-2.2-5-5-5z"/>
                                    <path d="M9.5 15h5l-.5 3h-4l-.5-3z"/>
                                    <path d="M10 18h4v1h-4z"/>
                                </svg>
                            </div>
                            <div class="phone-info">
                                <div class="phone-name">Spice Garden</div>
                                <div class="online">Online · AI Active</div>
                            </div>
                            <div class="phone-actions">
                                <div class="phone-action-btn"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="var(--text3)" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg></div>
                                <div class="phone-action-btn"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="var(--text3)" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg></div>
                            </div>
                        </div>

                        <!-- chat messages — 8 conversations -->
                        <div class="chat-area" id="chat-area" style="overflow:hidden;">
                            <!-- Convo 1: Timings -->
                            <div class="cmsg user" id="c1u" style="opacity:0;transition:opacity .35s">
                                <div class="cbubble">
                                    <div class="c-text">Hi! What are your timings?</div>
                                    <div class="c-meta-user">
                                        <span>9:41 AM</span>
                                        <span style="display:flex;align-items:center;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;"><path d="M5 13l4 4L19 7"/></svg>
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;margin-left:-6px;"><path d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="c1t" style="opacity:0;transition:opacity .35s;margin-top:6px"><div class="typing"><div class="tdot"></div><div class="tdot"></div><div class="tdot"></div></div></div>
                            <div class="cmsg bot" id="c1b" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Hello! Spice Garden is open Mon–Sat 11am–11pm & Sun 12pm–10pm. Dine-in, takeaway & delivery all available!</div>
                                    <div class="c-meta-bot">Spice Garden · 9:41 AM</div>
                                </div>
                            </div>

                            <!-- Convo 2: Home delivery -->
                            <div class="cmsg user" id="c2u" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Do you do home delivery?</div>
                                    <div class="c-meta-user">
                                        <span>9:42 AM</span>
                                        <span style="display:flex;align-items:center;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;"><path d="M5 13l4 4L19 7"/></svg>
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;margin-left:-6px;"><path d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="c2t" style="opacity:0;transition:opacity .35s;margin-top:6px"><div class="typing"><div class="tdot"></div><div class="tdot"></div><div class="tdot"></div></div></div>
                            <div class="cmsg bot" id="c2b" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Yes! Free delivery within 5km on orders above ₹399. Delivered hot in 30–45 mins.</div>
                                    <div class="c-meta-bot">Spice Garden · 9:42 AM</div>
                                </div>
                            </div>

                            <!-- Convo 3: Table booking -->
                            <div class="cmsg user" id="c3u" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Can I book a table for 4 tonight?</div>
                                    <div class="c-meta-user">
                                        <span>9:43 AM</span>
                                        <span style="display:flex;align-items:center;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;"><path d="M5 13l4 4L19 7"/></svg>
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;margin-left:-6px;"><path d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="c3t" style="opacity:0;transition:opacity .35s;margin-top:6px"><div class="typing"><div class="tdot"></div><div class="tdot"></div><div class="tdot"></div></div></div>
                            <div class="cmsg bot" id="c3b" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Absolutely! Table for 4 confirmed at 7:30 PM tonight. Name & contact please?</div>
                                    <div class="c-meta-bot">Spice Garden · 9:43 AM</div>
                                </div>
                            </div>

                            <!-- Convo 4: Menu -->
                            <div class="cmsg user" id="c4u" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">What's your best dish?</div>
                                    <div class="c-meta-user">
                                        <span>9:44 AM</span>
                                        <span style="display:flex;align-items:center;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;"><path d="M5 13l4 4L19 7"/></svg>
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;margin-left:-6px;"><path d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="c4t" style="opacity:0;transition:opacity .35s;margin-top:6px"><div class="typing"><div class="tdot"></div><div class="tdot"></div><div class="tdot"></div></div></div>
                            <div class="cmsg bot" id="c4b" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Our Chef's Special Butter Chicken (₹320) and Paneer Tikka Platter (₹280) are customer favourites!</div>
                                    <div class="c-meta-bot">Spice Garden · 9:44 AM</div>
                                </div>
                            </div>

                            <!-- Convo 5: Veg options -->
                            <div class="cmsg user" id="c5u" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Do you have pure veg options?</div>
                                    <div class="c-meta-user">
                                        <span>9:45 AM</span>
                                        <span style="display:flex;align-items:center;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;"><path d="M5 13l4 4L19 7"/></svg>
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;margin-left:-6px;"><path d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="c5t" style="opacity:0;transition:opacity .35s;margin-top:6px"><div class="typing"><div class="tdot"></div><div class="tdot"></div><div class="tdot"></div></div></div>
                            <div class="cmsg bot" id="c5b" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">We have 40+ pure veg dishes! Dal Makhani, Paneer dishes, Veg Biryani, and a dedicated Jain menu too.</div>
                                    <div class="c-meta-bot">Spice Garden · 9:45 AM</div>
                                </div>
                            </div>

                            <!-- Convo 6: Payment -->
                            <div class="cmsg user" id="c6u" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Do you accept UPI?</div>
                                    <div class="c-meta-user">
                                        <span>9:46 AM</span>
                                        <span style="display:flex;align-items:center;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;"><path d="M5 13l4 4L19 7"/></svg>
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;margin-left:-6px;"><path d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="c6t" style="opacity:0;transition:opacity .35s;margin-top:6px"><div class="typing"><div class="tdot"></div><div class="tdot"></div><div class="tdot"></div></div></div>
                            <div class="cmsg bot" id="c6b" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Yes! UPI, cards, cash & wallets all accepted. Online orders get 10% cashback too!</div>
                                    <div class="c-meta-bot">Spice Garden · 9:46 AM</div>
                                </div>
                            </div>

                            <!-- Convo 7: Complaint -->
                            <div class="cmsg user" id="c7u" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">My order is taking too long...</div>
                                    <div class="c-meta-user">
                                        <span>9:47 AM</span>
                                        <span style="display:flex;align-items:center;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;"><path d="M5 13l4 4L19 7"/></svg>
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;margin-left:-6px;"><path d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="c7t" style="opacity:0;transition:opacity .35s;margin-top:6px"><div class="typing"><div class="tdot"></div><div class="tdot"></div><div class="tdot"></div></div></div>
                            <div class="cmsg bot" id="c7b" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">So sorry for the wait! I'm flagging this to our manager right now. Your order will arrive in 10 mins. We'll add a complimentary dessert!</div>
                                    <div class="c-meta-bot">Spice Garden · 9:47 AM</div>
                                </div>
                            </div>

                            <!-- Convo 8: Catering -->
                            <div class="cmsg user" id="c8u" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Do you offer catering for events?</div>
                                    <div class="c-meta-user">
                                        <span>9:48 AM</span>
                                        <span style="display:flex;align-items:center;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;"><path d="M5 13l4 4L19 7"/></svg>
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:block;margin-left:-6px;"><path d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="c8t" style="opacity:0;transition:opacity .35s;margin-top:6px"><div class="typing"><div class="tdot"></div><div class="tdot"></div><div class="tdot"></div></div></div>
                            <div class="cmsg bot" id="c8b" style="opacity:0;transition:opacity .35s;margin-top:6px">
                                <div class="cbubble">
                                    <div class="c-text">Yes! We cater weddings, corporate events & parties from 20 to 500 guests. Share your event date and we'll send a custom quote!</div>
                                    <div class="c-meta-bot">Spice Garden · 9:48 AM</div>
                                </div>
                            </div>
                        </div>

                        <!-- input bar -->
                        <div style="display:flex;align-items:center;gap:8px;padding-top:10px;margin-top:8px;border-top:1px solid #e2e8f0;">
                            <div style="flex:1;background:#f1f5f9;border:1px solid rgba(0,0,0,0.08);border-radius:20px;padding:7px 12px;font-size:11px;color:#94a3b8;">Type a message...</div>
                            <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 10px rgba(16,185,129,0.35);">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4 20-7z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- floating AI badge -->
                    <div class="fbadge glass fb-ai">
                        <div class="fb-dot"></div>
                        <span>GPT-4o Powered</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ STATS BAR ══ -->
<div class="stats-bar">
    <div class="mtrack">
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><div><div class="snum">2 min</div><div class="slbl">To go live</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg></div><div><div class="snum">Unlimited</div><div class="slbl">Messages/month</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></div><div><div class="snum">Zero</div><div class="slbl">API fees</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div><div class="snum">99.9%</div><div class="slbl">Uptime</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M9 11V7a3 3 0 016 0v4"/><circle cx="12" cy="16" r="1" fill="#10b981"/></svg></div><div><div class="snum">24/7</div><div class="slbl">Always online</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div><div><div class="snum">500+</div><div class="slbl">Businesses</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><div><div class="snum">2 min</div><div class="slbl">To go live</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg></div><div><div class="snum">Unlimited</div><div class="slbl">Messages/month</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></div><div><div class="snum">Zero</div><div class="slbl">API fees</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div><div class="snum">99.9%</div><div class="slbl">Uptime</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M9 11V7a3 3 0 016 0v4"/><circle cx="12" cy="16" r="1" fill="#10b981"/></svg></div><div><div class="snum">24/7</div><div class="slbl">Always online</div></div></div>
        <div class="sitem"><div class="sicon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div><div><div class="snum">500+</div><div class="slbl">Businesses</div></div></div>
    </div>
</div>

<!-- ══ HOW IT WORKS (STEPS) ══ -->
<section class="steps-sec reveal" id="how-it-works">
    <div class="container">
        <div style="text-align:center;margin-bottom:56px;">
            <span class="pill"><span class="dot"></span>How It Works</span>
            <h2 style="font-size:clamp(28px,3vw,38px);font-weight:800;margin-top:14px;color:var(--text);">Live in 3 simple steps</h2>
            <p style="color:var(--text3);margin-top:10px;font-size:15px;">Setting up iChatUp takes less than 2 minutes. No coding required.</p>
        </div>
        <div class="steps-grid">
            <div class="steps-list">
                <div class="step-item active" onclick="showStep(1)">
                    <div class="step-num">Step 01</div>
                    <div class="step-title">Create your workspace</div>
                    <div class="step-desc">Sign up with your email or Google account in 1 click. No credit card required.</div>
                    <div class="step-time"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Takes 10s</div>
                </div>
                <div class="step-item" onclick="showStep(2)">
                    <div class="step-num">Step 02</div>
                    <div class="step-title">Scan the QR code</div>
                    <div class="step-desc">Scan the secure QR code on your dashboard using your phone's WhatsApp. Just like WhatsApp Web.</div>
                    <div class="step-time"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Takes 20s</div>
                </div>
                <div class="step-item" onclick="showStep(3)">
                    <div class="step-num">Step 03</div>
                    <div class="step-title">AI assistant goes live!</div>
                    <div class="step-desc">Your AI assistant instantly begins answering customer inquiries 24/7. Adjust settings anytime.</div>
                    <div class="step-time"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg> Instant</div>
                </div>
            </div>
            <div class="demo-box">
                <!-- Panel 1: Signup -->
                <div class="demo-panel active" id="dp1">
                    <div style="text-align:center;width:100%;">
                        <div style="width:56px;height:56px;border-radius:16px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></div>
                        <div style="font-weight:700;color:var(--text);font-size:18px;margin-bottom:8px;">Workspace Registration</div>
                        <p style="color:var(--text3);font-size:13px;max-width:280px;margin:0 auto 20px;">Fill in your details and start your free trial instantly.</p>
                        <div style="background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:16px;text-align:left;max-width:320px;margin:0 auto;">
                            <div style="height:10px;background:var(--border);border-radius:5px;width:40%;margin-bottom:12px;"></div>
                            <div style="height:32px;background:var(--bg);border:1px solid var(--border);border-radius:8px;margin-bottom:12px;display:flex;align-items:center;padding-left:10px;"><span style="color:var(--brand2);font-size:11px;">rahil@example.com</span></div>
                            <div style="height:32px;background:var(--brand);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;">Create Free Account</div>
                        </div>
                    </div>
                </div>
                <!-- Panel 2: QR -->
                <div class="demo-panel" id="dp2">
                    <div style="text-align:center;width:100%;">
                        <div style="width:56px;height:56px;border-radius:16px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/><path d="M8 6h8"/></svg></div>
                        <div style="font-weight:700;color:var(--text);font-size:18px;margin-bottom:8px;">Link Your Device</div>
                        <p style="color:var(--text3);font-size:13px;max-width:280px;margin:0 auto 20px;">Scan the QR code with WhatsApp's "Linked Devices".</p>
                        <div style="background:white;padding:12px;border-radius:16px;width:160px;height:160px;margin:0 auto;box-shadow:0 8px 24px rgba(0,0,0,0.05);display:flex;align-items:center;justify-content:center;position:relative;">
                            <svg width="130" height="130" viewBox="0 0 29 29" fill="black">
                                <path d="M0 0h7v7H0zm1 1v5h5V1zm8 0h3v1H9zm4 0h1v3h-1zm2 0h2v1h-2zm3 0h1v1h-1zm2 0h5v7h-5zm1 1v5h3V2zm-9 1h1v1H9zm1 1h2v1h-2zm-2 2h1v1H8zm1 0h1v1H9zm2 0h1v1h-1zm4 0h1v2h-1zm1 1h2v1h-2zm-7 1h1v1H8zm3 0h1v1h-1zm2 0h1v1h-1zm5 0h1v1h-1zm0 2h1v1h-1zm-9 1h1v3H8zm2 0h1v1h-1zm2 0h2v1h-2zm4 0h1v1h-1zm2 0h5v7h-5zm1 1v5h3V16zm-9 1h1v1H9zm1 1h2v1h-2zm-7 2h7v7H0zm1 1v5h5V23zm8 0h1v2H8zm2 0h2v1h-2zm3 0h1v1h-1zm4 0h1v1h-1zm-6 2h1v1H9zm3 0h1v2h-1zm2 0h1v1h-1zm1 1h2v1h-2z" />
                            </svg>
                            <div style="position:absolute;width:34px;height:34px;border-radius:50%;background:#10b981;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(16,185,129,0.3);"><svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></div>
                        </div>
                    </div>
                </div>
                <!-- Panel 3: Live -->
                <div class="demo-panel" id="dp3">
                    <div style="text-align:center;width:100%;">
                        <div style="width:56px;height:56px;border-radius:16px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;"><svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4 20-7z"/></svg></div>
                        <div style="font-weight:700;color:var(--text);font-size:18px;margin-bottom:8px;">AI Live & Responding</div>
                        <p style="color:var(--text3);font-size:13px;max-width:280px;margin:0 auto 16px;">AI is now replying automatically to all incoming messages.</p>
                        <div style="background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:12px;max-width:320px;margin:0 auto;text-align:left;">
                            <div style="font-size:11px;color:var(--text3);margin-bottom:6px;">Current Session Status</div>
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                                <div style="display:flex;align-items:center;gap:6px;"><span style="width:8px;height:8px;background:var(--brand2);border-radius:50%;display:inline-block;animation:pdot 1.5s infinite;"></span><span style="font-size:13px;font-weight:700;color:var(--text);">Connected</span></div>
                                <span style="font-size:11px;background:var(--glow);color:var(--brand2);padding:2px 8px;border-radius:100px;margin-left:auto;font-weight:600;">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <p style="text-align:center;margin-top:40px;color:var(--text3);font-size:14px;font-weight:500;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:5px"><path d="M5 12h14M12 5l7 7-7 7"/></svg> That's it. <span style="color:var(--text);font-weight:700;">No code. No API keys. No WhatsApp deletion.</span>
        </p>
    </div>
</section>

<!-- ══ FEATURES BENTO GRID ══ -->
<section class="feat-sec reveal" id="features">
    <div class="container">
        <div style="text-align:center;margin-bottom:56px;">
            <span class="pill"><span class="dot"></span>Powerful Features</span>
            <h2 style="font-size:clamp(28px,3vw,38px);font-weight:800;margin-top:14px;color:var(--text);">Everything you need to automate support</h2>
            <p style="color:var(--text3);margin-top:10px;font-size:15px;">Reseller-ready, secure, and built for businesses of all sizes.</p>
        </div>
        <div class="bento">
            <!-- Card 1: AI Auto-Replies (Wide) -->
            <div class="bcard wide">
                <div class="bicon"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M9 11V7a3 3 0 016 0v4"/><circle cx="12" cy="16" r="1" fill="#10b981"/><path d="M8 11V8"/><path d="M16 11V8"/></svg></div>
                <h3 class="btitle">AI Auto-Replies (GPT-4o)</h3>
                <p class="bdesc">iChatUp uses advanced GPT-4o models trained on your specific business rules to handle general customer inquiries, FAQs, and bookings flawlessly — with a human-like 3 to 15s delayed reply pattern that prevents bans.</p>
            </div>
            <!-- Card 2: Pre-Built Memory -->
            <div class="bcard">
                <div class="bicon"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><path d="M9.5 2A2.5 2.5 0 0112 4.5v15a2.5 2.5 0 01-4.96-.44 2.5 2.5 0 01-2.96-3.08 3 3 0 01-.34-5.58 2.5 2.5 0 013.32-3.97A2.5 2.5 0 019.5 2z"/><path d="M14.5 2A2.5 2.5 0 0112 4.5v15a2.5 2.5 0 004.96-.44 2.5 2.5 0 002.96-3.08 3 3 0 00.34-5.58 2.5 2.5 0 00-3.32-3.97A2.5 2.5 0 0014.5 2z"/></svg></div>
                <h3 class="btitle">Pre-Built Business Memory</h3>
                <p class="bdesc">No complex prompt engineering required. Simply fill out simple tabs for your Menu, Services, Pricing, and FAQs. The AI learns your business automatically.</p>
            </div>
            <!-- Card 3: Unlimited Messages -->
            <div class="bcard">
                <div class="bicon"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg></div>
                <h3 class="btitle">Unlimited Messages</h3>
                <p class="bdesc">We don't charge per message. Send as many messages as your business needs without worrying about escalating bills at the end of the month.</p>
            </div>
            <!-- Card 4: No API Needed (Wide) -->
            <div class="bcard wide">
                <div class="bicon"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></div>
                <h3 class="btitle">No Official API Needed</h3>
                <p class="bdesc" style="margin-bottom:14px;">Forget about complex developer account applications, expensive setup fees, and waiting weeks for Meta's approval. iChatUp connects directly via WhatsApp Web socket.</p>
                <table class="vs-tbl">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th>Official API</th>
                            <th style="color:var(--brand2);">iChatUp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Setup Time</td>
                            <td>3–10 Days</td>
                            <td class="yes">2 Minutes</td>
                        </tr>
                        <tr>
                            <td>Per-Message Fees</td>
                            <td class="no">Yes (Expensive)</td>
                            <td class="yes">Zero ($0)</td>
                        </tr>
                        <tr>
                            <td>Number Deletion Required</td>
                            <td class="no">Yes</td>
                            <td class="yes">No</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Card 5: Anti-Ban Protection -->
            <div class="bcard">
                <div class="bicon"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                <h3 class="btitle">Anti-Ban Protection</h3>
                <p class="bdesc">Built-in smart safety filters, randomized reply delays, and natural message pacing keep your WhatsApp account safe and fully compliant with anti-spam boundaries.</p>
            </div>
            <!-- Card 6: Live Dashboard -->
            <div class="bcard">
                <div class="bicon"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></div>
                <h3 class="btitle">Live Chat Logs & Takeover</h3>
                <p class="bdesc">Monitor active conversations in real-time. If a customer needs human help, flag the conversation and take over manually with a single click from your browser dashboard.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══ TESTIMONIALS (REVIEWS) ══ -->
<section class="testi-sec reveal" id="testimonials">
    <div class="container" style="text-align:center;">
        <span class="pill"><span class="dot"></span>Reviews</span>
        <h2 style="font-size:clamp(28px,3vw,38px);font-weight:800;margin-top:14px;color:var(--text);">Loved by 500+ businesses</h2>
    </div>
    <div class="ttrack">
        <!-- Review 1 -->
        <div class="tcard">
            <div class="tstars">★★★★★</div>
            <p class="ttext">"iChatUp has completely changed how we handle reservations. We get bookings 24/7 even while we're closed."</p>
            <div class="tauthor">
                <div class="tav" style="background:#059669;">R</div>
                <div>
                    <div class="tname">Rohan Sharma</div>
                    <div class="tbiz">Restaurant Owner</div>
                </div>
            </div>
        </div>
        <!-- Review 2 -->
        <div class="tcard">
            <div class="tstars">★★★★★</div>
            <p class="ttext">"Zero message limits and zero API fees. It paid for itself in the first 24 hours of setup. Super simple to train."</p>
            <div class="tauthor">
                <div class="tav" style="background:#2563eb;">A</div>
                <div>
                    <div class="tname">Ananya Gupta</div>
                    <div class="tbiz">SaaS Reseller</div>
                </div>
            </div>
        </div>
        <!-- Review 3 -->
        <div class="tcard">
            <div class="tstars">★★★★★</div>
            <p class="ttext">"The human takeover feature is wonderful. We watch the AI talk, and step in only if they want a custom quote."</p>
            <div class="tauthor">
                <div class="tav" style="background:#7c3aed;">M</div>
                <div>
                    <div class="tname">Meera Patel</div>
                    <div class="tbiz">Clinic Administrator</div>
                </div>
            </div>
        </div>
        <!-- Review 4 -->
        <div class="tcard">
            <div class="tstars">★★★★★</div>
            <p class="ttext">"We went from spending hours answering FAQs to zero. The AI answers everything about delivery and hours perfectly."</p>
            <div class="tauthor">
                <div class="tav" style="background:#db2777;">K</div>
                <div>
                    <div class="tname">Karan Johar</div>
                    <div class="tbiz">E-commerce Shop</div>
                </div>
            </div>
        </div>
        <!-- Review 5 -->
        <div class="tcard">
            <div class="tstars">★★★★★</div>
            <p class="ttext">"iChatUp has completely changed how we handle reservations. We get bookings 24/7 even while we're closed."</p>
            <div class="tauthor">
                <div class="tav" style="background:#059669;">R</div>
                <div>
                    <div class="tname">Rohan Sharma</div>
                    <div class="tbiz">Restaurant Owner</div>
                </div>
            </div>
        </div>
        <!-- Review 6 -->
        <div class="tcard">
            <div class="tstars">★★★★★</div>
            <p class="ttext">"Zero message limits and zero API fees. It paid for itself in the first 24 hours of setup. Super simple to train."</p>
            <div class="tauthor">
                <div class="tav" style="background:#2563eb;">A</div>
                <div>
                    <div class="tname">Ananya Gupta</div>
                    <div class="tbiz">SaaS Reseller</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ PRICING ══ -->
<section class="price-sec reveal" id="pricing">
    <div class="container">
        <div style="text-align:center;margin-bottom:28px;">
            <span class="pill"><span class="dot"></span>Flexible Pricing</span>
            <h2 style="font-size:clamp(28px,3vw,38px);font-weight:800;margin-top:14px;color:var(--text);">Choose the plan that fits you</h2>
            <p style="color:var(--text3);margin-top:10px;font-size:15px;">All plans include our 2-minute instant setup & unlimited messages.</p>
        </div>
        <div class="ptoggle">
            <span class="plbl active" id="lbl-monthly">Monthly Billing</span>
            <div class="pswitch" id="billing-switch"></div>
            <span class="plbl" id="lbl-yearly">Yearly Billing</span>
            <span class="dbadge">Save 20%</span>
        </div>
        <div class="pgrid">
            <!-- Plan 1: Starter -->
            <div class="pcard">
                <div class="pname">Starter</div>
                <div class="pprice" id="p1-price">₹999<span>/mo</span></div>
                <div class="ptagline">Perfect for small local businesses starting with AI automation.</div>
                <div class="pdiv"></div>
                <ul class="pfeats">
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>1 WhatsApp Number</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Unlimited Messages</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Pre-built Business Memory</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>GPT-4o Powered</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Live Chat Dashboard</li>
                    <li style="opacity:0.5;"><span class="pcx"><svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>Premium Fast API</li>
                    <li style="opacity:0.5;"><span class="pcx"><svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>White-label & CNAME</li>
                </ul>
                @auth
                    <a href="{{ url('/dashboard') }}" class="pbtn out">Get Started Free</a>
                @else
                    <a href="{{ route('register') }}" class="pbtn out">Get Started Free</a>
                @endauth
            </div>
            <!-- Plan 2: Growth (Popular) -->
            <div class="pcard pop">
                <div class="popbadge">Most Popular</div>
                <div class="pname">Growth</div>
                <div class="pprice" id="p2-price">₹2,499<span>/mo</span></div>
                <div class="ptagline">Best for growing shops, agencies, and clinics.</div>
                <div class="pdiv"></div>
                <ul class="pfeats">
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>3 WhatsApp Numbers</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Unlimited Messages</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Custom Training Prompt</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Premium Fast API</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Premium Live Support</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>AI Human Handover</li>
                    <li style="opacity:0.5;"><span class="pcx"><svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>White-label & CNAME</li>
                </ul>
                @auth
                    <a href="{{ url('/dashboard') }}" class="pbtn sol">Upgrade Workspace</a>
                @else
                    <a href="{{ route('register') }}" class="pbtn sol">Upgrade Workspace</a>
                @endauth
            </div>
            <!-- Plan 3: Scale -->
            <div class="pcard">
                <div class="pname">Scale</div>
                <div class="pprice" id="p3-price">₹7,999<span>/mo</span></div>
                <div class="ptagline">For agencies and resellers wanting custom white-label branding.</div>
                <div class="pdiv"></div>
                <ul class="pfeats">
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>10 WhatsApp Numbers</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Unlimited Messages</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>All Models Access</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Full Reseller Control</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>White-label Branding</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Custom CNAME Domain</li>
                    <li><span class="pck"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>Dedicated Account Manager</li>
                </ul>
                @auth
                    <a href="{{ url('/dashboard') }}" class="pbtn out">Connect Workspace</a>
                @else
                    <a href="{{ route('register') }}" class="pbtn out">Connect Workspace</a>
                @endauth
            </div>
        </div>
    </div>
</section>

<!-- ══ FAQ ══ -->
<section class="faq-sec reveal" id="faq">
    <div class="container">
        <div style="text-align:center;margin-bottom:28px;">
            <span class="pill"><span class="dot"></span>F.A.Q</span>
            <h2 style="font-size:clamp(28px,3vw,38px);font-weight:800;margin-top:14px;color:var(--text);">Frequently Asked Questions</h2>
            <p style="color:var(--text3);margin-top:10px;font-size:15px;">Everything you need to know about iChatUp.</p>
        </div>
        <div class="faq-list">
            <!-- FAQ 1 -->
            <div class="fitem">
                <div class="fq" onclick="toggleFaq(this)">Do I need to delete my existing WhatsApp? <span class="ficon">+</span></div>
                <div class="fa">No! You do not need to delete anything. iChatUp connects directly to your existing WhatsApp number (Personal or Business) through a simple secure QR code scan, similar to WhatsApp Web. Your conversations stay intact.</div>
            </div>
            <!-- FAQ 2 -->
            <div class="fitem">
                <div class="fq" onclick="toggleFaq(this)">Will my WhatsApp number get banned? <span class="ficon">+</span></div>
                <div class="fa">We take safety very seriously. iChatUp has built-in anti-ban filters, including randomized typing delays and message intervals that closely mimic natural human chat patterns, keeping your account fully safe.</div>
            </div>
            <!-- FAQ 3 -->
            <div class="fitem">
                <div class="fq" onclick="toggleFaq(this)">How is this different from WhatsApp Business API? <span class="ficon">+</span></div>
                <div class="fa">The official API requires expensive setup costs, Meta account approvals, and charges you for every single message conversation. iChatUp connects in 2 minutes with zero setup fees and lets you send unlimited messages under a simple flat subscription fee.</div>
            </div>
            <!-- FAQ 4 -->
            <div class="fitem">
                <div class="fq" onclick="toggleFaq(this)">How do I train the AI assistant? <span class="ficon">+</span></div>
                <div class="fa">No code is required. Our pre-built business memory structure gives you clean tabs to add your Menu, Services, Pricing, and common FAQs. The AI parses this information and answers customers automatically.</div>
            </div>
            <!-- FAQ 5 -->
            <div class="fitem">
                <div class="fq" onclick="toggleFaq(this)">Can I take over conversations manually? <span class="ficon">+</span></div>
                <div class="fa">Absolutely. Our dashboard features a real-time chat log stream. With a single click, you can flag any conversation for human takeover, which pauses the AI immediately and lets you chat manually from your browser.</div>
            </div>
        </div>
    </div>
</section>

<!-- ══ FINAL CTA ══ -->
<section class="cta-sec reveal">
    <div class="container">
        <div class="cta-box">
            <h2 class="cta-title">Ready to put your WhatsApp on autopilot?</h2>
            <p class="cta-sub">Join hundreds of smart business owners saving hours of manual replies daily. Start free today.</p>
            <div class="cta-pulse">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary" style="font-size:16px;padding:16px 36px;">Open My Dashboard →</a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary" style="font-size:16px;padding:16px 36px;">Connect WhatsApp Free →</a>
                @endauth
            </div>
        </div>
    </div>
</section>

<!-- ══ FOOTER ══ -->
<footer class="footer">
    <div class="container">
        <div class="fg">
            <div class="fbrand">
                <div class="logo">
                    <div class="logo-icon">
                        <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    </div>
                    <span class="logo-text">iChatUp</span>
                </div>
                <p>Advanced AI automation for WhatsApp. Connect in 2 minutes, automate 80% of support conversations.</p>
            </div>
            <div class="fcol">
                <h4>Product</h4>
                <ul>
                    <li><a href="#how-it-works">How it works</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#faq">FAQ</a></li>
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
            <span>© {{ date('Y') }} iChatUp. All rights reserved locally.</span>
            <span style="display:flex;align-items:center;gap:5px;">Made with <svg width="14" height="14" fill="#ef4444" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg> for modern businesses.</span>
        </div>
    </div>
</footer>

<!-- ══ INTERACTIVE SCRIPTS ══ -->
<script>
    // ── THEME SWITCHER ──
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

    // ── NAVBAR SCROLL GLOW ──
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // ── CHAT DEMO — 8 Conversations Cycling ──
    function startChatDemo() {
        const convos = [
            { u:'c1u', t:'c1t', b:'c1b' },
            { u:'c2u', t:'c2t', b:'c2b' },
            { u:'c3u', t:'c3t', b:'c3b' },
            { u:'c4u', t:'c4t', b:'c4b' },
            { u:'c5u', t:'c5t', b:'c5b' },
            { u:'c6u', t:'c6t', b:'c6b' },
            { u:'c7u', t:'c7t', b:'c7b' },
            { u:'c8u', t:'c8t', b:'c8b' },
        ];

        // Preload all DOM refs
        const els = convos.map(c => ({
            u: document.getElementById(c.u),
            t: document.getElementById(c.t),
            b: document.getElementById(c.b),
        }));

        const chatArea = document.getElementById('chat-area');
        let current = 0;
        let timers = [];

        function clearTimers() { timers.forEach(clearTimeout); timers = []; }

        function hideAll() {
            els.forEach(e => {
                if (e.u) { e.u.style.display = 'none'; e.u.style.opacity = 0; e.u.style.transform = 'translateY(8px)'; }
                if (e.t) { e.t.style.display = 'none'; e.t.style.opacity = 0; e.t.style.transform = 'translateY(8px)'; }
                if (e.b) { e.b.style.display = 'none'; e.b.style.opacity = 0; e.b.style.transform = 'translateY(8px)'; }
            });
        }

        function showElement(el, displayStyle = 'flex') {
            if (!el) return;
            el.style.display = displayStyle;
            // trigger reflow
            el.offsetHeight;
            el.style.opacity = 1;
            el.style.transform = 'translateY(0)';
            if (chatArea) {
                chatArea.scrollTop = chatArea.scrollHeight;
            }
        }

        function hideElement(el) {
            if (!el) return;
            el.style.opacity = 0;
            el.style.transform = 'translateY(8px)';
            timers.push(setTimeout(() => {
                el.style.display = 'none';
            }, 300));
        }

        function playConvo(idx) {
            const e = els[idx];
            if (!e) return;

            // Show user message
            timers.push(setTimeout(() => {
                showElement(e.u, 'flex');
            }, 300));

            // Show typing indicator
            timers.push(setTimeout(() => {
                showElement(e.t, 'block');
            }, 1300));

            // Show bot reply, hide typing
            timers.push(setTimeout(() => {
                hideElement(e.t);
                showElement(e.b, 'flex');
            }, 2700));
        }

        function runNextConvoStep() {
            if (current === 0) {
                clearTimers();
                hideAll();
                if (chatArea) chatArea.scrollTop = 0;
            }

            playConvo(current);

            // Schedule next convo step
            timers.push(setTimeout(() => {
                current = current + 1;
                if (current < convos.length) {
                    runNextConvoStep();
                } else {
                    // Loop back to start after showing all messages
                    timers.push(setTimeout(() => {
                        current = 0;
                        runNextConvoStep();
                    }, 5000));
                }
            }, 5500));
        }

        runNextConvoStep();
    }

    startChatDemo();

    // ── HOW IT WORKS STEPS TOGGLE ──
    window.showStep = function(stepNum) {
        const steps = document.querySelectorAll('.step-item');
        const panels = document.querySelectorAll('.demo-panel');
        
        steps.forEach((s, idx) => {
            if (idx + 1 === stepNum) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });

        panels.forEach((p, idx) => {
            if (idx + 1 === stepNum) {
                p.classList.add('active');
            } else {
                p.classList.remove('active');
            }
        });
    }

    // ── PRICING ANNUAL/MONTHLY TOGGLE ──
    const bswitch = document.getElementById('billing-switch');
    const lblM = document.getElementById('lbl-monthly');
    const lblY = document.getElementById('lbl-yearly');
    const p1 = document.getElementById('p1-price');
    const p2 = document.getElementById('p2-price');
    const p3 = document.getElementById('p3-price');

    let isAnnual = false;

    bswitch.addEventListener('click', () => {
        isAnnual = !isAnnual;
        bswitch.classList.toggle('on');
        lblM.classList.toggle('active');
        lblY.classList.toggle('active');

        if (isAnnual) {
            p1.innerHTML = '₹799<span>/mo</span>';
            p2.innerHTML = '₹1,999<span>/mo</span>';
            p3.innerHTML = '₹6,399<span>/mo</span>';
        } else {
            p1.innerHTML = '₹999<span>/mo</span>';
            p2.innerHTML = '₹2,499<span>/mo</span>';
            p3.innerHTML = '₹7,999<span>/mo</span>';
        }
    });

    // ── FAQ ACCORDION TOGGLE ──
    window.toggleFaq = function(element) {
        const item = element.parentElement;
        const isOpen = item.classList.contains('open');
        
        // Close all first
        document.querySelectorAll('.fitem').forEach(i => {
            i.classList.remove('open');
            const panel = i.querySelector('.fa');
            if (panel) panel.style.maxHeight = null;
        });

        if (!isOpen) {
            item.classList.add('open');
            const panel = item.querySelector('.fa');
            if (panel) panel.style.maxHeight = panel.scrollHeight + "px";
        }
    }

    // ── SCROLL REVEAL (Intersection Observer) ──
    // Immediately show hero elements (they're in viewport on load)
    document.querySelectorAll('.hero .reveal-left, .hero .reveal-right').forEach(el => {
        el.classList.add('visible');
    });

    const reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
            }
        });
    }, { threshold: 0.05, rootMargin: '0px 0px -50px 0px' });

    reveals.forEach(r => observer.observe(r));
</script>
</body></html>
