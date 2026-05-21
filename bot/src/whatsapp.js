// ============================================================
// whatsapp.js — Baileys WhatsApp Connection Engine
// v3.0 — Multi-Tenant Edition
//   - Each tenant gets an isolated socket, session folder, and state
//   - All single-tenant exports are preserved for backwards compatibility
//   - Tenant sessions stored in auth_session_<tenantId>/
// ============================================================
import makeWASocket, {
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
} from '@whiskeysockets/baileys';
import pino from 'pino';
import axios from 'axios';
import QRCode from 'qrcode';
import { log, extractText, jidToPhone, isGroupJid } from './utils.js';

const LARAVEL_URL   = process.env.LARAVEL_URL   || 'http://127.0.0.1:8000';
const SHARED_SECRET = process.env.SHARED_SECRET || 'whatsapp_ai_secret_2026';

// ── Session State Machine ─────────────────────────────────────
const STATE = {
  IDLE:         'idle',
  CONNECTING:   'connecting',
  CONNECTED:    'connected',
  RECONNECTING: 'reconnecting',
  BANNED_RISK:  'banned_risk',
  PAUSED:       'paused',
  LOGGED_OUT:   'logged_out',
};

// ── Backoff Config ────────────────────────────────────────────
const BACKOFF_STEPS_MS   = [8000, 16000, 32000, 60000, 300000];
const BAN_RISK_THRESHOLD = 5;
const RECONNECT_WINDOW_MS = 10 * 60 * 1000;
const MAX_QR_CYCLES       = 5;

// ── Per-Tenant Session Store (Map) ────────────────────────────
// tenantId (number) → { sock, sessionState, reconnectTimer, reconnectCount,
//                        reconnectWindow, healthScore, lastConnectedAt,
//                        sessionStartedAt, isBanRisk, isAutomationPaused,
//                        qrCount }
const tenants = new Map();

function newTenantState() {
  return {
    sock:                null,
    sessionState:        STATE.IDLE,
    reconnectTimer:      null,
    reconnectCount:      0,
    reconnectWindow:     [],
    healthScore:         100,
    lastConnectedAt:     null,
    sessionStartedAt:    Date.now(),
    isBanRisk:           false,
    isAutomationPaused:  false,
    qrCount:             0,
    isQrTimeout:         false,
  };
}

function getTenantState(tenantId) {
  if (!tenants.has(tenantId)) {
    tenants.set(tenantId, newTenantState());
  }
  return tenants.get(tenantId);
}

// ── Internal helper: POST to Laravel API ─────────────────────
async function notifyLaravel(endpoint, data, tenantId = 1) {
  try {
    await axios.post(`${LARAVEL_URL}${endpoint}`, data, {
      headers: {
        'Content-Type':  'application/json',
        'X-Bot-Secret':  SHARED_SECRET,
        'X-Tenant-ID':   String(tenantId),
      },
      timeout: 8000,
    });
  } catch (err) {
    log('warn', `Laravel notify failed [${endpoint}]`, { error: err.message, tenantId });
  }
}

// ── Calculate health score based on recent disconnects ───────
function recalculateHealthScore(ts) {
  const now = Date.now();
  ts.reconnectWindow = ts.reconnectWindow.filter(t => now - t < RECONNECT_WINDOW_MS);
  const d = ts.reconnectWindow.length;
  if      (d === 0) ts.healthScore = 100;
  else if (d === 1) ts.healthScore = 85;
  else if (d === 2) ts.healthScore = 65;
  else if (d === 3) ts.healthScore = 45;
  else if (d === 4) ts.healthScore = 25;
  else              ts.healthScore = Math.max(0, 10 - (d - 5) * 2);
  ts.isBanRisk = d >= BAN_RISK_THRESHOLD;
  log('info', `Health score: ${ts.healthScore}/100`, { recentDisconnects: d, isBanRisk: ts.isBanRisk, sessionState: ts.sessionState });
}

function getBackoffDelay(ts) {
  const stepIndex = Math.min(ts.reconnectCount, BACKOFF_STEPS_MS.length - 1);
  return BACKOFF_STEPS_MS[stepIndex];
}

async function pushHealthToLaravel(ts, status, tenantId) {
  await notifyLaravel('/api/whatsapp/status', {
    status,
    session_state:     ts.sessionState,
    health_score:      ts.healthScore,
    ban_risk:          ts.isBanRisk,
    reconnect_count:   ts.reconnectCount,
    last_connected_at: ts.lastConnectedAt ? ts.lastConnectedAt.toISOString() : null,
  }, tenantId);
}

