# zcc

## Installation (lokal / Plesk kompatibel)

Dieses Repository enthält ein minimales Core-Gerüst. Um es lokal oder in einer Plesk-ähnlichen Umgebung zu starten, brauchen Sie nur PHP mit Webserver (oder PHP built-in Server). Die Core-Dateien liegen unter `public/` als Webroot.

### Voraussetzungen
- PHP 8.1+ (mit `json`, `openssl`, `session` aktiviert)
- Webserver (Apache/Nginx) **oder** PHP built-in Server
- Schreibrechte für `storage/` (Module-Registry, Logs, Backups später)

### Lokaler Start (Dev)
```bash
php -S 0.0.0.0:8000 -t public
```
Danach öffnen: `http://localhost:8000`

### Plesk / Shared Hosting (ZIP-Upload)
1. ZIP des Projekts in das Webroot entpacken.
2. Webroot auf `public/` setzen (DocumentRoot).
3. Schreibrechte auf `storage/` sicherstellen.

### Login
Standard-Login (nur für den Start gedacht):
- Benutzer: `admin`
- Passwort: `admin`

### Hinweise zum Modul-System
- Modul-Routing läuft über `m.php?m=<key>`.
- Aktivierte Module werden aus `storage/modules.json` geladen.


# 🧠 ZenityDent Control Center (ZCC) — Master-Prompt (Core + Module-System + n8n-first)

> Zweck: Dieser Prompt beschreibt **das komplette Zielsystem** für ein eigenständiges „ZenityDent Control Center“ (ZCC) als internes Control Center.
> Fokus: **Stabilität, Modularität, n8n als Motor**, Nextcloud als Filesystem, OVH Zimbra Mail (IMAP/SMTP) rate-limit-schonend, Shopware 6 als Quelle.
>
> Leitprinzip: **Core bleibt klein & unzerstörbar**. Alles Fachliche passiert über **Module**.

---

## 0) NICHT VERHANDELBAR — Systemregeln

1. **Core-Dateien dürfen von Modulen niemals verändert werden.**
2. **Module dürfen den Core nicht „kaputt machen“** (keine fatal errors, kein Breaking-Update).
3. **Deterministisch**: keine Auto-Discovery, keine dynamischen Includes; alles explizit registriert.
4. **Sichere Defaults**:
   - Registrierung standardmäßig deaktiviert
   - Darkmode vorhanden (Theme/UX)
   - CSRF-Schutz bei allen Forms
5. **Core Updates nur über „Core Updater“** (UI) mit Backup+Rollback.
6. **Module Updates nur über `/modules.php`** (ZIP Upload).
7. **Namespaces/keine globalen Funktionsnamen** (keine Redeclare-Probleme).

---

## 1) Ziel-Architektur (Übersicht)

### Rollen
- `admin`: alles
- `staff`: lesen/arbeiten (konfigurierbar)
- optionale weitere Rollen

### Kernkomponenten
- **Core** (minimal, stabil)
- **Modul-System** (ZIP Upload, aktivieren/deaktivieren, Menü aus `module.json`)
- **Automation Center** (n8n-first)
- **Nextcloud** (WebDAV) als zentrales Filesystem
- **Mail** (OVH Zimbra) IMAP/SMTP, aber **header-only + caching**
- **Shopware 6** als Quelle (Orders/Metrics)

---

## 2) Core (neu bauen/neu definieren) — Umfang & Dateien

### 2.1 Core Verantwortlichkeiten (MUSS)
- Installer Flow (DB Setup, Admin-Account, installed.lock)
- Auth/Login/Session
- Roles & Permissions (Capabilities)
- Settings (Registration toggle, Theme toggle, Base URLs, Secrets)
- Audit Logger (systemweit)
- Modul-Registry (DB) + Modul Loader (m.php Router)
- **Core Updater** (Hotfix ZIP Whitelist + Backup + Rollback)
- Theme Shell (Layout, Sidebar, Submenus, Darkmode Tokens)
- Sicherheitsbasics:
  - CSRF
  - Rate limit für Login + API
  - Secure headers (best effort)
  - Path traversal protection helper

### 2.2 Core darf NICHT
- Business Logik enthalten
- Module-Dateien patchen
- n8n/Nextcloud/Mail/Shopware spezifische UI enthalten (das ist Modul-Sache)

### 2.3 Installer-Flow
- Schritt 1: DB Credentials + Connection Test
- Schritt 2: Tabellen anlegen + Seed (Core Module + Admin)
- Schritt 3: Admin erstellen
- Schritt 4: `installed.lock` schreiben
- Danach redirect auf Login

