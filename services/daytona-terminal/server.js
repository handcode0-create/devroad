import { createServer } from "node:http";
import { createHmac, timingSafeEqual } from "node:crypto";
import { WebSocketServer, WebSocket } from "ws";

const port = Number(process.env.PORT || 8080);
const secret = process.env.DEVROAD_SANDBOX_BRIDGE_SECRET || "";
const daytonaApiKey = process.env.DAYTONA_API_KEY || "";
const toolboxBase = (process.env.DAYTONA_TOOLBOX_URL || "https://proxy.app.daytona.io/toolbox").replace(/\/$/, "");
const allowedOrigins = (process.env.ALLOWED_ORIGINS || "").split(",").map((value) => value.trim()).filter(Boolean);

const server = createServer((request, response) => {
  if (request.url === "/health") {
    response.writeHead(200, { "content-type": "application/json" });
    response.end(JSON.stringify({ ok: true, service: "devroad-daytona-terminal" }));
    return;
  }

  response.writeHead(404);
  response.end();
});

const wss = new WebSocketServer({ noServer: true, maxPayload: 64 * 1024 });

server.on("upgrade", (request, socket, head) => {
  const url = new URL(request.url || "/", "http://localhost");

  if (url.pathname !== "/terminal") {
    socket.destroy();
    return;
  }

  if (!secret || !daytonaApiKey) {
    socket.destroy();
    return;
  }

  const origin = request.headers.origin;
  if (allowedOrigins.length > 0 && (!origin || !allowedOrigins.includes(origin))) {
    socket.destroy();
    return;
  }

  const token = url.searchParams.get("token");
  const payload = verifyToken(token);

  if (!payload) {
    socket.destroy();
    return;
  }

  wss.handleUpgrade(request, socket, head, (client) => {
    wss.emit("connection", client, request, payload);
  });
});

wss.on("connection", async (client, _request, payload) => {
  const sandboxId = String(payload.sandbox_id);
  const sessionId = String(payload.session_id);

  let upstream;

  try {
    await ensurePty(sandboxId, sessionId);

    const connectUrl = toolboxBase
      + "/"
      + encodeURIComponent(sandboxId)
      + "/process/pty/"
      + encodeURIComponent(sessionId)
      + "/connect";

    upstream = new WebSocket(connectUrl, {
      headers: {
        Authorization: "Bearer " + daytonaApiKey,
      },
      maxPayload: 64 * 1024,
    });

    upstream.on("open", () => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(JSON.stringify({ type: "ready", sessionId }));
      }
    });

    upstream.on("message", (data, isBinary) => {
      if (client.readyState !== WebSocket.OPEN) return;
      client.send(data, { binary: isBinary });
    });

    upstream.on("close", (code, reason) => {
      if (client.readyState === WebSocket.OPEN) {
        client.close(code || 1000, reason?.toString() || "");
      }
    });

    upstream.on("error", (error) => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(JSON.stringify({
          type: "error",
          message: error instanceof Error ? error.message : "Terminal upstream error",
        }));
        client.close(1011, "upstream error");
      }
    });

    client.on("message", (data, isBinary) => {
      if (!upstream || upstream.readyState !== WebSocket.OPEN) return;
      upstream.send(data, { binary: isBinary });
    });

    client.on("close", () => {
      if (upstream && upstream.readyState === WebSocket.OPEN) {
        upstream.close(1000);
      }
    });

    client.on("error", () => {
      if (upstream && upstream.readyState === WebSocket.OPEN) {
        upstream.close(1011);
      }
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : "Unable to connect to Daytona";
    if (client.readyState === WebSocket.OPEN) {
      client.send(JSON.stringify({ type: "error", message }));
      client.close(1011, "terminal initialization failed");
    }
  }
});

function verifyToken(token) {
  if (!token) return null;

  const separator = token.lastIndexOf(".");
  if (separator <= 0) return null;

  const encoded = token.slice(0, separator);
  const signature = token.slice(separator + 1);

  const expected = createHmac("sha256", secret).update(encoded).digest("hex");
  const actualBuffer = Buffer.from(signature, "utf8");
  const expectedBuffer = Buffer.from(expected, "utf8");

  if (actualBuffer.length !== expectedBuffer.length || !timingSafeEqual(actualBuffer, expectedBuffer)) {
    return null;
  }

  try {
    const payload = JSON.parse(Buffer.from(encoded, "base64url").toString("utf8"));

    if (!payload || !payload.sandbox_id || !payload.session_id || !payload.exp) {
      return null;
    }

    if (Number(payload.exp) < Math.floor(Date.now() / 1000)) {
      return null;
    }

    return payload;
  } catch {
    return null;
  }
}

async function ensurePty(sandboxId, sessionId) {
  const base = toolboxBase + "/" + encodeURIComponent(sandboxId) + "/process/pty/" + encodeURIComponent(sessionId);
  const headers = {
    Authorization: "Bearer " + daytonaApiKey,
    Accept: "application/json",
    "Content-Type": "application/json",
  };

  const existing = await fetch(base, { headers });

  if (existing.ok) {
    return;
  }

  if (existing.status !== 404) {
    throw new Error("Daytona PTY lookup failed (" + existing.status + ")");
  }

  const create = await fetch(
    toolboxBase + "/" + encodeURIComponent(sandboxId) + "/process/pty",
    {
      method: "POST",
      headers,
      body: JSON.stringify({
        id: sessionId,
        cwd: "/workspace",
        cols: 120,
        rows: 30,
        envs: {
          TERM: "xterm-256color",
          LANG: "en_US.UTF-8",
        },
        lazyStart: false,
      }),
    }
  );

  if (!create.ok && create.status !== 409) {
    const body = await create.text().catch(() => "");
    throw new Error("Daytona PTY creation failed (" + create.status + "): " + body.slice(0, 300));
  }
}

server.listen(port, "0.0.0.0", () => {
  console.log("DevRoad Daytona terminal bridge listening on " + port);
});
