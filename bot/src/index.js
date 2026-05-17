// ============================================================
// index.js — Entry point: start bot + API server
// ============================================================
import 'dotenv/config';
import { startWhatsApp } from './whatsapp.js';
import { startServer } from './server.js';
import { log } from './utils.js';

log('info', '🚀 WhatsApp AI Bot starting...');
log('info', `Laravel URL: ${process.env.LARAVEL_URL || 'http://127.0.0.1:8000'}`);
log('info', `Bot Port:    ${process.env.BOT_PORT || 3000}`);

// Start the Express API server first
startServer();

// Then connect to WhatsApp
startWhatsApp().catch((err) => {
  log('error', 'Fatal error starting WhatsApp', { error: err.message });
  process.exit(1);
});

// Graceful shutdown
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