### 2.4 Core Updater (MUSS)
- UI Seite (Admin-only)
- Upload ZIP (Whitelist):
  - `vendor-lite.php`
  - `m.php`
  - `app/Core/*.php`
- Vor Patch: Backup ZIP unter `/storage/core-backups/`
- Rollback: Restore aus Backup ZIP

---

## 3) Modul-System (ZIP Upload) — Standard

### ZIP Struktur
- `module.json` **im Root** der ZIP
- `index.php` im Root (Entry der Modul-UI)
- optional `pages/`, `assets/`, `src/`

### module.json (Standardfelder)
- `key`, `name`, `version`, `description`
- `permissions`: array
- `menu`: `[{ "title": "...", "url": "/m.php?m=<key>" , "group": "...", "icon": "..." }]`
- optional: `submenu`: array von menu items

### Module Loader
- `/m.php?m=<key>` routet auf aktiviertes Modul `index.php`
- Wenn Modul deaktiviert: „Module disabled“

---

## 4) Menü-Struktur (FINAL beschlossen) — mit Untermenüs

**Top-Level:**
- Dashboard
- Automation
- Mail
- Shopware
- Nextcloud
- Business
- System

**Untermenüs:**
- **Automation**
  - Workflows
  - Runs & Logs
  - Events
  - Settings
- **Mail**
  - Inbox
  - Compose (KI)
  - Drafts
  - Settings
- **Shopware**
  - Overview
  - Orders (read-only)
  - Metrics
- **Nextcloud**
  - Browser
  - Uploads
  - Settings
- **Business (light)**
  - Customers
  - Invoices (Registry)
  - Shipping (Status)
- **System**
  - Users & Roles
  - Permissions
  - Audit Log
  - Modules
  - Core Updates
  - Settings

### Menü-Skizze (Sidebar)
```text
[Dashboard]
[Automation ▸]
   - Workflows
   - Runs & Logs
   - Events
   - Settings
[Mail ▸]
   - Inbox
   - Compose (KI)
   - Drafts
   - Settings
[Shopware ▸]
   - Overview
   - Orders
   - Metrics
[Nextcloud ▸]
   - Browser
   - Uploads
   - Settings
[Business ▸]
   - Customers
   - Invoices
   - Shipping
[System ▸]
   - Users & Roles
   - Permissions
   - Audit Log
   - Modules
   - Core Updates
   - Settings
```

---

## 5) Foundation Module: Nextcloud Extended (WebDAV)

### Ziel
Nextcloud ist das **zentrale Filesystem** für:
- Mail Anhänge
- Rechnungs-PDFs
- Backups
- Labels/Shipping docs

### Root & Auto-Struktur
Root: `/ZenityDent` (konfigurierbar)
Auto anlegen:
- `/ZenityDent/customers`
- `/ZenityDent/orders`
- `/ZenityDent/invoices`
- `/ZenityDent/backups`

### MVP Funktionen
- list / mkdir / upload / download
- minimaler Browser (Ordnernavigation)
- später: Preview (PDF/Bilder)
- File Picker API für andere Module

### Schutz
- nur innerhalb Root
- keine `..` / Traversal
- klarer Fehleroutput (401/403/timeout)

---

## 6) Automation Center v3 (n8n-first) — Herzstück

### Ziel
ZCC steuert, n8n führt aus. ZCC speichert:
- Registry (welche Workflows existieren)
- Runs/Logs (Status, Fehler, Zeiten)
- Events (welche Events existieren, Mapping zu Workflows)

### UI Skizze
- **Automation → Workflows**: Liste + Start/DryRun + Konfigurieren
- **Automation → Runs & Logs**: Run-Historie + Details
- **Automation → Events**: Event Registry (readonly) + Zuordnung
- **Automation → Settings**: base URL, shared secret, timeouts

### Security (MVP)
- `shared_secret` selbst generiert (nicht von n8n)
- Header: `X-ZCC-Token: <shared_secret>`

### Security (v2)
- HMAC + timestamp + replay Schutz

---

## 7) Workflow-Library (n8n) — Final Struktur

### Namensschema
`ZD - <Domain> - <Action> - v1`

### Ordner
```text
ZenityDent Library
 ├─ 00 - Core
 ├─ 10 - Shopware
 ├─ 20 - Mail
 ├─ 30 - Nextcloud
 ├─ 40 - Invoice (Light)
 ├─ 50 - Shipping (Light)
 ├─ 60 - Reporting
 ├─ 70 - Health & Ops
 └─ 90 - Experiments
```

### Core Workflows (00 - Core)
- `ZD - Core - Config - v1`
- `ZD - Core - ZCC Callback - v1`
- `ZD - Core - Run Logger - v1`
- `ZD - Core - Idempotency Guard - v1`

