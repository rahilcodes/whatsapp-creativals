// ============================================================
// server.js — Express API server (called by Laravel)
// v3.0 — Multi-Tenant Edition
//   All endpoints accept an optional tenant_id in body/header
//   Defaults to tenant 1 for backwards compatibility
// ============================================================
import express from 'express';
import cors from 'cors';
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
app.post('/start', authMiddleware, async (req, res) => {
  const tenantId = resolveTenantId(req);
  try {
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