// ── Start / Restart WhatsApp connection for a tenant ─────────
export async function startWhatsApp(tenantId = 1, force = false) {
  const ts = getTenantState(tenantId);

  if (ts.reconnectTimer) {
    clearTimeout(ts.reconnectTimer);
    ts.reconnectTimer = null;
  }

  // Reset state first if forced, THEN check if paused
  if (force) {
    ts.qrCount      = 0;
    ts.sessionState = STATE.IDLE;
    ts.isQrTimeout  = false;
  }

  if (ts.sessionState === STATE.PAUSED && !force) {
    log('warn', `[T${tenantId}] Session paused. Call with force=true to override.`);
    return;
  }

  // Clean up any existing socket connection to prevent leaks and duplicate listeners
  if (ts.sock) {
    log('info', `[T${tenantId}] Cleaning up existing socket connection before starting a new one`);
    try { ts.sock.ev.removeAllListeners(); } catch (err) {}
    try { ts.sock.end(); } catch (err) {}
    ts.sock = null;
  }

  ts.sessionState = STATE.CONNECTING;
  const logger = pino({ level: 'silent' });
  const sessionDir = `auth_session_${tenantId}`;
  const { state, saveCreds } = await useMultiFileAuthState(sessionDir);
  const { version } = await fetchLatestBaileysVersion();

  log('info', `[T${tenantId}] Starting WhatsApp connection (Baileys v${version.join('.')})`, {
    reconnectCount: ts.reconnectCount,
    healthScore:    ts.healthScore,
  });

  ts.sock = makeWASocket({
    version,
    auth: {
      creds: state.creds,
      keys:  makeCacheableSignalKeyStore(state.keys, logger),
    },
    logger,
    generateHighQualityLinkPreview: false,
    browser: ['iChatUp', 'Chrome', '120.0'],
    getMessage: async () => undefined,
  });

  ts.sock.ev.on('creds.update', saveCreds);

  // ── Handle connection state changes ────────────────────────
  ts.sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      ts.qrCount++;
      log('info', `[T${tenantId}] QR code generated — scan with WhatsApp`);
      ts.sessionState = STATE.CONNECTING;
      try {
        const qrBase64 = await QRCode.toDataURL(qr, {
          width: 300, margin: 2,
          color: { dark: '#000000', light: '#ffffff' },
        });
        await notifyLaravel('/api/qr',               { qr: qrBase64 }, tenantId);
        await pushHealthToLaravel(ts, 'connecting',    tenantId);
      } catch (err) {
        log('error', `[T${tenantId}] QR generation failed`, { error: err.message });
      }

      if (ts.qrCount >= MAX_QR_CYCLES) {
        ts.qrCount      = 0;
        ts.sessionState = STATE.PAUSED;
        ts.isQrTimeout  = true;
        log('warn', `[T${tenantId}] No scan after ${MAX_QR_CYCLES} QR codes — pausing. Click Generate QR to retry.`);
        // Clear QR from DB and mark as disconnected so dashboard shows Generate QR button
        await notifyLaravel('/api/qr', { qr: null }, tenantId);
        await pushHealthToLaravel(ts, 'disconnected', tenantId);
        try { if (ts.sock) { ts.sock.ev.removeAllListeners(); ts.sock.end(); ts.sock = null; } } catch {}
        return;
      }
    }

    if (connection === 'close') {
      if (ts.isQrTimeout) {
        ts.isQrTimeout = false;
        log('info', `[T${tenantId}] Connection closed cleanly due to QR timeout. Awaiting manual reconnect.`);
        return;
      }

      const statusCode  = lastDisconnect?.error?.output?.statusCode;
      const isLoggedOut = statusCode === DisconnectReason.loggedOut;

      ts.sessionState = isLoggedOut ? STATE.LOGGED_OUT : STATE.RECONNECTING;
      ts.reconnectWindow.push(Date.now());
      ts.reconnectCount++;
      recalculateHealthScore(ts);

      log('warn', `[T${tenantId}] Connection closed`, {
        statusCode, willReconnect: !isLoggedOut, healthScore: ts.healthScore, isBanRisk: ts.isBanRisk,
      });

      await pushHealthToLaravel(ts, ts.isBanRisk ? 'banned_risk' : 'disconnected', tenantId);

      if (isLoggedOut) {
        log('warn', `[T${tenantId}] Logged out from WhatsApp — clearing session and waiting for new QR scan`);
        ts.sessionState = STATE.LOGGED_OUT;
        // Clear any stale QR and mark disconnected so dashboard shows Generate QR
        await notifyLaravel('/api/qr', { qr: null }, tenantId);
        // Delete the auth session folder so next scan starts fresh
        try {
          const { default: fs } = await import('fs');
          const sessionDir = `auth_session_${tenantId}`;
          if (fs.existsSync(sessionDir)) {
            fs.rmSync(sessionDir, { recursive: true, force: true });
            log('info', `[T${tenantId}] Cleared auth session folder after logout`);
          }
        } catch (fsErr) {
          log('warn', `[T${tenantId}] Could not clear session folder`, { error: fsErr.message });
        }
        // Set to paused so the user must click Generate QR
        ts.sessionState = STATE.PAUSED;
        ts.isQrTimeout  = false;
        return;
      }

      if (ts.isBanRisk) {
        ts.sessionState        = STATE.PAUSED;
        ts.isAutomationPaused  = true;
        log('error', `[T${tenantId}] 🚨 BAN RISK DETECTED — pausing automation.`);
        await pushHealthToLaravel(ts, 'banned_risk', tenantId);
        return;
      }

      const delay = getBackoffDelay(ts);
      log('info', `[T${tenantId}] Scheduling reconnect in ${delay / 1000}s (attempt #${ts.reconnectCount})...`);
      ts.reconnectTimer = setTimeout(() => startWhatsApp(tenantId), delay);
    }

    if (connection === 'open') {
      ts.qrCount          = 0;
      ts.sessionState     = STATE.CONNECTED;
      ts.lastConnectedAt  = new Date();
      ts.isAutomationPaused = false;

      if (!ts.isBanRisk) {
        ts.healthScore = Math.min(100, ts.healthScore + 20);
      }

      log('info', `[T${tenantId}] ✅ WhatsApp connected successfully!`, { healthScore: ts.healthScore });
      await pushHealthToLaravel(ts, 'connected', tenantId);
      await notifyLaravel('/api/qr', { qr: null }, tenantId);
    }
  });

  // ── Handle incoming messages ─────────────────────────────
  ts.sock.ev.on('messages.upsert', async ({ messages, type }) => {
    if (type !== 'notify') return;

    for (const msg of messages) {
      if (msg.key.fromMe) continue;
      if (!msg.message)   continue;

      const jid = msg.key.remoteJid;
      if (!jid || isGroupJid(jid)) continue;

      const phone     = jidToPhone(jid);
      const text      = extractText(msg);
      if (!text) continue;

      const messageId = msg.key.id;
      const timestamp = msg.messageTimestamp
        ? Number(msg.messageTimestamp)
        : Math.floor(Date.now() / 1000);

      log('info', `[T${tenantId}] Incoming message`, { phone, preview: text.substring(0, 60) });

      await notifyLaravel('/api/whatsapp/message', {
        phone, jid, message: text, message_id: messageId, timestamp,
      }, tenantId);
    }
  });

  return ts.sock;
}

