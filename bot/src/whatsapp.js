// ============================================================
// whatsapp.js — Baileys WhatsApp Connection Engine
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

const LARAVEL_URL = process.env.LARAVEL_URL || 'http://127.0.0.1:8000';
const SHARED_SECRET = process.env.SHARED_SECRET || 'whatsapp_ai_secret_2026';

let sock = null;
let connectionStatus = 'disconnected';
let reconnectTimer = null;

// ── Internal helper: POST to Laravel API ────────────────────
async function notifyLaravel(endpoint, data) {
  try {
    await axios.post(`${LARAVEL_URL}${endpoint}`, data, {
      headers: {
        'Content-Type': 'application/json',
        'X-Bot-Secret': SHARED_SECRET,
      },
      timeout: 8000,
    });
  } catch (err) {
    log('warn', `Laravel notify failed [${endpoint}]`, { error: err.message });
  }
}

// ── Start / Restart WhatsApp connection ──────────────────────
export async function startWhatsApp() {
  if (reconnectTimer) {
    clearTimeout(reconnectTimer);
    reconnectTimer = null;
  }

  const logger = pino({ level: 'silent' });
  const { state, saveCreds } = await useMultiFileAuthState('auth_session');
  const { version } = await fetchLatestBaileysVersion();

  log('info', `Starting WhatsApp connection (Baileys v${version.join('.')})`);

  sock = makeWASocket({
    version,
    auth: {
      creds: state.creds,
      keys: makeCacheableSignalKeyStore(state.keys, logger),
    },
    printQRInTerminal: true, // Also print to terminal for debugging
    logger,
    generateHighQualityLinkPreview: false,
    browser: ['WhatsApp AI Assistant', 'Chrome', '120.0'],
    getMessage: async () => undefined,
  });

  // ── Save credentials on update ───────────────────────────
  sock.ev.on('creds.update', saveCreds);

  // ── Handle connection state changes ──────────────────────
  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    // QR Code generated → convert to base64 image → push to Laravel
    if (qr) {
      log('info', 'QR code generated — scan with WhatsApp');
      connectionStatus = 'connecting';
      try {
        const qrBase64 = await QRCode.toDataURL(qr, {
          width: 300,
          margin: 2,
          color: { dark: '#000000', light: '#ffffff' },
        });
        await notifyLaravel('/api/qr', { qr: qrBase64 });
        await notifyLaravel('/api/whatsapp/status', { status: 'connecting' });
      } catch (err) {
        log('error', 'QR generation failed', { error: err.message });
      }
    }

    // Connection closed
    if (connection === 'close') {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
      connectionStatus = 'disconnected';

      log('warn', 'Connection closed', { statusCode, willReconnect: shouldReconnect });
      await notifyLaravel('/api/whatsapp/status', { status: 'disconnected' });

      if (shouldReconnect) {
        log('info', 'Scheduling reconnect in 8 seconds...');
        reconnectTimer = setTimeout(startWhatsApp, 8000);
      } else {
        log('warn', 'Logged out — manual QR scan required');
      }
    }

    // Successfully connected
    if (connection === 'open') {
      connectionStatus = 'connected';
      log('info', '✅ WhatsApp connected successfully!');
      await notifyLaravel('/api/whatsapp/status', { status: 'connected' });
      await notifyLaravel('/api/qr', { qr: null }); // Clear QR from dashboard
    }
  });

  // ── Handle incoming messages ──────────────────────────────
  sock.ev.on('messages.upsert', async ({ messages, type }) => {
    if (type !== 'notify') return;

    for (const msg of messages) {
      // Skip: own messages, groups, empty
      if (msg.key.fromMe) continue;
      if (!msg.message) continue;

      const jid = msg.key.remoteJid;
      if (!jid || isGroupJid(jid)) continue; // Groups ignored for now

      const phone = jidToPhone(jid);
      const text = extractText(msg);
      if (!text) continue;

      const messageId = msg.key.id;
      const timestamp = msg.messageTimestamp
        ? Number(msg.messageTimestamp)
        : Math.floor(Date.now() / 1000);

      log('info', 'Incoming message', { phone, preview: text.substring(0, 60) });

      await notifyLaravel('/api/whatsapp/message', {
        phone,
        jid,
        message: text,
        message_id: messageId,
        timestamp,
      });
    }
  });

  return sock;
}

// ── Send a message via Baileys ────────────────────────────────
export async function sendMessage(jid, text) {
  if (!sock) throw new Error('Socket not initialized');
  if (connectionStatus !== 'connected') throw new Error('WhatsApp not connected');

  await sock.sendMessage(jid, { text });
  log('info', 'Message sent', { jid, preview: text.substring(0, 60) });
}

// ── Manual reconnect (clears session to get new QR) ──────────
export async function reconnect() {
  log('info', 'Manual reconnect triggered');
  if (sock) {
    try { sock.ev.removeAllListeners(); } catch {}
    try { await sock.logout(); } catch {}
    sock = null;
  }
  
  // Nuke the auth folder so Baileys generates a fresh QR instead of reusing dead creds
  import('fs').then((fs) => {
    if (fs.existsSync('auth_session')) {
      fs.rmSync('auth_session', { recursive: true, force: true });
      log('info', 'Cleared stale auth_session folder');
    }
  });

  connectionStatus = 'disconnected';
  await startWhatsApp();
}

// ── Get current connection status ────────────────────────────
export function getStatus() {
  return { status: connectionStatus };
}
