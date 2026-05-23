// ============================================================
// server.js — Express API server (called by Laravel)
// v3.1 — Multi-Tenant Edition
//   All endpoints accept an optional tenant_id in body/header
//   Defaults to tenant 1 for backwards compatibility
// ============================================================
import express from 'express';
import cors from 'cors';
import fs from 'fs';
import path from 'path';
import { sendMessage, reconnect, getStatus, getHealth, startWhatsApp } from './whatsapp.js';
import { enqueueMessage } from './queue.js';
import { log } from './utils.js';

const app = express();
const PORT          = process.env.BOT_PORT    || 3000;
const SHARED_SECRET = process.env.SHARED_SECRET || 'whatsapp_ai_secret_2026';

app.use(cors({ origin: 'http://127.0.0.1:8000' }));
app.use(express.json());

// ── Auth middleware: verify shared secret ────────────────────
function authMiddleware(req, res, next) {
  const secret = req.headers['x-bot-secret'];
  if (secret !== SHARED_SECRET) {
    log('warn', 'Unauthorized request blocked', { ip: req.ip });
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

// ── Resolve tenant ID from request (header or body) ──────────
function resolveTenantId(req) {
  const fromHeader = req.headers['x-tenant-id'];
  const fromBody   = req.body?.tenant_id;
  return parseInt(fromHeader || fromBody || '1', 10);
}

// ── GET /status — Current WhatsApp connection status ─────────
app.get('/status', authMiddleware, (req, res) => {
  const tenantId = resolveTenantId(req);
  const status   = getStatus(tenantId);
  res.json({ success: true, tenant_id: tenantId, ...status });
});

// ── POST /send — Enqueue a WhatsApp message ──────────────────
app.post('/send', authMiddleware, (req, res) => {
  const { jid, text, message_id, delay_min = 3, delay_max = 15 } = req.body;
  const tenantId = resolveTenantId(req);

  if (!jid || !text) {
    return res.status(400).json({ error: 'jid and text are required' });
  }

  try {
    const queueId = enqueueMessage(jid, text, message_id, delay_min, delay_max, tenantId);
    res.json({ success: true, message: 'Enqueued', queue_id: queueId, tenant_id: tenantId });
  } catch (err) {
    log('error', 'Enqueue failed', { error: err.message, tenantId });
    res.status(500).json({ success: false, error: err.message });
  }
});

// ── POST /reconnect — Force reconnect / new QR ───────────────
app.post('/reconnect', authMiddleware, async (req, res) => {
  const tenantId = resolveTenantId(req);
  try {
    await reconnect(tenantId);
    res.json({ success: true, message: 'Reconnect initiated', tenant_id: tenantId });
  } catch (err) {
    log('error', 'Reconnect failed', { error: err.message, tenantId });
    res.status(500).json({ success: false, error: err.message });
  }
});

// ── POST /start — Start a new tenant session ─────────────────
// Always force=true and wipe stale session folder so a fresh
// QR is always generated (handles paused / expired sessions).
app.post('/start', authMiddleware, async (req, res) => {
  const tenantId = resolveTenantId(req);
  try {
    // Wipe stale auth folder so Baileys doesn't silently try
    // to resume an expired / logged-out session
    const sessionDir = path.join(process.cwd(), `auth_session_${tenantId}`);
    if (fs.existsSync(sessionDir)) {
      fs.rmSync(sessionDir, { recursive: true, force: true });
      log('info', `[T${tenantId}] Cleared stale auth session folder before fresh start`);
    }
    await startWhatsApp(tenantId, true);
    res.json({ success: true, message: 'Session started', tenant_id: tenantId });
  } catch (err) {
    log('error', 'Start failed', { error: err.message, tenantId });
    res.status(500).json({ success: false, error: err.message });
  }
});

// ── GET /health — Simple ping (no auth) ──────────────────────
app.get('/health', (req, res) => {
  res.json({ ok: true, uptime: process.uptime() });
});

// ── GET /health/detail — Full session health (authenticated) ─
app.get('/health/detail', authMiddleware, (req, res) => {
  const tenantId = resolveTenantId(req);
  const health   = getHealth(tenantId);
  res.json({ success: true, tenant_id: tenantId, ...health });
});

export function startServer() {
  app.listen(PORT, '127.0.0.1', () => {
    log('info', `Bot API server running on http://127.0.0.1:${PORT}`);
  });
}
