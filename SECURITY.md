# Security Reference — VanderWeb WordPress Headless

Use this as a go-live checklist. Each section covers one layer of the defence-in-depth stack.

---

## What the security layer protects against

| Threat | Mitigation |
|---|---|
| Username enumeration via `/wp/v2/users` | Endpoint removed for unauthenticated requests |
| Username enumeration via `/?author=1` | Redirected to homepage for unauthenticated visitors |
| Unsolicited cross-origin API reads | CORS restricted to `VANDER_ALLOWED_ORIGINS` |
| XML-RPC brute force / DDoS amplification | XML-RPC disabled entirely |
| REST API scrapers discovering endpoint | Discovery link and header removed from responses |
| Naive REST brute-force probes | Transient-based rate limit: 30 req / 60 s per IP |
| Open REST API leaking content/metadata | All unauthenticated REST traffic blocked except whitelisted prefixes |

---

## VANDER_ALLOWED_ORIGINS

Defined in `includes/security-helpers.php`:

```php
define(
    'VANDER_ALLOWED_ORIGINS',
    [
        'https://vander.dk',
        'https://www.vander.dk',
        'http://localhost:3000', // Dev only — remove in production.
    ]
);
```

**To add a staging environment**, append its origin to the array:

```php
'https://staging.vander.dk',
```

The check is strict string equality — no wildcard matching — to prevent subdomain takeover attacks where a compromised `*.vander.dk` subdomain could otherwise satisfy a wildcard rule.

---

## Whitelisted REST prefixes

Only these prefixes are open to unauthenticated requests (see `rest-security.php`):

- `/wp-json/vander/v1/` — plugin settings and section types
- `/wp-json/wp/v2/pages` — pages consumed by the Nuxt frontend
- `/wp-json/wp/v2/posts` — posts if a blog section is used

Everything else returns `401 rest_forbidden`. To open additional endpoints, add their prefix to the `$allowed_prefixes` array in `vander_whitelist_rest_endpoints()`.

---

## Recommended wp-config.php constants

Add these to `wp-config.php` on the server. They are not set by the plugin (wp-config lives outside the plugin's scope).

```php
define( 'DISALLOW_FILE_EDIT', true );   // Disables theme/plugin editor in WP admin.
define( 'DISALLOW_FILE_MODS', true );   // Optional: blocks plugin/theme installs from admin UI.
define( 'WP_DEBUG', false );            // Always false in production — never leak stack traces.
define( 'FORCE_SSL_ADMIN', true );      // Forces HTTPS for all admin and login traffic.
```

- [ ] `DISALLOW_FILE_EDIT` added to wp-config.php
- [ ] `WP_DEBUG` set to `false`
- [ ] `FORCE_SSL_ADMIN` set to `true`

---

## Cloudflare settings

### DNS
- [ ] All DNS records for `vander.dk` and `www.vander.dk` are proxied (orange cloud ON)

### SSL/TLS
- [ ] SSL/TLS mode set to **Full (strict)** — requires a valid certificate on the origin server

### Security
- [ ] **Bot Fight Mode** enabled (Security → Bots)

### WAF rules
- [ ] Rate limit `/wp-json/*` to **100 requests per minute per IP**
  - Expression: `http.request.uri.path contains "/wp-json/"`
  - Action: Block after 100 req / 60 s
- [ ] Block `/wp-login.php` from outside Denmark *(optional, breaks remote admin access)*
  - Expression: `http.request.uri.path eq "/wp-login.php" and ip.geoip.country ne "DK"`
  - Action: Block
- [ ] Block `/xmlrpc.php` entirely at the edge (belt-and-suspenders with the PHP filter)
  - Expression: `http.request.uri.path eq "/xmlrpc.php"`
  - Action: Block

---

## Application Passwords (Nuxt server-side calls)

For Nuxt server-side requests that need authenticated REST access (e.g. saving settings, previewing drafts):

1. In WP Admin, go to **Users → Profile** for the API user
2. Scroll to **Application Passwords**
3. Enter a name (e.g. `nuxt-server`) and click **Add New Application Password**
4. Copy the generated password — it is shown only once
5. Set it as an environment variable in the Nuxt project:
   ```
   WP_APP_USER=api-user
   WP_APP_PASSWORD=xxxx xxxx xxxx xxxx xxxx xxxx
   ```
6. In Nuxt, pass it as a Basic Auth header:
   ```ts
   const credentials = btoa(`${user}:${password}`)
   $fetch(url, { headers: { Authorization: `Basic ${credentials}` } })
   ```

- [ ] Application Password created for server-side Nuxt calls
- [ ] Credentials stored in environment variables, not committed to the repo

---

## Go-live checklist summary

- [ ] `VANDER_ALLOWED_ORIGINS` updated — `localhost:3000` removed
- [ ] `wp-config.php` constants added
- [ ] Cloudflare DNS proxied (orange cloud)
- [ ] Cloudflare SSL/TLS: Full (strict)
- [ ] Cloudflare Bot Fight Mode enabled
- [ ] Cloudflare WAF: rate limit `/wp-json/`
- [ ] Application Password created for server-side API calls
- [ ] `WP_DEBUG` is `false` on the production server
- [ ] No debug plugins active (Query Monitor, Debug Bar, etc.)
