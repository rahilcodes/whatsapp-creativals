// ============================================================
// server.js — Express API server (called by Laravel)
// ============================================================
import express from 'express';
import cors from 'cors';
import { sendMessage, reconnect, getStatus } from './whatsapp.js';
import { log, randomDelay, typingDelay } from './utils.js';

const app = express();
const PORT = process.env.BOT_PORT || 3000;
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

// ── GET /status — Current WhatsApp connection status ─────────
app.get('/status', authMiddleware, (req, res) => {
  const status = getStatus();
  res.json({ success: true, ...status });
});

// ── POST /send — Send a WhatsApp message ─────────────────────
app.post('/send', authMiddleware, async (req, res) => {
  const { jid, text, delay_min = 3, delay_max = 15 } = req.body;

  if (!jid || !text) {
    return res.status(400).json({ error: 'jid and text are required' });
  }

  try {
    // Human-like delay: combine random delay + typing simulation
    const baseDelay = (delay_min * 1000) + Math.random() * ((delay_max - delay_min) * 1000);
    const typeDelay = typingDelay(text);
    const totalDelay = Math.min(baseDelay + typeDelay, delay_max * 1000);

    log('info', `Sending with ${Math.round(totalDelay / 1000)}s delay`, { jid });
    await new Promise((r) => setTimeout(r, totalDelay));

    await sendMessage(jid, text);
    res.json({ success: true, message: 'Sent', delay_ms: totalDelay });
  } catch (err) {
    log('error', 'Send failed', { error: err.message });
    res.status(500).json({ success: false, error: err.message });
  }
});

// ── POST /reconnect — Force reconnect / new QR ───────────────
app.post('/reconnect', authMiddleware, async (req, res) => {
  try {
    await reconnect();
    res.json({ success: true, message: 'Reconnect initiated' });
  } catch (err) {
    log('error', 'Reconnect failed', { error: err.message });
    res.status(500).json({ success: false, error: err.message });
  }
});

// ── Health check ─────────────────────────────────────────────
app.get('/health', (req, res) => {
  res.json({ ok: true, uptime: process.uptime() });
});

export function startServer() {
  app.listen(PORT, '127.0.0.1', () => {
    log('info', `Bot API server running on http://127.0.0.1:${PORT}`);
  });
}
