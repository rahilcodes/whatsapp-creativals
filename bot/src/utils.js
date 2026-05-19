// ============================================================
// utils.js — Shared helpers for the WhatsApp AI Bot
// ============================================================

/**
 * Returns a promise that resolves after a random delay between min and max ms.
 */
export function randomDelay(minMs = 3000, maxMs = 15000) {
  const delay = Math.floor(Math.random() * (maxMs - minMs + 1)) + minMs;
  return new Promise((resolve) => setTimeout(resolve, delay));
}

/**
 * Computes a realistic typing delay based on message length.
 * ~40 WPM average human typing speed.
 */
export function typingDelay(text) {
  const words = text.trim().split(/\s+/).length;
  const wpm = 40 + Math.floor(Math.random() * 20); // 40–60 WPM
  const ms = (words / wpm) * 60 * 1000;
  return Math.min(Math.max(ms, 2000), 12000); // Clamp 2s–12s
}

/**
 * Structured logger with timestamp.
 */
export function log(level, message, data = {}) {
  const ts = new Date().toISOString();
  const prefix = {
    info: '\x1b[36m[INFO]\x1b[0m',
    warn: '\x1b[33m[WARN]\x1b[0m',
    error: '\x1b[31m[ERROR]\x1b[0m',
    debug: '\x1b[90m[DEBUG]\x1b[0m',
  }[level] || '[LOG]';
  const extra = Object.keys(data).length ? ' ' + JSON.stringify(data) : '';
  console.log(`${ts} ${prefix} ${message}${extra}`);
}

/**
 * Extract plain text from a Baileys message object.
 */
export function extractText(msg) {
  const m = msg.message;
  if (!m) return null;
  return (
    m.conversation ||
    m.extendedTextMessage?.text ||
    m.imageMessage?.caption ||
    m.videoMessage?.caption ||
    m.buttonsResponseMessage?.selectedDisplayText ||
    m.listResponseMessage?.title ||
    null
  );
}

/**
 * Normalize a JID to a plain phone number.
 */
export function jidToPhone(jid) {
  return jid?.replace(/@s\.whatsapp\.net$/, '').replace(/@g\.us$/, '').replace(/@lid$/, '') || '';
}

/**
 * Check if a JID belongs to a group.
 */
export function isGroupJid(jid) {
  return jid?.endsWith('@g.us') || false;
}
