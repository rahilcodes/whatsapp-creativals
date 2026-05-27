import { sendMessage, getStatus } from './whatsapp.js';
import { log, typingDelay } from './utils.js';

// ── In-Memory Queue State ─────────────────────────────────────
// Each item includes tenantId so the worker routes to the right socket
const queue = [];
let isWorkerRunning = false;
let messageCounter  = 0;

// Configuration
const MAX_ATTEMPTS      = 3;
const RETRY_BACKOFF_MS  = 10000; // 10 seconds between retries
const WORKER_INTERVAL_MS = 2000; // Worker checks queue every 2s

// ── Add a message to the queue ────────────────────────────────
export function enqueueMessage(jid, text, messageId, delayMin, delayMax, tenantId = 1, imageUrl = null) {
  const id = messageId || `msg_${++messageCounter}_${Date.now()}`;

  queue.push({
    id,
    jid,
    text,
    tenantId,
    delayMin:    delayMin || 3,
    delayMax:    delayMax || 15,
    attempts:    0,
    nextRetryAt: Date.now(),
    status:      'pending', // pending | processing | failed
    imageUrl,
  });

  log('info', `[T${tenantId}] Message enqueued [${id}] for ${jid.split('@')[0]}`, { queueLength: queue.length });

  if (!isWorkerRunning) {
    startWorker();
  }

  return id;
}

// ── Background Worker Loop ────────────────────────────────────
async function startWorker() {
  if (isWorkerRunning) return;
  isWorkerRunning = true;

  log('info', 'Message queue worker started');

  while (true) {
    const now = Date.now();

    // Find the next eligible pending message (any tenant)
    const nextMsgIndex = queue.findIndex(msg =>
      msg.status === 'pending' && msg.nextRetryAt <= now
    );

    if (nextMsgIndex !== -1) {
      const msg      = queue[nextMsgIndex];
      const tenantId = msg.tenantId;

      // Check this specific tenant's session status
      const { status: sessionStatus } = getStatus(tenantId);

      if (sessionStatus === 'connected') {
        msg.status = 'processing';

        try {
          // Simulate human delays on first attempt
          if (msg.attempts === 0) {
            const initialDelay = Math.floor(
              Math.random() * (msg.delayMax * 1000 - msg.delayMin * 1000 + 1)
            ) + msg.delayMin * 1000;
            const typeDelay  = typingDelay(msg.text);
            const totalDelay = initialDelay + typeDelay;
            log('info', `[T${tenantId}] Queue [${msg.id}]: Simulating human typing (${Math.round(totalDelay / 1000)}s)`);
            await new Promise(r => setTimeout(r, totalDelay));
          }

          // Double-check connection right before sending
          if (getStatus(tenantId).status === 'connected') {
            msg.attempts++;
            await sendMessage(msg.jid, msg.text, tenantId, msg.imageUrl);
            log('info', `[T${tenantId}] Queue [${msg.id}]: Delivered successfully`);
            queue.splice(nextMsgIndex, 1);
          } else {
            throw new Error('Connection dropped before send');
          }
        } catch (err) {
          msg.status = 'pending';

          if (msg.attempts >= MAX_ATTEMPTS) {
            log('error', `[T${tenantId}] Queue [${msg.id}]: Failed permanently after ${msg.attempts} attempts`, { error: err.message });
            msg.status = 'failed';
            queue.splice(nextMsgIndex, 1);
          } else {
            msg.nextRetryAt = Date.now() + RETRY_BACKOFF_MS;
            log('warn', `[T${tenantId}] Queue [${msg.id}]: Send failed, retry #${msg.attempts + 1} in ${RETRY_BACKOFF_MS / 1000}s`, { error: err.message });
          }
        }
      } else {
        // Session not connected — re-schedule this message and move on
        if (queue.length > 0) {
          log('warn', `[T${tenantId}] Queue Worker: session is ${sessionStatus}, skipping message [${msg.id}]`);
        }
      }
    }

    // Sleep before next cycle
    await new Promise(r => setTimeout(r, WORKER_INTERVAL_MS));
  }
}
