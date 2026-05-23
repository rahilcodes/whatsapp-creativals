// ============================================================
// pm2.config.js — PM2 Process Manager Configuration
// ============================================================
// USAGE:
//   First time setup:  pm2 start pm2.config.js
//   Save process list: pm2 save
//   Auto-start on boot: pm2 startup  (follow the printed command)
//
// Other commands:
//   pm2 status          — View bot status
//   pm2 logs ichatup-bot — View live logs
//   pm2 restart ichatup-bot — Restart bot
//   pm2 stop ichatup-bot    — Stop bot
// ============================================================

export default {
  apps: [
    {
      name: 'ichatup-bot',
      script: 'src/index.js',

      // Run as ES module
      interpreter: 'node',
      interpreter_args: '',

      // Single instance (WhatsApp bots must not run in cluster mode)
      instances: 1,
      exec_mode: 'fork',

      // Auto-restart on crash
      autorestart: true,
      max_restarts: 10,
      restart_delay: 4000, // Wait 4s between restarts to avoid rate limits
      min_uptime: '10s',   // Must be running 10s before restart counter resets

      // Memory limit — restart if bot leaks above 512MB
      max_memory_restart: '512M',

      // File watching (disabled — use manual restart)
      watch: false,

      // Log files
      error_file: 'logs/pm2-error.log',
      out_file: 'logs/pm2-out.log',
      merge_logs: true,
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',

      // Environment
      env: {
        NODE_ENV: 'production',
      },
      env_development: {
        NODE_ENV: 'development',
      },
    },
  ],
};
