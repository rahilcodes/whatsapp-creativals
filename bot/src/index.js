// ============================================================
// index.js — Entry point: start bot + API server
// v5.0 — Multi-Tenant Auto-Discovery (Parallel + Clean)
//   - On startup, resets all stale statuses in Laravel DB
//   - Fetches all active tenant IDs from Laravel
//   - Starts ALL tenant WhatsApp sessions in PARALLEL (no stagger)
//   - Cleans up stale auth_session_* folders automatically
// ============================================================
import 'dotenv/config';
import axios from 'axios';
import fs from 'fs';
import path from 'path';
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

// ── Wait for Laravel to be ready ─────────────────────────────
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

// ── Clean up auth_session_* folders for tenants no longer active ──
function cleanStaleSessions(activeTenantIds) {
  try {
    const cwd = process.cwd();
    const entries = fs.readdirSync(cwd).filter(f => /^auth_session_\d+$/.test(f));
    for (const dir of entries) {
      const tenantId = parseInt(dir.replace('auth_session_', ''), 10);
      if (!activeTenantIds.includes(tenantId)) {
        try {
          fs.rmSync(path.join(cwd, dir), { recursive: true, force: true });
          log('info', `🧹 Cleaned stale session folder: ${dir} (tenant no longer active)`);
        } catch (err) {
          log('warn', `Could not clean ${dir}`, { error: err.message });
        }
      }
    }
  } catch (err) {
    log('warn', 'Could not scan for stale sessions', { error: err.message });
  }
}

// ── Main init: fetch all tenants and start sessions in PARALLEL ──
async function initAllTenants() {
  const laravelReady = await waitForLaravel();

  if (!laravelReady) {
    log('warn', 'Starting default tenant (ID: 1) as fallback');
    await startWhatsApp(1);
    return;
  }

  // ── Step 1: Reset ALL stale statuses in Laravel DB ──────────
  try {
    await axios.post(`${LARAVEL_URL}/api/bot/startup-reset`, {}, {
      headers: { 'X-Bot-Secret': SHARED_SECRET },
      timeout: 5000,
    });
    log('info', '✅ Reset all tenant statuses to disconnected in DB');
  } catch (err) {
    log('warn', 'Could not reset tenant statuses', { error: err.message });
  }

  // ── Step 2: Fetch all active tenant IDs from Laravel ────────
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

  // ── Step 3: Clean up stale auth session folders ──────────────
  // Remove auth_session_* folders for tenants that are no longer active
  cleanStaleSessions(tenantIds);

  // ── Step 4: Start ALL tenant sessions IN PARALLEL ────────────
  // No more sequential 2-second stagger — every tenant starts immediately.
  // Each tenant has its own isolated socket so there's no conflict.
  log('info', `Starting ${tenantIds.length} WhatsApp session(s) in parallel...`);

  const results = await Promise.allSettled(
    tenantIds.map(tenantId => {
      log('info', `[T${tenantId}] Auto-starting WhatsApp session on boot`);
      return startWhatsApp(tenantId);
    })
  );

  const failed = results.filter(r => r.status === 'rejected');
  if (failed.length > 0) {
    log('warn', `${failed.length} tenant session(s) failed to start on boot`);
  }

  log('info', `✅ Boot complete — ${tenantIds.length - failed.length}/${tenantIds.length} sessions started`);
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