---

## 8) Standard Payloads (ZCC ↔ n8n)

### ZCC → n8n (Trigger)
```json
{
  "event": "mail.compose.requested",
  "request_id": "uuid",
  "idempotency_key": "string",
  "requested_by": { "user_id": 1, "role": "admin" },
  "language": "de|en|fr|nl",
  "entity": { "type": "customer|invoice|order|mail", "id": "..." },
  "meta": { "tone": "friendly|pro|short", "goal": "reply|offer|reminder" },
  "data": {}
}
```

### n8n → ZCC (Callback)
```json
{
  "request_id": "uuid",
  "run": {
    "workflow_key": "mail-compose",
    "status": "success|error|running",
    "started_at": "ISO",
    "finished_at": "ISO",
    "execution_id": "n8n-id",
    "error": { "message": "...", "code": "..." }
  },
  "updates": [
    {
      "entity": { "type": "mail_draft", "id": "..." },
      "patch": { "subject": "...", "body_text": "...", "body_html": "..." }
    }
  ]
}
```

---

## 9) Shopware 6 Integration (n8n Pipeline + ZCC Widget)

### Prinzip
- n8n pollt Shopware oder nutzt Webhooks
- n8n schreibt komprimierte Daten in ZCC Cache-Tabellen
- ZCC Dashboard zeigt Widget (ohne Shopware live zu belasten)

### Widget (MVP)
- Bestellungen heute (count)
- Umsatz heute (sum)
- letzte 5 Bestellungen (Nr, Kunde, Betrag, Zeit, Status)
- Button „Refresh“ → triggert n8n

---

## 10) Mail Center (OVH Zimbra) — rate-limit-schonend + KI Compose

### MVP IMAP Strategie
- **Header only** fürs Listing
- Pagination (30er Seiten)
- UID incremental sync
- Caching in DB (mail_index)
- Throttling (min. 60s zwischen Syncs)

### Bonus (MUSS rein)
- n8n übernimmt regelmäßigen IMAP Header Sync (Cron)
- ZCC lädt Body/Attachments nur on-demand

### KI Compose/Reply (über n8n)
- Nutzer tippt Stichpunkte + Ton + Ziel + Sprache (DE/EN/FR/NL)
- n8n generiert Draft (JSON subject/body)
- ZCC zeigt Draft zur Freigabe
- Versand bevorzugt über n8n (besseres Logging/Automationen)

---

## 11) Business „Light“ (optional, minimal)

Ziel: keine ERP-Logik, nur Registry/Status für Übersicht & Automationen.
- Customers minimal (Name, Email, Nextcloud folder)
- Invoices Registry (Status + PDF path)
- Shipping Status (Tracking + status)

Alles „schwere“ macht n8n.

---

## 12) Backup Manager v2 (Backups in Nextcloud)

- DB Dump (sql.gz)
- Upload nach `/ZenityDent/backups/db/YYYY-MM-DD/...`
- Rotation (daily/weekly/monthly)
- Audit logging
- Restore nur später & stark abgesichert

---

## 13) Umsetzungs-Reihenfolge (Bundles)

### Bundle 1 — Foundation
- Nextcloud Extended (WebDAV + Struktur + minimal browser)
- Theme/UX stabil (submenu support)
- Core Stabilität (Audit/Permissions/Modules/CoreUpdater)

### Bundle 2 — Automation Center v3 (voll)
- Workflows UI + Runs + Events + Settings
- Callback Endpoint + Security

### Bundle 3 — Mail (lean) + KI Compose
- Header Sync (n8n) + Inbox UI (cache)
- Compose/Reply via n8n + Drafts

### Bundle 4 — Shopware Widget + Cache
- Orders/Metrics Pipeline in n8n
- Dashboard Widget in ZCC

### Bundle 5 — Backup Manager v2
- DB Dumps → Nextcloud

---

## 14) Definition of Done (DoD)

Ein Bundle gilt als fertig, wenn:
- keine 500er
- Modul aktivierbar/deaktivierbar
- Audit schreibt Events
- Permissions greifen
- bei externen Services: klare Fehlermeldungen (nicht crash)
- UI bleibt nutzbar & schnell

---

## 15) Output-Anforderung an den Entwickler/Agenten

- Liefere ZCC als ZIP (Plesk kompatibel)
- Module als einzelne ZIPs (Root: module.json)
- SQL Schema/Installer updates sind enthalten
- Dokumentiere:
  - Settings Keys
  - n8n required headers
  - Workflow keys + Kategorien
  - Menügruppen/submenus

---

**Ende.**
