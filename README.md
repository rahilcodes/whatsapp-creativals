# WhatsApp AI Assistant (Localhost SaaS)

A production-grade, locally hosted WhatsApp AI assistant built with **Node.js (Baileys)** for WhatsApp connectivity and **Laravel 11** for the dashboard, AI logic, and memory management.

## Features
- **Inbound Only:** Only replies to messages, never initiates.
- **Human-like delays:** Simulates typing speed based on message length.
- **Safety checks:** Respects working hours, rate limits (per-user & global), and stops AI if human takeover keywords are triggered.
- **AI Memory:** Maintains short-term memory (recent chat), auto-generates long-term memory (user summaries), and uses a business knowledge base for context.
- **Dashboard:** Premium SaaS-style dashboard to monitor live status, view chats, configure settings, and manage business memory.

## Prerequisites
Ensure you have the following installed:
1. **PHP 8.2+** and **Composer**
2. **Node.js 18+** and **npm**
3. Open a terminal in the project root.

---

## 🚀 Setup Guide

### 1. Configure the Laravel Backend

1. Navigate to the `app/` directory:
   ```bash
   cd app
   ```
2. Open the `.env` file and **add your OpenAI API Key**:
   ```env
   OPENAI_API_KEY=sk-your-real-key-here
   ```
   *(Optional) You can also change the `SHARED_SECRET` in both `app/.env` and `bot/.env` if you want extra security.*

3. The SQLite database and migrations have already been set up for you.

4. Start the Laravel development server:
   ```bash
   php artisan serve
   ```
   *Leave this terminal running. The dashboard is now available at `http://127.0.0.1:8000`.*

---

### 2. Configure the Node.js WhatsApp Bot

1. Open a **new** terminal window and navigate to the `bot/` directory:
   ```bash
   cd bot
   ```

2. Start the Node.js bot:
   ```bash
   npm start
   ```
   *Leave this terminal running.*

---

### 3. Connect WhatsApp

1. Open your browser and go to the dashboard: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**
2. A QR code will appear on the dashboard (it is also printed in the Node.js terminal).
3. Open WhatsApp on the phone you want to use as the bot.
   > **Warning:** It is highly recommended to use a dedicated business number, not your personal number.
4. Go to **Settings > Linked Devices > Link a Device** and scan the QR code.
5. The dashboard will instantly update to show **"Connected ✓"**.

---

## 🛠️ Usage

- **Send a message:** Text the bot number from another phone. You will see the incoming message in the dashboard's Activity Feed.
- **Chats:** Go to the **Chats** tab to view conversations. You can manually pause the AI for specific users (Human Takeover) or clear their memory.
- **Business Memory:** Go to the **Business Memory** tab to add your services, pricing, menus, and FAQs. The AI uses this to answer customer questions accurately.
- **Settings:** Go to the **AI Settings** tab to configure working hours, response delays, the main system prompt, and trigger keywords.

## 📁 Architecture
- **`bot/` (Node.js):** Connects to WhatsApp via `@whiskeysockets/baileys`. Listens for messages and POSTs them to Laravel. Exposes a small Express API on port 3000 to receive "send message" commands from Laravel.
- **`app/` (Laravel):** Receives webhooks from Node. Runs all safety checks (rate limits, working hours, duplicates). Fetches memory. Calls OpenAI. Sends the generated reply back to the Node API. Serves the dashboard UI.
