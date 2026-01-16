# n8n Setup (Step-by-Step)

This guide explains how to install n8n and import the ZCC workflows.

## 1) Install n8n (Docker)
```bash
docker volume create n8n_data
docker run -it --rm \\
  --name n8n \\
  -p 5678:5678 \\
  -e N8N_BASIC_AUTH_ACTIVE=true \\
  -e N8N_BASIC_AUTH_USER=admin \\
  -e N8N_BASIC_AUTH_PASSWORD=admin \\
  -e TZ=Europe/Berlin \\
  -v n8n_data:/home/node/.n8n \\
  n8nio/n8n
```

Open: `http://localhost:5678`

## 2) Configure env vars (for ZCC calls)
In the n8n UI:
1. **Settings → Environment Variables**
2. Add:
   - `ZCC_BASE_URL` = `https://<your-zcc-host>`
   - `ZCC_SHARED_SECRET` = (from ZCC Automation settings)

## 3) Import workflows
1. Go to **Workflows → Import from File**.
2. Import all JSON files from `n8n/workflows/`.
3. Keep them **inactive** until endpoints are reachable.

## 4) Update credentials in each workflow
For workflows that call external services (Mail IMAP / Shopware / Nextcloud):
1. Add appropriate credentials (IMAP, HTTP, WebDAV, etc.) in n8n.
2. Open each workflow and connect credentials to its nodes.

## 5) Activate the workflows
Once ZCC is reachable and secrets are configured:
1. Activate `ZD - Mail - Header Sync - v1`
2. Activate `ZD - Shopware - Refresh - v1`
3. Activate `ZD - Backup - Rotation - v1`
4. Activate `ZD - Core - Run Logger - v1`
5. Activate `ZD - Mail - Compose - v1`

## 6) Test calls
Use “Execute Workflow” to verify:
- `/automation/callback` responds `200 OK`
- `/mail/ingest` responds `200 OK`
- `/shopware/ingest` responds `200 OK`

---

If you prefer Docker Compose, create a `docker-compose.yml` using the same environment variables and port mapping (5678).
