import { cookies } from "next/headers";
import crypto from "crypto";

const PHP_API_BASE_URL = process.env.PHP_API_BASE_URL;
const INTERNAL_HMAC_SECRET = process.env.INTERNAL_HMAC_SECRET;

const ACCESS_COOKIE_NAME = process.env.ACCESS_COOKIE_NAME || "access_token";
const REFRESH_COOKIE_NAME = process.env.REFRESH_COOKIE_NAME || "refresh_token";

if (!PHP_API_BASE_URL) throw new Error("Missing env PHP_API_BASE_URL");
if (!INTERNAL_HMAC_SECRET) throw new Error("Missing env INTERNAL_HMAC_SECRET");

function sha256Hex(input) {
  return crypto.createHash("sha256").update(input).digest("hex");
}

function hmacHex(input) {
  return crypto.createHmac("sha256", INTERNAL_HMAC_SECRET).update(input).digest("hex");
}

function buildInternalSignature({ method, pathWithQuery, ts, bodyHash }) {
  const canonical = `${method}\n${pathWithQuery}\n${ts}\n${bodyHash}`;
  return hmacHex(canonical);
}

async function callPhpApiRaw({
  method,
  path,
  query,
  headers,
  body,
  includeAuth,
  includeRefresh,
}) {
  const url = new URL(path, PHP_API_BASE_URL);
  if (query) {
    for (const [k, v] of Object.entries(query)) {
      if (v !== undefined) url.searchParams.set(k, String(v));
    }
  }

  const upperMethod = String(method || "GET").toUpperCase();
  const bodyString = body === undefined ? "" : JSON.stringify(body);
  const bodyHash = sha256Hex(bodyString);
  const ts = String(Math.floor(Date.now() / 1000));

  const pathWithQuery = url.pathname + (url.search ? url.search : "");
  const sig = buildInternalSignature({ method: upperMethod, pathWithQuery, ts, bodyHash });

  const reqHeaders = {
    Accept: "application/json",
    "Content-Type": body === undefined ? undefined : "application/json",
    "X-Internal-Timestamp": ts,
    "X-Internal-Body-Hash": bodyHash,
    "X-Internal-Signature": sig,
    ...(headers || {}),
  };
  if (!reqHeaders["Content-Type"]) delete reqHeaders["Content-Type"]; // avoid invalid header

  if (includeAuth) {
    const access = cookies().get(ACCESS_COOKIE_NAME)?.value;
    if (access) reqHeaders.Authorization = `Bearer ${access}`;
  }

  if (includeRefresh) {
    const refresh = cookies().get(REFRESH_COOKIE_NAME)?.value;
    if (refresh) {
      reqHeaders.Cookie = `${REFRESH_COOKIE_NAME}=${refresh}`;
    }
  }

  return fetch(url.toString(), {
    method: upperMethod,
    headers: reqHeaders,
    body: body === undefined ? undefined : bodyString,
    cache: "no-store",
  });
}

async function refreshTokensViaPhpApi() {
  const res = await callPhpApiRaw({
    method: "POST",
    path: "/_internal/auth/refresh",
    includeRefresh: true,
  });
  if (!res.ok) return false;

  const data = await res.json().catch(() => null);
  if (!data || !data.access_token) return false;

  const cookieStore = cookies();
  cookieStore.set(ACCESS_COOKIE_NAME, data.access_token, {
    httpOnly: true,
    secure: true,
    sameSite: "lax",
    path: "/",
    maxAge: 10 * 60,
  });

  if (data.refresh_token) {
    cookieStore.set(REFRESH_COOKIE_NAME, data.refresh_token, {
      httpOnly: true,
      secure: true,
      sameSite: "lax",
      path: "/api/auth/refresh",
      maxAge: 30 * 24 * 60 * 60,
    });
  }

  return true;
}

export async function phpApiRequest({ method, path, query, headers, body, authRequired }) {
  const isRefreshCall = path === "/_internal/auth/refresh";

  let res = await callPhpApiRaw({
    method,
    path,
    query,
    headers,
    body,
    includeAuth: !!authRequired,
    includeRefresh: isRefreshCall,
  });

  if (authRequired && res.status === 401) {
    const refreshed = await refreshTokensViaPhpApi();
    if (!refreshed) return res;

    res = await callPhpApiRaw({
      method,
      path,
      query,
      headers,
      body,
      includeAuth: true,
      includeRefresh: false,
    });
  }

  return res;
}
