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
