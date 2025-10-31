# PAX Support Pro

**PAX Support Pro** — a next-generation smart WordPress support system integrating **AI assistance**, **live chat**, **tickets**, **callbacks**, and **auto-updating automation**.

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
- Menu is **fully customizable** — admin can:
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

pax-support-pro/
│
├── admin/
│ ├── admin-settings.php
│ ├── admin-console.php
│ └── admin-dashboard.php
│
├── includes/
│ ├── helpers.php
│ ├── options.php
│ └── updater.php
│
├── frontend/
│ ├── chat-ui.php
│ ├── scheduler-ui.php
│ └── knowledge-base-ui.php
│
├── assets/
│ ├── js/chat-launcher.js
│ └── css/chat-style.css
│
├── rest/
│ └── callback.php
│
└── pax-support-pro.php


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

### Version 1.1.2 (2025-10-30)
**Stable Release - Chat Modal & Dropdown Fixes**

#### Fixed
- ✅ Chat widget now opens exclusively inside modal window (not page-embedded)
- ✅ Three-dot dropdown menu displays all items correctly for logged-in users
- ✅ Dropdown menu z-index increased for better visibility above all elements
- ✅ Guest users now receive login-required alert when clicking menu button
- ✅ Guest users see disabled input with login message instead of removed chat
- ✅ WordPress 'Update Now' button appears correctly when new release is published
- ✅ Enhanced modal isolation with ESC key support for all modals
- ✅ Improved click-outside detection for dropdown menu
- ✅ Added ESC key handler to close dropdown menu
- ✅ Fixed GitHub updater metadata to ensure proper update detection

#### Enhanced
- ✅ Mobile responsive styles for dropdown menu
- ✅ All menu content loads inside modals (not main page DOM)
- ✅ Modal backdrop, animations, and click-outside functionality verified
- ✅ Added required plugin metadata fields for WordPress compatibility

### Version 1.1.1 (Previous)
- Initial release with core features

