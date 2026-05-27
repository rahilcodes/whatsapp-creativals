# iChatUp Platform: Future Action, Backup, & Restore Plan

This document serves as your complete guide to **safeguarding your live system** (backing up databases, user data, and active WhatsApp logins) and outlines the **exact architectural plan** to implement Image Sending & Reading whenever you are ready in the future.

---

## 💾 Part 1: Automated Backup & Restore Strategy

Your application uses two critical pieces of state that must be backed up:
1. **SQLite Database (`app/database/database.sqlite`):** Holds all users, tenants, settings, custom AI instructions, payment credentials, and captured sales leads.
2. **WhatsApp Auth Sessions (`bot/auth_session_*`):** Holds the cryptographic tokens keeping your users logged into WhatsApp. If these are lost, all users will be forced to re-scan their QR codes.

### 1. The On-Server Backup Script
To make backups effortless, you can create a simple automated script on your Hostinger VPS. 

Create a file named `/var/www/backup.sh` on your server with the following code:
```bash
#!/bin/bash

# Define backup directory
BACKUP_DIR="/var/www/backups/backup_$(date +%F_%H-%M-%S)"
mkdir -p "$BACKUP_DIR"

echo "💾 Starting full system backup to $BACKUP_DIR..."

# 1. Backup SQLite Database
if [ -f /var/www/whatsapp-ai/app/database/database.sqlite ]; then
    cp /var/www/whatsapp-ai/app/database/database.sqlite "$BACKUP_DIR/database.sqlite"
    echo "✅ Database backed up."
else
    echo "⚠️ Warning: Database file not found."
fi

# 2. Backup Google Sheets Service Account Key
if [ -f /var/www/google-service-account.json ]; then
    cp /var/www/google-service-account.json "$BACKUP_DIR/google-service-account.json"
    echo "✅ Google Service Account Key backed up."
fi

# 3. Backup .env Configurations
if [ -f /var/www/whatsapp-ai/app/.env ]; then
    cp /var/www/whatsapp-ai/app/.env "$BACKUP_DIR/laravel.env"
    echo "✅ Laravel .env configuration backed up."
fi

# 4. Backup WhatsApp Active Login Sessions
if [ -d /var/www/whatsapp-ai/bot ]; then
    mkdir -p "$BACKUP_DIR/whatsapp_sessions"
    cp -r /var/www/whatsapp-ai/bot/auth_session_* "$BACKUP_DIR/whatsapp_sessions/" 2>/dev/null || true
    echo "✅ Active WhatsApp sessions backed up."
fi

echo "=========================================================="
echo "🎉 BACKUP COMPLETE! File saved at: $BACKUP_DIR"
echo "=========================================================="
```

#### How to run it:
Just log into your server and run:
```bash
sudo bash /var/www/backup.sh
```

---

### 2. The On-Server Restore Script
If you ever migrate to a new server, or if something breaks, you can instantly restore your entire system to a perfect working state.

Create a file named `/var/www/restore.sh` with the following code:
```bash
#!/bin/bash

# Check if a backup folder was passed as an argument
if [ -z "$1" ]; then
    echo "❌ Error: Please specify the backup folder to restore from."
    echo "Example: sudo bash restore.sh /var/www/backups/backup_2026-05-27_14-00-00"
    exit 1
fi

BACKUP_DIR="$1"

if [ ! -d "$BACKUP_DIR" ]; then
    echo "❌ Error: Backup directory $BACKUP_DIR does not exist."
    exit 1
fi

echo "🔄 Restoring system from $BACKUP_DIR..."

# Stop engines first
pm2 stop all || true

# 1. Restore SQLite Database
if [ -f "$BACKUP_DIR/database.sqlite" ]; then
    cp "$BACKUP_DIR/database.sqlite" /var/www/whatsapp-ai/app/database/database.sqlite
    echo "✅ Database restored."
fi

# 2. Restore Google Key
if [ -f "$BACKUP_DIR/google-service-account.json" ]; then
    cp "$BACKUP_DIR/google-service-account.json" /var/www/google-service-account.json
    cp "$BACKUP_DIR/google-service-account.json" /var/www/whatsapp-ai/app/storage/app/google-service-account.json
    echo "✅ Google Service Account Key restored."
fi

# 3. Restore Laravel configuration
if [ -f "$BACKUP_DIR/laravel.env" ]; then
    cp "$BACKUP_DIR/laravel.env" /var/www/whatsapp-ai/app/.env
    echo "✅ Laravel .env restored."
fi

# 4. Restore Active WhatsApp Login Sessions
if [ -d "$BACKUP_DIR/whatsapp_sessions" ]; then
    cp -r "$BACKUP_DIR/whatsapp_sessions"/* /var/www/whatsapp-ai/bot/ 2>/dev/null || true
    echo "✅ Active WhatsApp login sessions restored."
fi

# Restart engines
cd /var/www/whatsapp-ai && sudo bash deploy.sh

echo "=========================================================="
echo "🎉 SYSTEM RESTORED SUCCESSFULY AND ENGINES ONLINE!"
echo "=========================================================="
```

#### How to run it:
```bash
sudo bash /var/www/restore.sh /var/www/backups/backup_FOLDER_NAME
```

---

## 🖼️ Part 2: Future Action Plan for Image sending & reading

When you are ready in the future to implement the image features, here is the exact step-by-step roadmap to make it happen without breaking anything:

### 1. Sending Payment QR Code Images over WhatsApp
* **How it will work:**
  1. Under the hood, whenever the AI detects a user asking for payment details or a scanner (e.g. *"send me your QR code"*), it will append a hidden token `[SEND_QR]` to its text response.
  2. In your Laravel backend (`WhatsAppWebhookController.php`), we will intercept this `[SEND_QR]` token.
  3. We will strip it from the conversational text message.
  4. We will grab the absolute URL of your uploaded QR code scanner: `url($tenant->qr_code_path)`.
  5. We will pass this URL as an optional `image_url` parameter to the Node.js WhatsApp engine `/send` API endpoint.
  6. The Node.js WhatsApp engine (`bot/src/whatsapp.js`) will use Baileys' native media sender:
     ```javascript
     await sock.sendMessage(jid, { 
         image: { url: imageUrl }, 
         caption: textMessage 
     });
     ```
  7. **User Experience:** The user gets your warm conversational text message, followed instantly by your optimized UPI Scanner QR Code image directly in their chat!

### 2. Conversational Image Reading (Multimodal Vision AI)
* **How it will work:**
  1. The Node.js bot (`bot/src/whatsapp.js`) is already built to intercept incoming images, automatically download them, compress them, encode them to Base64, and POST them to your Laravel webhook under `image_payload`.
  2. In your Laravel backend (`WhatsAppWebhookController.php`), if the image is **not** a payment receipt, instead of ignoring it, we will pass it directly to `AIService::generateReply` as a multimodal payload.
  3. In `AIService.php`, when calling OpenAI's GPT-4o-mini, we will compile the messages array using OpenAI's standard **Vision Object format**:
     ```php
     $messages[] = [
         'role' => 'user',
         'content' => [
             ['type' => 'text', 'text' => $userTextMessage],
             ['type' => 'image_url', 'image_url' => ['url' => "data:image/jpeg;base64,{$imagePayload}"]]
         ]
     ];
     ```
  4. **User Experience:** If a user sends a photo of a product, a design mock-up, or a requirement document, your bot reads the image instantly and chats intelligently with them about it!
