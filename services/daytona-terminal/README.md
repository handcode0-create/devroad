# DevRoad Daytona Terminal Bridge

Small WebSocket bridge between the authenticated DevRoad application and Daytona PTY sessions.

## Security model

- The browser never receives the Daytona API key.
- Laravel issues a short-lived HMAC-signed terminal token after the project ownership policy passes.
- The bridge validates the token before opening a Daytona connection.
- The bridge optionally validates the browser Origin.
- Daytona remains the only runtime executing user commands.

## Runtime

Set:

- `DAYTONA_API_KEY`
- `DAYTONA_TOOLBOX_URL`
- `DEVROAD_SANDBOX_BRIDGE_SECRET`
- `ALLOWED_ORIGINS` (comma-separated, recommended)

The service exposes `GET /health` and `WS /terminal?token=...`.

## Deploy on Railway

1. New service from the same repository, **Root Directory** = `services/daytona-terminal` (it brings its own `Dockerfile` and `railway.json`, healthcheck `/health`).
2. Variables on the **bridge** service:
   - `DAYTONA_API_KEY` : the Daytona API key (only this service and the web service need it)
   - `DAYTONA_TOOLBOX_URL` : `https://proxy.app.daytona.io/toolbox`
   - `DEVROAD_SANDBOX_BRIDGE_SECRET` : a long random value, **identical** on the web service
   - `ALLOWED_ORIGINS` : the exact DevRoad URL, e.g. `https://<domaine-devroad>`
3. Networking → Generate Domain on the bridge service.
4. Variables on the **DevRoad web** service:
   - `DEVROAD_SANDBOX_ENABLED=true`
   - `DEVROAD_SANDBOX_DRIVER=daytona`
   - `DAYTONA_API_KEY`, `DAYTONA_API_URL`, `DAYTONA_TOOLBOX_URL`
   - `DEVROAD_SANDBOX_BRIDGE_URL=https://<domaine-du-pont>`
   - `DEVROAD_SANDBOX_BRIDGE_SECRET` (same value as the bridge)
5. Redeploy the web service, create a Sandbox project, start it, then open its terminal.

Generate the secret with `php -r "echo bin2hex(random_bytes(32));"`. Never commit it or the Daytona key.

The terminal only connects once the sandbox is `running`. If the page shows « Terminal indisponible », one of the variables above is missing on the web service.
