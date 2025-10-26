# PAX Support Pro

**PAX Support Pro**  a next-generation smart WordPress support system integrating **AI assistance**, **live chat**, **tickets**, **callbacks**, and **auto-updating automation**.

A full-featured plugin for modern support centers, built for speed, stability, and dynamic control.

---

## ⚙️ Core Features

### 🧩 System Architecture
- Fully modular design integrated with WordPress 6.7+.
- Auto-installer creates all required tables:
  - `wp_pax_tickets`
  - `wp_pax_logs`
  - `wp_pax_settings`
  - `wp_pax_schedules`
- Auto-repair for missing tables after migration.
- Role-based access:
  - **pax_agent**, **pax_manager**, **pax_viewer**.
- Smart environment checks (PHP, cURL, mbstring).

---

## 💬 Chat & Ticket System

- Dynamic chat launcher with **glass-neon design**.
- All menu buttons fully functional:
  - Chat, New Ticket, Help Center, Troubleshooter, Diagnostics, Callback, Order Lookup, My Requests, Feedback, Donate, Server Status.
- Menu is **fully customizable**  admin can:
  - Add, rename, reorder, or remove buttons.
  - Assign custom icons (emoji/SVG) and URLs.
  - Control visibility (user/admin/both).
- Smart cooldown system:
  - Users cannot open a new ticket until cooldown expires (default: 3 days).
  - Option to delete or close ticket early.
- Direct messaging from admin console.
- Freeze / Unfreeze / Transfer tickets.
- Internal notes (admin-only).

---

## 🧠 AI & Automation

- Integrated **OpenAI** assistant (configurable API key + model).
- Multilingual support (auto-detect via browser or WPML).
- Smart Triggers:
  - Detects keywords like “refund” or “error” and suggests related articles.
- AI-powered article suggestions from built-in knowledge base.
- Smart context caching for faster responses.
- Auto-close inactive tickets after X days.
- Daily cleanup via WP-Cron.

---

## 🗓️ Smart Scheduler

- Built-in callback scheduler.
- Admin defines working hours and available agents.
- Sync with user timezone.
- Optional Google Calendar integration.
- Smart reminders + alerts.

---

## 🎨 Customization & Design

- Modern, minimal dark interface.
- **Admin settings panel** includes:
  - Real-time color pickers with live preview.
  - Animation style (Fade, Slide, Pop, Zoom, Bounce).
  - Launcher shape (Circle, Square, Emoji).
  - Emoji picker for launcher icon.
  - Adjustable animation speed and typing delay.
  - Custom link manager for buttons.
- Live Preview block inside settings.
- Fully responsive, optimized for mobile.
- GPU-accelerated transitions for smooth animations.

---

## 🔔 Smart Alerts & Robot UI

- AI robot animation during chat typing.
- Smart Toast notifications for all user actions.
- Online/Offline detector displayed in chat header.
- Configurable alert sound and animation speed.

---

## 🧑‍💼 Admin Console & Dashboard

- Dedicated console for all tickets and callbacks.
- Search, sort, and filter tickets.
- Quick-reply and freeze/unfreeze controls.
- Real-time agent analytics.
- Performance and error logs.
- **Dashboard widget** showing:
  - Ticket counts.
  - Server metrics.
  - OpenAI status.
- Export logs and stats as CSV.

---

## 🔐 Security & Privacy

- Anti-spam firewall:
  - Rate limit (5/hour/IP) on REST callbacks.
  - Behavioral pattern detection.
- Cloudflare Turnstile / reCAPTCHA integration.
- GDPR data center:
  - Data download/delete.
  - Retention period control.
- Smart IP blocking.

---

## 💾 Backup & Auto-Updater

- Local + Cloud backup (Google Drive / Dropbox).
- Automatic version check via GitHub API.
- One-click update or auto-update.
- Version signature verification.
- Rollback on failure.
- Full update logs.
- Configurable check frequency (daily or weekly).

---

## ⚙️ Performance

- “Super Speed Mode” with lazy loading and minimal assets.
- Optimized REST responses.
- Asset version control via `wp_enqueue_script()` and `wp_enqueue_style()`.
- Custom animation profiles (5 presets).

---

## 🧰 Developer Tools

- Fully compatible with WP hooks and filters.
- Extensible REST API endpoints.
- Modular file structure:
  - `admin/`
  - `includes/`
  - `assets/js`
  - `assets/css`
  - `frontend/`
  - `rest/`
- Integrated error reporting and logging.

---

## 🖥️ Installation

### Manual:
1. Upload `pax-support-pro` to `/wp-content/plugins/`.
2. Activate from **Plugins → Installed Plugins**.
3. Visit **Settings → PAX Support Pro**.
4. Configure colors, animations, AI key, and menus.

