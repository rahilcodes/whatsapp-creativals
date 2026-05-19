// ============================================================
// index.js — Entry point: start bot + API server
// v4.0 — Multi-Tenant Auto-Discovery
//   - On startup, resets all stale statuses in Laravel DB
//   - Fetches all active tenant IDs from Laravel
//   - Starts a WhatsApp session for every active tenant
// ============================================================
import 'dotenv/config';
import axios from 'axios';
import { startWhatsApp } from './whatsapp.js';
import { startServer } from './server.js';
import { log } from './utils.js';

const LARAVEL_URL   = process.env.LARAVEL_URL   || 'http://127.0.0.1:8000';
const SHARED_SECRET = process.env.SHARED_SECRET || 'whatsapp_ai_secret_2026';
const BOT_PORT      = process.env.BOT_PORT      || 3000;

log('info', '🚀 WhatsApp AI Bot starting...');
log('info', `Laravel URL: ${LARAVEL_URL}`);
log('info', `Bot Port:    ${BOT_PORT}`);

// Start the Express API server first
startServer();

// Wait for Laravel to be ready (give it a moment if starting together)
async function waitForLaravel(maxAttempts = 10, delayMs = 2000) {
  for (let i = 1; i <= maxAttempts; i++) {
    try {
      await axios.get(`${LARAVEL_URL}/up`, { timeout: 3000 });
      log('info', '✅ Laravel is ready');
      return true;
    } catch {
      log('info', `Waiting for Laravel... (attempt ${i}/${maxAttempts})`);
      await new Promise(r => setTimeout(r, delayMs));
    }
  }
  log('warn', 'Laravel not responding — starting with defaults');
  return false;
}

async function initAllTenants() {
  const laravelReady = await waitForLaravel();

  if (!laravelReady) {
    // Fall back to starting a single default session
    log('warn', 'Starting default tenant (ID: 1) as fallback');
    await startWhatsApp(1);
    return;
  }

  // ── Step 1: Reset ALL stale "connected" statuses in Laravel DB ──
  // This ensures the dashboard accurately reflects reality on every bot start
  try {
    await axios.post(`${LARAVEL_URL}/api/bot/startup-reset`, {}, {
      headers: { 'X-Bot-Secret': SHARED_SECRET },
      timeout: 5000,
    });
    log('info', '✅ Reset all tenant statuses to disconnected in DB');
  } catch (err) {
    log('warn', 'Could not reset tenant statuses', { error: err.message });
  }

  // ── Step 2: Fetch all active tenant IDs from Laravel ────────────
  let tenantIds = [];
  try {
    const resp = await axios.get(`${LARAVEL_URL}/api/tenants/active`, {
      headers: { 'X-Bot-Secret': SHARED_SECRET },
      timeout: 5000,
    });
    tenantIds = resp.data.tenant_ids || [];
    log('info', `Found ${tenantIds.length} active tenant(s)`, { tenantIds });
  } catch (err) {
    log('warn', 'Could not fetch active tenants — falling back to tenant 1', { error: err.message });
    tenantIds = [1];
  }

  // ── Step 3: Start WhatsApp for every active tenant ───────────────
  // Stagger starts by 2s to avoid hammering Baileys simultaneously
  for (const tenantId of tenantIds) {
    log('info', `[T${tenantId}] Auto-starting WhatsApp session on boot`);
    await startWhatsApp(tenantId).catch(err => {
      log('error', `[T${tenantId}] Failed to start WhatsApp`, { error: err.message });
    });
    // Small stagger between tenants to avoid rate-limits
    if (tenantIds.length > 1) {
      await new Promise(r => setTimeout(r, 2000));
    }
  }
}

initAllTenants().catch(err => {
  log('error', 'Fatal error during initialization', { error: err.message, stack: err.stack });
  process.exit(1);
});

// ── Graceful shutdown ────────────────────────────────────────
process.on('SIGINT', () => {
  log('info', 'Shutting down gracefully...');
  process.exit(0);
});

process.on('uncaughtException', (err) => {
  log('error', 'Uncaught exception', { error: err.message, stack: err.stack });
});

process.on('unhandledRejection', (reason) => {
  log('error', 'Unhandled rejection', { reason: String(reason) });
});