// ── Send a message via Baileys ────────────────────────────────
export async function sendMessage(jid, text, tenantId = 1) {
  const ts = getTenantState(tenantId);
  if (!ts.sock) throw new Error(`[T${tenantId}] Socket not initialized`);
  if (ts.sessionState !== STATE.CONNECTED) {
    throw new Error(`[T${tenantId}] WhatsApp not connected (state: ${ts.sessionState})`);
  }
  if (ts.isAutomationPaused) {
    throw new Error(`[T${tenantId}] Automation paused — session is in ban-risk state`);
  }
  await ts.sock.sendMessage(jid, { text });
  log('info', `[T${tenantId}] Message sent`, { jid, preview: text.substring(0, 60) });
}

// ── Manual reconnect for a tenant ────────────────────────────
export async function reconnect(tenantId = 1) {
  const ts = getTenantState(tenantId);
  log('info', `[T${tenantId}] Manual reconnect triggered — resetting session`);

  ts.isBanRisk          = false;
  ts.isAutomationPaused = false;
  ts.reconnectWindow    = [];
  ts.healthScore        = 100;
  ts.sessionState       = STATE.IDLE;
  ts.qrCount            = 0;

  if (ts.reconnectTimer) {
    clearTimeout(ts.reconnectTimer);
    ts.reconnectTimer = null;
  }

  if (ts.sock) {
    try { ts.sock.ev.removeAllListeners(); } catch {}
    try { await ts.sock.logout(); }          catch {}
    ts.sock = null;
  }

  const fs = await import('fs');
  const sessionDir = `auth_session_${tenantId}`;
  if (fs.existsSync(sessionDir)) {
    fs.rmSync(sessionDir, { recursive: true, force: true });
    log('info', `[T${tenantId}] Cleared stale ${sessionDir} folder`);
  }

  await startWhatsApp(tenantId);
}

// ── Get current connection status for a tenant ───────────────
export function getStatus(tenantId = 1) {
  const ts = getTenantState(tenantId);
  return { status: ts.sessionState };
}

// ── Get full session health for a tenant ─────────────────────
export function getHealth(tenantId = 1) {
  const ts = getTenantState(tenantId);
  const uptimeSeconds = Math.floor((Date.now() - ts.sessionStartedAt) / 1000);
  return {
    session_state:      ts.sessionState,
    health_score:       ts.healthScore,
    ban_risk:           ts.isBanRisk,
    automation_paused:  ts.isAutomationPaused,
    reconnect_count:    ts.reconnectCount,
    recent_disconnects: ts.reconnectWindow.filter(t => Date.now() - t < RECONNECT_WINDOW_MS).length,
    last_connected_at:  ts.lastConnectedAt ? ts.lastConnectedAt.toISOString() : null,
    uptime_seconds:     uptimeSeconds,
    engine_started_at:  new Date(ts.sessionStartedAt).toISOString(),
  };
}
