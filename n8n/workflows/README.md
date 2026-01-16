# n8n Workflow Library (Specs)

This folder contains **starter specs** for the ZCC ↔ n8n integrations. The JSON files are
placeholders and are **not production-ready**. Use the specs below to build real n8n
workflows (HTTP Request nodes + cron/webhook triggers).

## Shared Requirements
- Header: `X-ZCC-Token: <shared_secret>` (from Automation settings)
- All requests are JSON (`Content-Type: application/json`)
- ZCC endpoints are documented in the root `README.md`

## ZD - Core - ZCC Callback - v1
**Purpose:** Update run status + apply entity patches.

**HTTP Request → ZCC**
- Method: `POST`
- URL: `https://<zcc-host>/automation/callback`
- Headers: `X-ZCC-Token`
- Body (JSON):
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

## ZD - Mail - Compose - v1
**Purpose:** Build a draft from a compose request.

**Input Trigger**
- Webhook or queue listener for compose requests.

**Output → ZCC Callback**
- Use the **Core Callback** payload to persist a mail draft.

## ZD - Mail - Header Sync - v1
**Purpose:** Sync IMAP headers into the ZCC cache.

**Trigger**
- Cron (e.g., every 5–10 min)

**HTTP Request → ZCC**
- Method: `POST`
- URL: `https://<zcc-host>/mail/ingest`
- Headers: `X-ZCC-Token`
- Body (JSON):
```json
{
  "index": [
    { "uid": "123", "from": "alice@example.com", "subject": "Hi", "date": "ISO" }
  ],
  "drafts": [
    { "id": "draft-1", "subject": "...", "body_text": "...", "body_html": "...", "updated_at": "ISO" }
  ]
}
```

## ZD - Shopware - Refresh - v1
**Purpose:** Push Shopware cache into ZCC.

**Trigger**
- Webhook or cron (Shopware → n8n)

**HTTP Request → ZCC**
- Method: `POST`
- URL: `https://<zcc-host>/shopware/ingest`
- Headers: `X-ZCC-Token`
- Body (JSON):
```json
{
  "metrics": { "orders_today": 5, "revenue_today": 1200.50, "last_updated": "ISO" },
  "orders": [
    { "number": "10001", "customer": "Max M.", "amount": 99.99, "status": "paid", "time": "ISO" }
  ]
}
```

## ZD - Backup - Rotation - v1
**Purpose:** Run backup schedule and rotate uploads in Nextcloud.

**Trigger**
- Cron (daily)

**Suggested Steps**
1. Call ZCC Backup UI endpoint (or direct internal route if added later).
2. Upload to Nextcloud WebDAV.
3. Retention (daily/weekly/monthly).
