@php
    $reseller    = app()->has('active_reseller') ? app('active_reseller') : null;
    $appName     = $reseller?->name ?? 'iChatUp';
    $brandPrimary = $reseller?->primary_color ?? '#10b981';
    $faviconUrl  = $reseller?->favicon_path ? Storage::url($reseller->favicon_path) : asset('favicon.png');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appName }} — Welcome Aboard</title>
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand: {{ $brandPrimary }};
            --brand-glow: {{ $brandPrimary }}59;
            --brand-dim: {{ $brandPrimary }}1a;
            --bg: #050b14;
            --bg2: #080f1c;
            --bg3: #0d1827;
            --surface: rgba(255,255,255,0.04);
            --surface-hover: rgba(255,255,255,0.07);
            --border: rgba(255,255,255,0.07);
            --border-strong: rgba(255,255,255,0.12);
            --text: #f1f5f9;
            --text-muted: #64748b;
            --text-soft: #94a3b8;
            --violet: #8b5cf6;
            --violet-glow: rgba(139,92,246,0.25);
        }

        html, body { height: 100%; overflow: hidden; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg); color: var(--text); }

        /* Animated bg */
        .bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4; animation: float 12s ease-in-out infinite; }
        .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(16,185,129,0.25), transparent); top: -150px; left: -150px; }
        .orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(139,92,246,0.2), transparent); bottom: -100px; right: -100px; animation-delay: -4s; }
        .orb-3 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(6,182,212,0.15), transparent); top: 50%; right: 30%; animation-delay: -8s; }
        @keyframes float { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(30px,-20px) scale(1.05)} 66%{transform:translate(-20px,15px) scale(0.97)} }

        /* Layout */
        .layout { position: relative; z-index: 1; display: grid; grid-template-columns: 1fr 1fr; height: 100vh; }

        /* LEFT PANEL */
        .left-panel {
            display: flex; flex-direction: column;
            height: 100vh; overflow: hidden;
            padding: 22px 40px 18px;
            border-right: 1px solid var(--border);
            background: linear-gradient(160deg, rgba(16,185,129,0.03) 0%, transparent 40%, rgba(139,92,246,0.02) 100%);
        }

        /* Top bar */
        .top-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-shrink: 0; }
        .logo-wrap { display: flex; align-items: center; gap: 8px; }
        .logo-wrap img { width: 32px; height: 32px; border-radius: 10px; object-fit: contain; }
        .logo-wrap span { font-size: 16px; font-weight: 800; background: linear-gradient(135deg, #10b981, #34d399); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .logout-btn { font-size: 12px; font-weight: 600; color: var(--text-muted); background: var(--surface); border: 1px solid var(--border); padding: 6px 14px; border-radius: 8px; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .logout-btn:hover { color: var(--text); background: var(--surface-hover); }

        /* Stepper */
        .stepper { display: flex; align-items: center; gap: 0; margin-bottom: 28px; flex-shrink: 0; }
        .step-item { display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .step-badge { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; transition: all 0.4s ease; }
        .step-badge.active { background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 0 20px rgba(16,185,129,0.4); }
        .step-badge.done { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .step-badge.pending { background: var(--surface); border: 1px solid var(--border); color: var(--text-muted); }
        .step-label { font-size: 10px; font-weight: 600; color: var(--text-muted); white-space: nowrap; }
        .step-label.active-label { color: var(--brand); }
        .step-connector { height: 1px; flex: 1; min-width: 20px; background: var(--border); margin-bottom: 18px; transition: background 0.4s ease; }
        .step-connector.filled { background: linear-gradient(90deg, #10b981, #059669); }

        /* Form area */
        .form-area { flex: 1; overflow: hidden; position: relative; }
        .step-section { position: absolute; inset: 0; overflow-y: auto; padding-right: 4px; transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.4,0,0.2,1); }
        .step-section::-webkit-scrollbar { width: 3px; }
        .step-section::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }
        .step-section.hidden-step { opacity: 0; transform: translateX(40px); pointer-events: none; }
        .step-section.visible-step { opacity: 1; transform: translateX(0); }

        .step-heading { font-size: 26px; font-weight: 900; color: var(--text); line-height: 1.2; margin-bottom: 5px; letter-spacing: -0.5px; }
        .step-heading .accent { background: linear-gradient(135deg, #10b981, #34d399); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .step-subtext { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; line-height: 1.5; }

        /* Type cards */
        .type-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
        .type-card { padding: 18px; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); cursor: pointer; transition: all 0.25s ease; position: relative; overflow: hidden; }
        .type-card::before { content: ''; position: absolute; inset: 0; opacity: 0; background: linear-gradient(135deg, rgba(16,185,129,0.06), transparent); transition: opacity 0.25s; }
        .type-card:hover { border-color: var(--border-strong); transform: translateY(-2px); }
        .type-card.selected { border-color: var(--brand); box-shadow: 0 0 0 1px rgba(16,185,129,0.2), inset 0 0 30px rgba(16,185,129,0.04); }
        .type-card.selected::before { opacity: 1; }
        .type-card.selected-violet { border-color: var(--violet); box-shadow: 0 0 0 1px rgba(139,92,246,0.2), inset 0 0 30px rgba(139,92,246,0.04); }
        .card-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
        .card-icon-green { background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.2); }
        .card-icon-violet { background: rgba(139,92,246,0.12); border: 1px solid rgba(139,92,246,0.2); }
        /* SVG icons for cards */
        .card-icon svg { width: 18px; height: 18px; }
        .card-title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 5px; }
        .card-desc { font-size: 11.5px; color: var(--text-muted); line-height: 1.5; }
        .check-badge { position: absolute; top: 10px; right: 10px; width: 18px; height: 18px; border-radius: 50%; background: var(--brand); display: flex; align-items: center; justify-content: center; font-size: 9px; color: white; opacity: 0; transition: opacity 0.2s; }
        .type-card.selected .check-badge, .type-card.selected-violet .check-badge { opacity: 1; background: var(--violet); }
        .type-card.selected .check-badge { background: var(--brand); }

        /* Tip box */
        .tip-box { padding: 12px 14px; background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.15); border-radius: 10px; display: flex; align-items: flex-start; gap: 10px; }
        .tip-icon { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }
        .tip-text { font-size: 12px; color: var(--text-muted); line-height: 1.6; }

        /* Form fields */
        .field-group { margin-bottom: 16px; }
        .field-label { font-size: 12px; font-weight: 600; color: var(--text-soft); margin-bottom: 6px; display: block; letter-spacing: 0.3px; }
        .glass-input { width: 100%; background: rgba(8,15,28,0.8) !important; border: 1px solid var(--border-strong) !important; color: var(--text) !important; border-radius: 10px !important; padding: 11px 13px !important; font-size: 13px !important; font-family: 'Inter', sans-serif; transition: all 0.25s; outline: none; }
        .glass-input:focus { border-color: var(--brand) !important; box-shadow: 0 0 0 3px rgba(16,185,129,0.1) !important; }
        .glass-input::placeholder { color: var(--text-muted) !important; }
        textarea.glass-input { resize: none; }

        /* Category grid */
        .cat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 7px; }
        .cat-btn { padding: 9px 7px; border-radius: 10px; border: 1px solid var(--border); background: var(--surface); font-size: 11px; font-weight: 600; color: var(--text-soft); cursor: pointer; transition: all 0.2s; text-align: left; display: flex; align-items: center; gap: 6px; }
        .cat-btn:hover { background: var(--surface-hover); border-color: var(--border-strong); }
        .cat-btn.cat-selected { border-color: var(--brand); background: rgba(16,185,129,0.08); color: var(--brand); }
        .cat-indicator { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; opacity: 0.6; }
        .cat-name { line-height: 1.3; }

        /* Subcategory pills */
        .sub-pills { display: flex; flex-wrap: wrap; gap: 6px; }
        .sub-pill { padding: 5px 11px; border-radius: 20px; border: 1px solid var(--border); background: var(--surface); font-size: 11px; color: var(--text-soft); cursor: pointer; transition: all 0.2s; }
        .sub-pill:hover { border-color: var(--border-strong); color: var(--text); }
        .sub-pill.sub-selected { border-color: var(--brand); background: rgba(16,185,129,0.1); color: var(--brand); }

        /* Personal role pills */
        .role-pills { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .role-pill { padding: 12px 14px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface); font-size: 12px; font-weight: 600; color: var(--text-soft); cursor: pointer; transition: all 0.2s; text-align: left; }
        .role-pill:hover { border-color: var(--border-strong); color: var(--text); }
        .role-pill.role-selected { border-color: var(--violet); background: rgba(139,92,246,0.08); color: #a78bfa; }
        .role-pill-title { font-size: 13px; font-weight: 700; color: inherit; margin-bottom: 3px; }
        .role-pill-desc { font-size: 11px; color: var(--text-muted); font-weight: 400; }

        /* Tone cards */
        .tone-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
        .tone-btn { padding: 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface); cursor: pointer; transition: all 0.2s; text-align: left; }
        .tone-btn:hover { border-color: var(--border-strong); }
        .tone-btn.tone-selected { border-color: var(--brand); background: rgba(16,185,129,0.08); }
        .tone-title { font-size: 12px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
        .tone-desc { font-size: 11px; color: var(--text-muted); }

        /* AI name suggestions */
        .suggestions { display: flex; gap: 7px; flex-wrap: wrap; margin-top: 8px; }
        .suggestion-chip { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid var(--border); background: var(--surface); color: var(--text-soft); cursor: pointer; transition: all 0.2s; }
        .suggestion-chip:hover { border-color: var(--brand); color: var(--brand); }

        /* Prefill link */
        .prefill-link { font-size: 11px; color: var(--brand); cursor: pointer; opacity: 0; transition: opacity 0.2s; display: inline-block; margin-top: 4px; font-weight: 600; }
        .prefill-link.show { opacity: 1; }
        .prefill-link:hover { text-decoration: underline; }

        /* Bottom nav */
        .bottom-nav { display: flex; align-items: center; justify-content: space-between; padding-top: 16px; margin-top: 4px; border-top: 1px solid var(--border); flex-shrink: 0; }
        .btn-back { font-size: 13px; font-weight: 600; color: var(--text-muted); background: none; border: none; cursor: pointer; padding: 10px 14px; border-radius: 10px; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
        .btn-back:hover { color: var(--text); background: var(--surface); }
        .btn-back.invisible { visibility: hidden; }
        .btn-skip { font-size: 13px; font-weight: 600; color: var(--text-muted); background: none; border: none; cursor: pointer; padding: 10px 14px; border-radius: 10px; transition: all 0.2s; display: none; }
        .btn-skip:hover { color: var(--text); }
        .btn-next { display: flex; align-items: center; gap: 8px; padding: 11px 22px; border-radius: 11px; background: linear-gradient(135deg, #10b981, #059669); color: white; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.25s; box-shadow: 0 8px 24px rgba(16,185,129,0.25); }
        .btn-next:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(16,185,129,0.35); }
        .btn-next:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* RIGHT PANEL */
        .right-panel { position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--bg2); }
        .right-panel::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 40px 40px; pointer-events: none; }

        .right-content { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 20px; padding: 28px; }
        .right-label { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; color: var(--text-muted); text-transform: uppercase; }

        /* Phone mockup — natural, not oversized */
        .phone-wrap { position: relative; width: 240px; height: 470px; border-radius: 38px; border: 7px solid #1a2332; background: #0b141a; overflow: hidden; box-shadow: 0 30px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04); transition: transform 0.4s ease; }
        .phone-wrap:hover { transform: scale(1.015); }
        .phone-notch { position: absolute; top: 5px; left: 50%; transform: translateX(-50%); width: 70px; height: 12px; background: #1a2332; border-radius: 20px; z-index: 30; }
        .phone-glow { position: absolute; inset: -20px; border-radius: 50px; background: radial-gradient(ellipse, rgba(16,185,129,0.08), transparent 70%); pointer-events: none; }

        /* WhatsApp chat */
        .wa-container { display: flex; flex-direction: column; height: 100%; }
        .wa-header { background: #075e54; padding: 24px 11px 9px; display: flex; align-items: center; gap: 9px; flex-shrink: 0; }
        .wa-avatar { width: 33px; height: 33px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: white; flex-shrink: 0; transition: background 0.4s; }
        .wa-info { flex: 1; min-width: 0; }
        .wa-name { font-size: 12px; font-weight: 700; color: white; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wa-status { font-size: 9.5px; color: rgba(255,255,255,0.7); }
        .wa-icons { display: flex; gap: 12px; opacity: 0.8; }
        .wa-icons svg { width: 14px; height: 14px; fill: white; }
        .wa-bg { flex: 1; padding: 10px; overflow: hidden; background: #0b141a; background-image: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23122a38' fill-opacity='0.4' fill-rule='evenodd'%3E%3Ccircle cx='1' cy='1' r='1'/%3E%3C/g%3E%3C/svg%3E"); display: flex; flex-direction: column; justify-content: flex-end; gap: 7px; }
        .wa-bubble-security { background: rgba(255,224,130,0.85); color: #5a3600; font-size: 8.5px; padding: 4px 8px; border-radius: 7px; text-align: center; align-self: center; max-width: 90%; line-height: 1.4; }
        .wa-bubble-bot { background: #202c33; color: #e9edef; font-size: 10.5px; padding: 7px 9px; border-radius: 9px 9px 9px 2px; max-width: 88%; position: relative; line-height: 1.5; }
        .wa-bubble-user { background: #005c4b; color: #e9edef; font-size: 10.5px; padding: 7px 9px; border-radius: 9px 9px 2px 9px; max-width: 88%; align-self: flex-end; line-height: 1.5; }
        .wa-time { font-size: 7.5px; color: rgba(255,255,255,0.45); text-align: right; margin-top: 2px; }
        .typing-dots { display: flex; gap: 3px; align-items: center; padding: 2px 0; }
        .typing-dots span { width: 5px; height: 5px; border-radius: 50%; background: #8696a0; animation: blink 1.4s infinite ease-in-out; }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink { 0%,80%,100%{opacity:0.3} 40%{opacity:1} }
        .wa-input-bar { background: #1f2c34; padding: 7px 9px; display: flex; align-items: center; gap: 7px; flex-shrink: 0; }
        .wa-input-fake { flex: 1; background: #2a3942; border-radius: 18px; padding: 6px 10px; font-size: 9.5px; color: #8696a0; }
        .wa-mic-icon { width: 14px; height: 14px; background: #2a3942; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        /* Stats card */
        .info-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 15px 18px; width: 100%; max-width: 260px; }
        .info-stat { display: flex; align-items: center; gap: 10px; }
        .info-stat + .info-stat { margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border); }
        .stat-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .stat-val { font-size: 14px; font-weight: 800; color: var(--text); }
        .stat-label { font-size: 10px; color: var(--text-muted); }

        /* QR panel */
        .qr-panel-wrap { display: none; flex-direction: column; align-items: center; gap: 18px; width: 100%; max-width: 300px; }
        .qr-panel-wrap.show { display: flex; }
        .qr-box { width: 190px; height: 190px; background: white; border-radius: 14px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; box-shadow: 0 0 60px rgba(16,185,129,0.2); }
        .qr-scan-line { position: absolute; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, #10b981, transparent); animation: scan 2.5s ease-in-out infinite; }
        @keyframes scan { 0%{top:0;opacity:0} 10%{opacity:1} 90%{opacity:1} 100%{top:100%;opacity:0} }
        .spinner { width: 32px; height: 32px; border: 3px solid rgba(16,185,129,0.2); border-top-color: #10b981; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .qr-status-badge { display: flex; align-items: center; gap: 7px; padding: 5px 13px; border-radius: 20px; background: var(--surface); border: 1px solid var(--border); font-size: 11px; font-weight: 600; }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; }
        .status-dot.red { background: #ef4444; box-shadow: 0 0 8px #ef4444; animation: pulse-dot 2s infinite; }
        .status-dot.yellow { background: #f59e0b; box-shadow: 0 0 8px #f59e0b; animation: pulse-dot 1.5s infinite; }
        .status-dot.green { background: #10b981; box-shadow: 0 0 8px #10b981; }
        @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:0.4} }

        .qr-steps-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 14px; width: 100%; }
        .qr-step { display: flex; gap: 9px; align-items: flex-start; margin-bottom: 9px; }
        .qr-step:last-child { margin-bottom: 0; }
        .qr-step-num { width: 18px; height: 18px; border-radius: 50%; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; color: #10b981; flex-shrink: 0; margin-top: 1px; }
        .qr-step-text { font-size: 11px; color: var(--text-muted); line-height: 1.5; }
        .qr-step-text strong { color: var(--text-soft); font-weight: 600; }

        /* Connected state */
        .connected-state { display: none; flex-direction: column; align-items: center; gap: 14px; text-align: center; }
        .connected-state.show { display: flex; }
        .connected-icon { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 40px rgba(16,185,129,0.4); }
        .connected-icon svg { width: 30px; height: 30px; stroke: white; fill: none; stroke-width: 2.5; }
        .connected-title { font-size: 18px; font-weight: 800; color: var(--text); }
        .connected-desc { font-size: 12px; color: var(--text-muted); }

        /* Toast */
        .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(100px); background: #1e293b; border: 1px solid var(--border); border-radius: 10px; padding: 11px 18px; font-size: 13px; color: var(--text); font-weight: 500; transition: transform 0.3s ease; z-index: 9999; box-shadow: 0 10px 40px rgba(0,0,0,0.4); display: flex; align-items: center; gap: 9px; }
        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast.error { border-color: rgba(239,68,68,0.3); }
        .toast.success { border-color: rgba(16,185,129,0.3); }

        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }

        @media (max-width: 1024px) {
            .layout { grid-template-columns: 1fr; }
            .right-panel { display: none; }
            .left-panel { padding: 18px 22px; }
        }
    </style>
</head>
<body>
    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="layout">
        <!-- LEFT PANEL -->
        <div class="left-panel">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="logo-wrap">
                    @if($reseller?->logo_path)
                        <img src="{{ Storage::url($reseller->logo_path) }}" alt="{{ $appName }}" />
                    @elseif($reseller)
                        <div class="w-8 h-8 rounded-[10px] flex items-center justify-center font-bold text-white text-sm flex-shrink-0"
                             style="background: linear-gradient(135deg, {{ $brandPrimary }}, #047857);">
                            {{ strtoupper(substr($appName, 0, 1)) }}
                        </div>
                    @else
                        <img src="{{ asset('ichatup_logo.png') }}" alt="iChatUp Logo" />
                    @endif
                    <span>{{ $appName }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="logout-btn">Sign Out</button>
                </form>
            </div>

            <!-- Stepper -->
            <div class="stepper">
                <div class="step-item">
                    <div class="step-badge active" id="sb1">1</div>
                    <span class="step-label active-label" id="sl1">Who are you?</span>
                </div>
                <div class="step-connector" id="sc1"></div>
                <div class="step-item">
                    <div class="step-badge pending" id="sb2">2</div>
                    <span class="step-label" id="sl2">Your Story</span>
                </div>
                <div class="step-connector" id="sc2"></div>
                <div class="step-item">
                    <div class="step-badge pending" id="sb3">3</div>
                    <span class="step-label" id="sl3">Meet Your AI</span>
                </div>
                <div class="step-connector" id="sc3"></div>
                <div class="step-item">
                    <div class="step-badge pending" id="sb4">4</div>
                    <span class="step-label" id="sl4">Go Live</span>
                </div>
            </div>

            <!-- Form -->
            <form id="obForm" style="flex:1; display:flex; flex-direction:column; overflow:hidden;">
                @csrf
                <div class="form-area">

                    <!-- STEP 1: Who are you? -->
                    <div class="step-section visible-step" id="sec1">
                        <div class="step-heading">You're almost in. <span class="accent">Let's set the scene.</span></div>
                        <p class="step-subtext">Tell us how you'll use your AI — we'll configure everything perfectly from day one.</p>

                        <div class="type-cards">
                            <label style="cursor:pointer;">
                                <input type="radio" name="account_type" value="business" checked class="sr-only" onchange="pickType('business')">
                                <div class="type-card selected" id="card-business">
                                    <div class="check-badge">✓</div>
                                    <div class="card-icon card-icon-green">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                    </div>
                                    <div class="card-title">I run a Business</div>
                                    <div class="card-desc">Set up an AI that handles customers, captures leads, answers FAQs, and books appointments — automatically.</div>
                                </div>
                            </label>
                            <label style="cursor:pointer;">
                                <input type="radio" name="account_type" value="personal" class="sr-only" onchange="pickType('personal')">
                                <div class="type-card" id="card-personal">
                                    <div class="check-badge">✓</div>
                                    <div class="card-icon card-icon-violet">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    </div>
                                    <div class="card-title">Personal Brand</div>
                                    <div class="card-desc">For influencers, CEOs, creators, and coaches who want an AI that speaks in their voice, 24/7.</div>
                                </div>
                            </label>
                        </div>

                        <div class="tip-box">
                            <svg class="tip-icon" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                            <p class="tip-text" id="tipText">Businesses get niche-specific FAQs, pricing templates, and customer flows pre-loaded — ready to edit in minutes.</p>
                        </div>
                    </div>

                    <!-- STEP 2: Your Story -->
                    <div class="step-section hidden-step" id="sec2">
                        <!-- Business branch -->
                        <div id="bizBranch">
                            <div class="step-heading">Tell us about <span class="accent">your business.</span></div>
                            <p class="step-subtext">We'll inject niche-specific FAQs, pricing templates, and service info — all pre-filled for you.</p>

                            <div class="field-group">
                                <label class="field-label">Business Name</label>
                                <input type="text" name="business_name" id="f_biz_name" class="glass-input" placeholder="e.g. The Spice Garden Restaurant" oninput="onBizName(this.value)" autocomplete="off">
                            </div>

                            <div class="field-group">
                                <label class="field-label">What's your industry?</label>
                                @php
                                $categories = [
                                    'Retail & E-commerce'                          => 'Retail & E-commerce',
                                    'Food & Beverage (Restaurants, Cafes)'         => 'Food & Beverage',
                                    'Real Estate & Property'                       => 'Real Estate & Property',
                                    'Healthcare & Wellness'                        => 'Healthcare & Wellness',
                                    'Professional Services (Agency, Consult)'      => 'Professional Services',
                                    'Education & Coaching'                         => 'Education & Coaching',
                                    'Travel, Tourism & Hospitality'                => 'Travel & Hospitality',
                                    'Beauty & Salon'                               => 'Beauty & Salon',
                                    'Local Services (Cleaning, Auto, Plumbing)'   => 'Local Services',
                                    'Other / Custom Niche'                         => 'Other / Custom Niche',
                                ];
                                @endphp
                                <div class="cat-grid">
                                    @foreach($categories as $catKey => $catLabel)
                                    <button type="button" class="cat-btn" data-cat="{{ $catKey }}" onclick="pickCat('{{ addslashes($catKey) }}')">
                                        <span class="cat-indicator"></span>
                                        <span class="cat-name">{{ $catLabel }}</span>
                                    </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="category" id="f_cat">
                            </div>

                            <div class="field-group" id="subContainer" style="display:none;">
                                <label class="field-label">Narrow it down</label>
                                <div class="sub-pills" id="subPills"></div>
                                <input type="hidden" name="subcategory" id="f_sub">
                            </div>

                            <div class="field-group">
                                <label class="field-label">
                                    Describe what you do
                                    <span class="prefill-link" id="prefillLink" onclick="doPrefill()">Use niche template</span>
                                </label>
                                <textarea name="business_description" id="f_biz_desc" class="glass-input" rows="3" placeholder="e.g. We're a cozy neighbourhood cafe serving farm-fresh food and specialty coffee since 2018…"></textarea>
                            </div>
                        </div>

                        <!-- Personal branch -->
                        <div id="persBranch" style="display:none;">
                            <div class="step-heading">Build your <span class="accent">personal brand.</span></div>
                            <p class="step-subtext">Your AI will represent you — speaking in your tone, answering your audience, and keeping you always-on.</p>

                            <div class="field-group">
                                <label class="field-label">Your Name / Brand Name</label>
                                <input type="text" name="personal_name" id="f_pers_name" class="glass-input" placeholder="e.g. Sarah Johnson" value="{{ $user->name }}" oninput="onPersName(this.value)">
                            </div>

                            <div class="field-group">
                                <label class="field-label">What best describes you?</label>
                                <div class="role-pills">
                                    @php
                                    $roles = [
                                        ['Influencer & Creator', 'Content, collabs, and DM replies at scale'],
                                        ['CEO & Executive', 'Professional presence for your audience'],
                                        ['Coach & Consultant', 'Pre-qualify leads and book discovery calls'],
                                        ['Artist & Musician', 'Fan engagement and project announcements'],
                                        ['Public Figure', 'Manage inbound from followers and press'],
                                        ['Other Individual', 'Personal use, tailored to your needs'],
                                    ];
                                    @endphp
                                    @foreach($roles as $role)
                                    <button type="button" class="role-pill" data-role="{{ $role[0] }}" onclick="pickRole('{{ addslashes($role[0]) }}', this)">
                                        <div class="role-pill-title">{{ $role[0] }}</div>
                                        <div class="role-pill-desc">{{ $role[1] }}</div>
                                    </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="personal_role" id="f_pers_role">
                            </div>

                            <div class="field-group">
                                <label class="field-label">What should your AI help with?</label>
                                <textarea name="personal_description" id="f_pers_desc" class="glass-input" rows="3" placeholder="e.g. Answer FAQs about my services, refer people to my booking link, and reply in my casual, direct tone..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Meet Your AI -->
                    <div class="step-section hidden-step" id="sec3">
                        <div class="step-heading">Now, <span class="accent">name your AI.</span></div>
                        <p class="step-subtext">Give your assistant a name and personality. Your audience will interact with it by name.</p>

                        <div class="field-group">
                            <label class="field-label">AI Assistant Name</label>
                            <input type="text" name="ai_name" id="f_ai_name" class="glass-input" placeholder="e.g. Spark, Nova, Aria" oninput="onAiName(this.value)">
                            <div class="suggestions" id="nameSuggs"></div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Pick a conversational tone</label>
                            @php
                            $tones = [
                                'professional' => ['Professional',    'Clear, polite, formal'],
                                'warm'         => ['Warm & Friendly', 'Empathetic, inviting'],
                                'casual'       => ['Casual & Witty',  'Relaxed, modern, fun'],
                                'sales'        => ['Sales Pro',       'Persuasive, lead-focused'],
                            ];
                            @endphp
                            <div class="tone-grid">
                                @foreach($tones as $key => $t)
                                <button type="button" class="tone-btn" id="tone_{{ $key }}" onclick="pickTone('{{ $key }}')">
                                    <div class="tone-title">{{ $t[0] }}</div>
                                    <div class="tone-desc">{{ $t[1] }}</div>
                                </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="ai_tone" id="f_tone">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Opening Greeting Message</label>
                            <textarea name="greeting_message" id="f_greeting" class="glass-input" rows="3" placeholder="Hi! Welcome to [Your Business]. I'm here — how can I help you today?" oninput="onGreeting(this.value)"></textarea>
                        </div>
                    </div>

                    <!-- STEP 4: Go Live -->
                    <div class="step-section hidden-step" id="sec4">
                        <div class="step-heading">You're set. <span class="accent">Connect WhatsApp.</span></div>
                        <p class="step-subtext">Scan the QR code with your phone to go live. Your AI starts handling conversations immediately.</p>

                        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px; display:flex; flex-direction:column; gap:13px;">
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size:13px; font-weight:600; color:var(--text-soft);">WhatsApp Connection</span>
                                <div class="qr-status-badge">
                                    <span class="status-dot red" id="statusDot"></span>
                                    <span id="statusText" style="color:var(--text-muted);">Initializing</span>
                                </div>
                            </div>

                            <!-- Loading state -->
                            <div id="qrLoading" style="display:flex; flex-direction:column; align-items:center; gap:10px; padding:20px 0;">
                                <div class="spinner"></div>
                                <p style="font-size:12px; color:var(--text-muted);">Generating your unique QR code</p>
                            </div>

                            <!-- QR image -->
                            <div id="qrImgWrap" style="display:none; align-items:center; justify-content:center;">
                                <div class="qr-box" style="width:160px; height:160px;">
                                    <div class="qr-scan-line"></div>
                                    <img id="qrImg" src="" alt="QR Code" style="width:100%;height:100%;object-fit:contain;">
                                </div>
                            </div>

                            <!-- Error state -->
                            <div id="qrError" style="display:none; text-align:center; padding:16px 0;">
                                <svg style="width:32px;height:32px;stroke:var(--text-muted);fill:none;stroke-width:2;margin-bottom:10px;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                <p style="font-size:12px; color:var(--text-muted); line-height:1.6; margin-bottom:10px;">Bot engine not detected. Make sure <code style="background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:10px;">node bot/index.js</code> is running, then click Retry.</p>
                                <button type="button" onclick="retryQr()" style="font-size:11px;padding:6px 16px;border-radius:8px;border:1px solid var(--accent);background:transparent;color:var(--accent);cursor:pointer;font-weight:600;transition:all 0.2s;" onmouseover="this.style.background='var(--accent)';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='var(--accent)'">Retry</button>
                            </div>

                            <!-- Steps -->
                            <div style="border-top:1px solid var(--border);padding-top:12px; display:flex; flex-direction:column; gap:9px;">
                                <div class="qr-step"><span class="qr-step-num">1</span><span class="qr-step-text">Open <strong>WhatsApp</strong> on your phone</span></div>
                                <div class="qr-step"><span class="qr-step-num">2</span><span class="qr-step-text">Tap <strong>Menu → Linked Devices → Link a Device</strong></span></div>
                                <div class="qr-step"><span class="qr-step-num">3</span><span class="qr-step-text">Point your camera at the QR code above</span></div>
                            </div>
                        </div>
                    </div>

                </div><!-- end form-area -->

                <!-- Bottom Nav -->
                <div class="bottom-nav">
                    <button type="button" class="btn-back invisible" id="backBtn" onclick="goBack()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Back
                    </button>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <button type="button" class="btn-skip" id="skipBtn" onclick="doSkip()">Skip for now</button>
                        <button type="button" class="btn-next" id="nextBtn" onclick="goNext()">
                            <span id="nextLabel">Continue</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- RIGHT PANEL -->
        <div class="right-panel">
            <!-- Preview panel (steps 1-3) -->
            <div class="right-content" id="rightContent">
                <p class="right-label" id="rightLabel">Live preview</p>

                <div style="position:relative;">
                    <div class="phone-glow"></div>
                    <div class="phone-wrap" id="phoneMockup">
                        <div class="phone-notch"></div>
                        <div class="wa-container">
                            <div class="wa-header">
                                <div class="wa-avatar" id="mockAvatar" style="background:#10b981;">B</div>
                                <div class="wa-info">
                                    <div class="wa-name" id="mockName">Your Business</div>
                                    <div class="wa-status" id="mockStatus">AI Assistant · Online</div>
                                </div>
                                <div class="wa-icons">
                                    <svg viewBox="0 0 24 24"><path d="M17.3 22.3c-3.8 0-7.7-1.6-10.4-4.3S2.6 11.8 2.6 8C2.6 4.7 5.3 2 8.6 2c2.4 0 4.5 1.5 5.3 3.8.4 1-.1 2-1 2.5l-1.3.7c.7 1.5 1.9 2.7 3.4 3.4l.7-1.3c.4-.9 1.5-1.4 2.5-1 .8.3 1.5 1 1.9 1.8 1.1 2.3.2 5.1-1.9 6.7-1.7 1.3-3.7 2-5.7 2.1-1.3-.1-1.3-.1 0 0z"/></svg>
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5" fill="white"/><circle cx="12" cy="12" r="1.5" fill="white"/><circle cx="12" cy="19" r="1.5" fill="white"/></svg>
                                </div>
                            </div>
                            <div class="wa-bg">
                                <div class="wa-bubble-security">Messages are end-to-end encrypted</div>
                                <div class="wa-bubble-bot">
                                    <span id="mockGreeting">Hello! How can I help you today?</span>
                                    <div class="wa-time">12:00 PM</div>
                                </div>
                                <div class="wa-bubble-user">
                                    Are you open today? What are your prices?
                                    <div class="wa-time">12:01 PM</div>
                                </div>
                                <div class="wa-bubble-bot" id="mockReplyBubble">
                                    <div class="typing-dots" id="mockTyping"><span></span><span></span><span></span></div>
                                    <span id="mockReply" style="display:none;"></span>
                                    <div class="wa-time" id="mockReplyTime" style="display:none;">12:01 PM</div>
                                </div>
                            </div>
                            <div class="wa-input-bar">
                                <div class="wa-input-fake">Type a message</div>
                                <div class="wa-mic-icon">
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="#8696a0" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card" id="statsCard">
                    <div class="info-stat">
                        <div class="stat-icon" style="background:rgba(16,185,129,0.1);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <div>
                            <div class="stat-val">0.8s</div>
                            <div class="stat-label">Average AI reply time</div>
                        </div>
                    </div>
                    <div class="info-stat">
                        <div class="stat-icon" style="background:rgba(139,92,246,0.1);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <div class="stat-val">24 / 7</div>
                            <div class="stat-label">Always-on customer coverage</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR panel (step 4) -->
            <div class="right-content" id="qrContent" style="display:none;">
                <p class="right-label">Scan to connect</p>
                <div class="qr-box" id="rightQrBox">
                    <div class="qr-scan-line" id="rightScanLine" style="display:none;"></div>
                    <div id="rightSpinner" style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                        <div class="spinner"></div>
                        <span style="font-size:10px;color:#64748b;">Generating</span>
                    </div>
                    <img id="rightQrImg" src="" alt="QR" style="display:none;width:100%;height:100%;object-fit:contain;">
                </div>
                <div class="qr-status-badge" id="rightStatus">
                    <span class="status-dot red" id="rightDot"></span>
                    <span id="rightStatusTxt" style="color:var(--text-muted);">Disconnected</span>
                </div>
                <div class="connected-state" id="connectedState">
                    <div class="connected-icon">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="connected-title">Connected!</div>
                    <div class="connected-desc">Your AI is live and ready to talk.</div>
                </div>

                <div class="qr-steps-card">
                    <div class="qr-step"><span class="qr-step-num">1</span><span class="qr-step-text">Open <strong>WhatsApp</strong> on your phone</span></div>
                    <div class="qr-step"><span class="qr-step-num">2</span><span class="qr-step-text">Tap <strong>Menu → Linked Devices → Link a Device</strong></span></div>
                    <div class="qr-step"><span class="qr-step-num">3</span><span class="qr-step-text">Point your camera at the QR code</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <!-- Skip form -->
    <form method="POST" action="{{ route('onboarding.skip') }}" id="skipForm">@csrf</form>

    <script>
    // ─── State ───────────────────────────────────────────────
    let currentStep = 1;
    let acctType    = 'business';
    let selCat      = '';
    let selSub      = '';
    let selTone     = '';
    let qrPollTimer = null;
    let qrAttempts  = 0;

    const subcats = {
        'Retail & E-commerce': ['Fashion & Apparel','Consumer Electronics','Home & Kitchen','Beauty & Cosmetics','Handmade Goods','Grocery & Gourmet'],
        'Food & Beverage (Restaurants, Cafes)': ['Fine Dining','Cafe & Coffee Shop','Fast Food & Takeaway','Bakery & Pastry','Catering Services','Bar & Nightlife'],
        'Real Estate & Property': ['Residential Sales','Commercial Property','Vacation Rentals','Property Management'],
        'Healthcare & Wellness': ['Medical & Dental','Gym & Fitness','Yoga & Pilates','Spa & Massage','Mental Health Coaching'],
        'Professional Services (Agency, Consult)': ['Software & IT Consultancy','Marketing & Creative Agency','Legal & Law Services','Financial & Accounting'],
        'Education & Coaching': ['Academic Tutoring','Language Academy','Business Coaching','Music & Creative Arts'],
        'Travel, Tourism & Hospitality': ['Hotel & Lodging','Tour Operator','Car Rental','Event Venue'],
        'Beauty & Salon': ['Hair Salon','Nail Spa','Barbershop','Aesthetics Clinic'],
        'Local Services (Cleaning, Auto, Plumbing)': ['Home Cleaning','Plumbing & Electrical','Auto Detailing & Repair','Landscaping'],
        'Other / Custom Niche': ['Custom / Not Listed']
    };

    const descPrefills = {
        'Retail & E-commerce': 'We sell premium boutique clothing and handmade accessories designed for everyday modern living.',
        'Food & Beverage (Restaurants, Cafes)': 'We are a cozy neighbourhood cafe serving farm-fresh food, artisanal coffee, and baked pastries daily.',
        'Real Estate & Property': 'We help families find their dream homes and handle professional property management across the city.',
        'Healthcare & Wellness': 'We offer tailored yoga, physiotherapy, and mental wellness sessions focused on long-term health.',
        'Professional Services (Agency, Consult)': 'We are a boutique software consultancy building reliable web and mobile applications for growing brands.',
        'Education & Coaching': 'We provide personalized K-12 tutoring and customized exam preparation for students worldwide.',
        'Travel, Tourism & Hospitality': 'We manage boutique vacation rentals and curate private local sightseeing tours for discerning travellers.',
        'Beauty & Salon': 'We offer professional haircutting, colouring, nail art, and skincare in a relaxing, premium salon environment.',
        'Local Services (Cleaning, Auto, Plumbing)': 'We provide residential plumbing, leak repairs, and electrical services with a 24-hour emergency response.',
        'Other / Custom Niche': 'We offer customized services tailored to each client\'s specific needs and requirements.'
    };

    const toneReplies = {
        professional: 'We are open Monday to Saturday, 9 AM to 6 PM. Pricing is tailored to each project. How may I assist you?',
        warm:         'Hi there! We are open till 10 PM tonight and our prices start at just $10. What can I help you with?',
        casual:       'Hey! Yep, open till 10 PM. Pricing starts around $25. What\'s up?',
        sales:        'Yes, we are open and there is a special offer running right now. Want me to check availability for you?'
    };

    const greetingTemplates = {
        professional: 'Hello! Welcome to [Biz]. I\'m [AI], your assistant. How may I serve you today?',
        warm:         'Hi! Welcome to [Biz]! I\'m [AI] — here to make your day easier. What do you need?',
        casual:       'Hey! Welcome to [Biz]. I\'m [AI]. What can I help you with today?',
        sales:        'Hello! Welcome to [Biz]. I\'m [AI] — we have special offers running now. What are you looking for?'
    };

    // ─── Init ────────────────────────────────────────────────
    window.onload = () => {
        pickType('business');
        pickTone('professional');
    };

    // ─── Toast ───────────────────────────────────────────────
    function showToast(msg, type = 'error') {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className = `toast ${type} show`;
        setTimeout(() => { t.classList.remove('show'); }, 3500);
    }

    // ─── STEP 1: Account type ─────────────────────────────────
    function pickType(type) {
        acctType = type;
        const bc = document.getElementById('card-business');
        const pc = document.getElementById('card-personal');
        bc.className = 'type-card' + (type === 'business' ? ' selected' : '');
        pc.className = 'type-card' + (type === 'personal' ? ' selected-violet' : '');

        const tip = document.getElementById('tipText');
        tip.textContent = type === 'business'
            ? 'Businesses get niche-specific FAQs, pricing templates, and customer flows pre-loaded — ready to edit in minutes.'
            : 'Personal brands get tone-matched responses, FAQ handling, and booking link referrals — all in your voice.';

        updateMockHeader();
        triggerBotReply();
    }

    // ─── STEP 2: Category ─────────────────────────────────────
    function pickCat(cat) {
        selCat = cat;
        document.getElementById('f_cat').value = cat;

        document.querySelectorAll('.cat-btn').forEach(b => {
            b.classList.toggle('cat-selected', b.dataset.cat === cat);
        });

        const subs = subcats[cat] || [];
        const cont = document.getElementById('subContainer');
        const pills = document.getElementById('subPills');
        pills.innerHTML = '';
        selSub = '';
        document.getElementById('f_sub').value = '';

        if (subs.length) {
            cont.style.display = 'block';
            subs.forEach(s => {
                const p = document.createElement('button');
                p.type = 'button'; p.className = 'sub-pill'; p.textContent = s;
                p.onclick = () => pickSub(s, p);
                pills.appendChild(p);
            });
        } else { cont.style.display = 'none'; }

        const pl = document.getElementById('prefillLink');
        pl.className = 'prefill-link' + (descPrefills[cat] ? ' show' : '');
        triggerBotReply();
    }

    function pickSub(sub, el) {
        selSub = sub;
        document.getElementById('f_sub').value = sub;
        document.querySelectorAll('.sub-pill').forEach(p => p.classList.remove('sub-selected'));
        el.classList.add('sub-selected');
    }

    function doPrefill() {
        if (selCat && descPrefills[selCat]) {
            document.getElementById('f_biz_desc').value = descPrefills[selCat];
        }
    }

    // ─── STEP 2: Personal role ────────────────────────────────
    function pickRole(role, el) {
        document.getElementById('f_pers_role').value = role;
        document.querySelectorAll('.role-pill').forEach(p => p.classList.remove('role-selected'));
        el.classList.add('role-selected');
    }

    // ─── STEP 3: AI name + tone ───────────────────────────────
    function onAiName(val) {
        // Only update the status subtitle in WhatsApp header, NOT the name
        updateMockStatus();
        regenerateGreeting();
        buildSuggestions(val);
    }

    function buildSuggestions(base) {
        const box = document.getElementById('nameSuggs');
        box.innerHTML = '';
        if (!base.trim()) return;
        const clean = base.split(' ')[0].replace(/[^a-zA-Z]/g, '');
        if (!clean) return;
        [`${clean} Bot`, `${clean} AI`, `${clean} Pro`].forEach(s => {
            const chip = document.createElement('button');
            chip.type = 'button'; chip.className = 'suggestion-chip'; chip.textContent = s;
            chip.onclick = () => { document.getElementById('f_ai_name').value = s; onAiName(s); };
            box.appendChild(chip);
        });
    }

    function pickTone(tone) {
        selTone = tone;
        document.getElementById('f_tone').value = tone;
        document.querySelectorAll('.tone-btn').forEach(b => b.classList.remove('tone-selected'));
        const btn = document.getElementById('tone_' + tone);
        if (btn) btn.classList.add('tone-selected');

        const av = document.getElementById('mockAvatar');
        const colours = { professional: '#10b981', warm: '#f43f5e', casual: '#06b6d4', sales: '#f59e0b' };
        av.style.background = colours[tone] || '#10b981';

        regenerateGreeting();
        triggerBotReply();
    }

    function regenerateGreeting() {
        const aiName = document.getElementById('f_ai_name').value || 'Assistant';
        // Greeting uses business name, NOT AI name
        const bizName = acctType === 'business'
            ? (document.getElementById('f_biz_name').value || 'our business')
            : (document.getElementById('f_pers_name').value || 'our brand');
        const tmpl = greetingTemplates[selTone] || greetingTemplates.professional;
        const msg = tmpl.replace('[Biz]', bizName).replace('[AI]', aiName);
        document.getElementById('f_greeting').value = msg;
        updateMockGreeting(msg);
    }

    function onGreeting(val) { updateMockGreeting(val); }

    // ─── Mock phone updates ───────────────────────────────────
    // WhatsApp header shows BUSINESS NAME (not AI name)
    function updateMockHeader() {
        const nameEl = document.getElementById('mockName');
        const av     = document.getElementById('mockAvatar');
        const biz    = document.getElementById('f_biz_name')  ? document.getElementById('f_biz_name').value.trim()  : '';
        const pers   = document.getElementById('f_pers_name') ? document.getElementById('f_pers_name').value.trim() : '';

        let displayName, initial;
        if (acctType === 'business') {
            displayName = biz || 'Your Business';
            initial     = (biz || 'B')[0].toUpperCase();
        } else {
            displayName = pers || 'Your Brand';
            initial     = (pers || 'P')[0].toUpperCase();
        }

        nameEl.textContent = displayName;
        av.textContent     = initial;
    }

    function updateMockStatus() {
        const aiName = document.getElementById('f_ai_name') ? document.getElementById('f_ai_name').value.trim() : '';
        document.getElementById('mockStatus').textContent = aiName ? `${aiName} · Online` : 'AI Assistant · Online';
    }

    function updateMockGreeting(msg) {
        document.getElementById('mockGreeting').textContent = msg || 'Hello! How can I help you today?';
    }

    function onBizName(val) { updateMockHeader(); buildSuggestions(val); }
    function onPersName(val) { updateMockHeader(); }

    function triggerBotReply() {
        const typing = document.getElementById('mockTyping');
        const reply  = document.getElementById('mockReply');
        const time   = document.getElementById('mockReplyTime');
        typing.style.display = 'flex';
        reply.style.display  = 'none';
        time.style.display   = 'none';
        clearTimeout(window._replyTimer);
        window._replyTimer = setTimeout(() => {
            typing.style.display = 'none';
            reply.textContent    = toneReplies[selTone] || toneReplies.professional;
            reply.style.display  = 'inline';
            time.style.display   = 'block';
        }, 900);
    }

    // ─── Navigation ──────────────────────────────────────────
    function goNext() {
        if (currentStep === 1) { transition(1, 2); }
        else if (currentStep === 2) { if (!validateStep2()) return; transition(2, 3); }
        else if (currentStep === 3) { if (!validateStep3()) return; submitOnboarding(); }
    }

    function goBack() { if (currentStep > 1) transition(currentStep, currentStep - 1); }

    function transition(from, to) {
        document.getElementById('sec' + from).className = 'step-section hidden-step';
        setTimeout(() => { document.getElementById('sec' + to).className = 'step-section visible-step'; }, 50);

        if (to === 2) {
            document.getElementById('bizBranch').style.display  = acctType === 'business' ? 'block' : 'none';
            document.getElementById('persBranch').style.display = acctType === 'personal' ? 'block' : 'none';
        }

        updateStepper(to);
        currentStep = to;
        updateNav(to);

        if (to === 4) {
            document.getElementById('rightContent').style.display = 'none';
            document.getElementById('qrContent').style.display    = 'flex';
        } else {
            document.getElementById('rightContent').style.display = 'flex';
            document.getElementById('qrContent').style.display    = 'none';
        }
    }

    function updateStepper(step) {
        for (let i = 1; i <= 4; i++) {
            const badge = document.getElementById('sb' + i);
            const label = document.getElementById('sl' + i);
            if (i < step) {
                badge.className   = 'step-badge done';
                badge.textContent = '✓';
            } else if (i === step) {
                badge.className   = 'step-badge active';
                badge.textContent = i;
                label.className   = 'step-label active-label';
            } else {
                badge.className   = 'step-badge pending';
                badge.textContent = i;
                label.className   = 'step-label';
            }
            if (i < 4) {
                document.getElementById('sc' + i).className = 'step-connector' + (i < step ? ' filled' : '');
            }
        }
    }

    function updateNav(step) {
        const back = document.getElementById('backBtn');
        const next = document.getElementById('nextBtn');
        const skip = document.getElementById('skipBtn');
        const lbl  = document.getElementById('nextLabel');

        back.className = 'btn-back' + (step === 1 ? ' invisible' : '');

        if (step === 4) {
            next.style.display = 'none';
            skip.style.display = 'inline-flex';
            back.className     = 'btn-back invisible';
        } else {
            next.style.display = 'flex';
            next.disabled      = false;
            skip.style.display = 'none';
            lbl.textContent    = step === 3 ? 'Launch My AI' : 'Continue';
        }
    }

    // ─── Validation ───────────────────────────────────────────
    function validateStep2() {
        if (acctType === 'business') {
            if (!document.getElementById('f_biz_name').value.trim()) { showToast('Please enter your business name.'); return false; }
            if (!document.getElementById('f_cat').value)             { showToast('Please select a business category.'); return false; }
        } else {
            if (!document.getElementById('f_pers_name').value.trim()) { showToast('Please enter your name.'); return false; }
            if (!document.getElementById('f_pers_role').value)        { showToast('Please select what best describes you.'); return false; }
        }
        return true;
    }

    function validateStep3() {
        if (!document.getElementById('f_ai_name').value.trim())   { showToast('Please name your AI assistant.'); return false; }
        if (!document.getElementById('f_tone').value)             { showToast('Please choose a conversational tone.'); return false; }
        if (!document.getElementById('f_greeting').value.trim())  { showToast('Please write a greeting message.'); return false; }
        return true;
    }

    // ─── Submit ───────────────────────────────────────────────
    function submitOnboarding() {
        const btn = document.getElementById('nextBtn');
        btn.disabled = true;
        document.getElementById('nextLabel').textContent = 'Saving';

        const fd = new FormData(document.getElementById('obForm'));

        fetch("{{ route('onboarding.store') }}", {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            document.getElementById('nextLabel').textContent = 'Launch My AI';
            if (data.success) {
                transition(3, 4);
                startQrPoll();
            } else {
                showToast(data.error || 'Something went wrong. Please try again.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            document.getElementById('nextLabel').textContent = 'Launch My AI';
            showToast('Connection error. Please try again.');
        });
    }

    // ─── Skip ─────────────────────────────────────────────────
    function doSkip() { document.getElementById('skipForm').submit(); }

    // ─── QR Polling ───────────────────────────────────────────
    // Mirrors the dashboard flow exactly:
    //   1. Call /api/bot/start  (wakes the Node.js bot engine)
    //   2. Poll /api/qr every 1.5s until QR appears or status=connected
    function startQrPoll() {
        qrAttempts  = 0;

        // Wake the bot engine first (same as dashboard "Generate QR" button)
        fetch('/api/bot/start', {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                           'Content-Type': 'application/json' }
        }).catch(() => {}); // Fire-and-forget; poll will surface any issues

        // Start polling after a 1-second head-start so bot has time to init
        setTimeout(() => {
            qrPollTimer = setInterval(pollQr, 1500);
            pollQr();
        }, 1000);
    }

    function retryQr() {
        // Reset error UI and stop any existing poll
        clearInterval(qrPollTimer);
        qrAttempts = 0;
        document.getElementById('qrError').style.display   = 'none';
        document.getElementById('qrLoading').style.display = 'flex';
        document.getElementById('qrImgWrap').style.display = 'none';
        document.getElementById('rightSpinner').style.display = 'flex';
        document.getElementById('rightQrImg').style.display = 'none';
        document.getElementById('rightScanLine').style.display = 'none';
        setStatus('yellow', 'Connecting');
        startQrPoll();
    }

    function pollQr() {
        qrAttempts++;
        fetch('/api/qr', { credentials: 'same-origin' })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            const loadEl   = document.getElementById('qrLoading');
            const imgWrap  = document.getElementById('qrImgWrap');
            const errDiv   = document.getElementById('qrError');
            const rSpinner = document.getElementById('rightSpinner');
            const rImg     = document.getElementById('rightQrImg');
            const rScan    = document.getElementById('rightScanLine');

            if (data.qr) {
                // Reset attempt counter each time we get a valid QR
                qrAttempts = 0;
                loadEl.style.display  = 'none';
                errDiv.style.display  = 'none';
                imgWrap.style.display = 'flex';
                document.getElementById('qrImg').src = data.qr;

                rSpinner.style.display = 'none';
                rImg.src               = data.qr;
                rImg.style.display     = 'block';
                rScan.style.display    = 'block';
                setStatus('yellow', 'Scan to connect');
            } else if (data.session_state === 'paused') {
                // Bot hit QR timeout — auto-restart the engine silently
                clearInterval(qrPollTimer);
                qrAttempts = 0;
                setStatus('yellow', 'Restarting engine');
                setTimeout(() => startQrPoll(), 2000);
                return;
            }

            if (data.status === 'connected') {
                clearInterval(qrPollTimer);
                setStatus('green', 'Connected');
                imgWrap.style.display       = 'none';
                rImg.style.display          = 'none';
                rScan.style.display         = 'none';
                rSpinner.style.display      = 'none';
                document.getElementById('connectedState').classList.add('show');
                confetti({ particleCount: 200, spread: 90, origin: { y: 0.5 } });
                setTimeout(() => { window.location.href = "{{ route('dashboard') }}"; }, 2500);
            }
        })
        .catch(() => {
            // Allow up to 25 failed attempts (~37s) before showing the error state
            // The bot engine can take up to 15 seconds to start and push its first QR
            if (qrAttempts >= 25) {
                clearInterval(qrPollTimer);
                document.getElementById('qrLoading').style.display = 'none';
                document.getElementById('qrError').style.display   = 'block';
                document.getElementById('rightSpinner').style.display = 'none';
                setStatus('red', 'Bot offline');
            }
        });
    }

    function setStatus(color, text) {
        ['statusDot','rightDot'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.className = 'status-dot ' + color;
        });
        const st = document.getElementById('statusText');
        const rt = document.getElementById('rightStatusTxt');
        if (st) st.textContent = text;
        if (rt) rt.textContent = text;
    }
    </script>
</body>
</html>