### Auto Installer:
When activated:
- Creates all required database tables.
- Inserts default settings and roles.
- Loads default menu items automatically.

---

## 🧩 File Structure

pax-core-suite/
│
├── bootstrap/
│   ├── autoload.php
│   ├── kernel.php
│   └── init-hooks.php
│
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   ├── SyncTickets.php
│   │   │   ├── CleanLogs.php
│   │   │   └── UpdateAIModel.php
│   │   └── Kernel.php
│   ├── Events/
│   │   ├── TicketCreated.php
│   │   ├── ChatMessageReceived.php
│   │   └── SystemHealthCheck.php
│   ├── Exceptions/
│   │   ├── Handler.php
│   │   └── InvalidPayloadException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── TicketController.php
│   │   │   │   ├── AIController.php
│   │   │   │   └── UserController.php
│   │   │   └── Web/
│   │   │       ├── DashboardController.php
│   │   │       └── SettingsController.php
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php
│   │   │   ├── VerifyCsrfToken.php
│   │   │   └── Localize.php
│   │   └── Routes/
│   │       ├── api.php
│   │       └── web.php
│   ├── Models/
│   │   ├── Ticket.php
│   │   ├── User.php
│   │   ├── Setting.php
│   │   └── AI_Log.php
│   ├── Services/
│   │   ├── AI/
│   │   │   ├── Engine.php
│   │   │   ├── NLPAdapter.php
│   │   │   ├── PromptManager.php
│   │   │   └── SentimentAnalyzer.php
│   │   ├── Cache/
│   │   │   ├── RedisCache.php
│   │   │   └── TransientCache.php
│   │   ├── Notifications/
│   │   │   ├── EmailNotifier.php
│   │   │   ├── SlackNotifier.php
│   │   │   └── WebhookNotifier.php
│   │   └── Security/
│   │       ├── Encryption.php
│   │       ├── Firewall.php
│   │       └── AccessControl.php
│   └── Support/
│       ├── Helpers.php
│       ├── Arr.php
│       ├── Str.php
│       └── Validator.php
│
├── config/
│   ├── ai.php
│   ├── app.php
│   ├── cache.php
│   ├── database.php
│   ├── logging.php
│   ├── mail.php
│   └── security.php
│
├── database/
│   ├── migrations/
│   │   ├── 2025_10_01_create_tickets_table.php
│   │   ├── 2025_10_01_create_ai_logs_table.php
│   │   └── 2025_10_01_create_settings_table.php
│   ├── seeders/
│   │   ├── DemoUserSeeder.php
│   │   └── DefaultSettingsSeeder.php
│   └── factories/
│       └── TicketFactory.php
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── master.blade.php
│   │   │   └── auth.blade.php
│   │   ├── dashboard/
│   │   │   ├── index.blade.php
│   │   │   └── analytics.blade.php
│   │   ├── chat/
│   │   │   ├── widget.blade.php
│   │   │   └── conversation.blade.php
│   │   └── emails/
│   │       └── ticket-notification.blade.php
│   ├── lang/
│   │   ├── en/
│   │   │   └── messages.php
│   │   ├── ar/
│   │   │   └── messages.php
│   │   └── de/
│   │       └── messages.php
│   ├── css/
│   ├── js/
│   └── images/
│
├── tests/
│   ├── Feature/
│   │   ├── ApiTest.php
│   │   ├── AITest.php
│   │   └── AuthTest.php
│   └── Unit/
│       ├── EncryptionTest.php
│       ├── ModelTest.php
│       └── CacheTest.php
│
├── scripts/
│   ├── deploy.sh
│   ├── clear-cache.sh
│   ├── optimize.sh
│   └── monitor.php
│
├── storage/
│   ├── logs/
│   │   └── system.log
│   ├── cache/
│   └── ai-models/
│
├── vendor/
│
├── composer.json
├── pax-core-suite.php
├── README.md
└── LICENSE




---

## 🧷 License


---

## 🧷 License

This project is licensed under the **GPL v3.0** License.  
© 2025 Ahmad Al Khalaf (PlayArcadiaX).

---

## 🌐 Developer Support

For direct support or contributions:
- Developer: **Ahmad Al Khalaf**
- Website: [https://playarcadiax.com](https://playarcadiax.com)
- GitHub: [https://github.com/Black10998/PAX](https://github.com/Black10998/PAX)
- Support & donations: [https://www.paypal.me/AhmadAlkhalaf29](https://www.paypal.me/AhmadAlkhalaf29)

## 🧾 Changelog

