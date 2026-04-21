# VanderWeb WordPress Headless

WordPress plugin that converts a WordPress site into a headless CMS backend for a Nuxt 3 frontend. Provides structured page sections, global site settings (header/footer/general), and a clean REST API for the frontend to consume.

---

## Repositories

| Repo | Purpose |
|---|---|
| `vanderweb/vanderweb-wordpress-headless` | This plugin — install on every WordPress site |
| `vanderweb/vander-frontend` | Nuxt 3 frontend — clone per site, edit `site.config.json` |
| `vanderweb/wp-nuxt-core` | Shared Nuxt composables and TypeScript types |

---

## Installing on a new site

### 1. WordPress plugin

Download the latest zip from the [releases page](https://github.com/vanderweb/vanderweb-wordpress-headless/releases) or build it locally (see below).

In WordPress admin: **Plugins → Add New → Upload Plugin** → activate.

Add the following to `wp-config.php` **before** `require_once ABSPATH . 'wp-settings.php'`:

```php
// Vander Headless — allowed CORS origins for the Nuxt frontend.
// Comma-separated. Include all environments that need to call the REST API.
define( 'VANDER_CORS_ORIGINS', 'https://newsite.com,https://www.newsite.com,http://localhost:3000' );

// Recommended hardening constants.
define( 'DISALLOW_FILE_EDIT', true );
define( 'WP_DEBUG', false );
define( 'FORCE_SSL_ADMIN', true );
```

### 2. Nuxt frontend

```bash
git clone git@github.com:vanderweb/vander-frontend.git my-project
cd my-project
npm install
```

Edit `site.config.json` in the project root:

```json
{
  "wpApiBase": "https://newsite.com"
}
```

That is the only file that needs to change per site. Run the dev server:

```bash
npm run dev
```

> **CI/CD override:** Set the `WP_API_BASE` environment variable to override `site.config.json` without committing changes (useful for staging vs production pipelines).

---

## REST API

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/wp-json/wp/v2/pages/:id` | public | Standard WP page — includes enriched `page_sections` array |
| GET | `/wp-json/vander/v1/settings` | public | Returns `{ general, header, footer }` |
| POST | `/wp-json/vander/v1/settings` | `manage_options` | Saves settings |
| GET | `/wp-json/vander/v1/section-types` | public | Full section type schema |
| GET | `/wp-json/vander/v1/menus` | `manage_options` | Lists registered WordPress nav menus |

All other REST endpoints are blocked for unauthenticated requests. See [SECURITY.md](SECURITY.md) for the full whitelist and how to extend it.

---

## Admin settings

Under **Vander Headless** in the WordPress admin menu:

- **General Settings** — site name, logo, favicon, Google Analytics ID, maintenance mode
- **Header Settings** — logo, navigation (select a WordPress menu or enter links manually), CTA button, sticky/transparent behaviour
- **Footer Settings** — logo, tagline, columns with links, social links, bottom text

---

## Page sections

Open any page in the Gutenberg editor. The **Page Sections** panel appears in the right sidebar under the **Side** tab. Use it to compose pages from typed content blocks instead of writing HTML.

Available section types:

| Type | Fields |
|---|---|
| Hero | heading, subheading, CTA, background image, overlay opacity |
| Services | heading, subheading, repeatable items (title, icon, text) |
| Cases | heading, subheading, repeatable case post IDs |
| About | heading, text, image, CTA |
| Testimonials | heading, repeatable items (quote, author, role, avatar) |
| Contact | heading, subheading, email, phone, show form toggle |
| Text + Image | heading, text, image, layout (image left/right) |
| Free Text | heading, content, centered toggle |

Image fields resolve to `{ id, url, alt }` objects server-side. Case post IDs resolve to `{ id, slug, title, excerpt, thumbnail_url }`. The Nuxt frontend receives fully-enriched data — no additional API calls needed.

### Adding a new section type

1. Open `includes/section-definitions.php`
2. Add an entry to the array returned by `vander_get_section_types()`
3. The Gutenberg panel and REST endpoint pick it up automatically

```php
[
    'type'   => 'cta_banner',
    'label'  => 'CTA Banner',
    'fields' => [
        [ 'key' => 'heading',   'label' => 'Heading',   'type' => 'text' ],
        [ 'key' => 'cta_label', 'label' => 'CTA Label', 'type' => 'text' ],
        [ 'key' => 'cta_url',   'label' => 'CTA URL',   'type' => 'text' ],
    ],
],
```

---

## Building the JS bundle locally

Required only when modifying the React admin pages or Gutenberg panel. The compiled output is committed to the repo so the plugin works without a build step on the server.

```bash
cd gutenberg
npm install
npm run build    # production build → gutenberg/build/
npm run start    # dev watch mode
```

---

## Building the plugin zip

Run from the repo root on Windows:

```powershell
Add-Type -Assembly 'System.IO.Compression.FileSystem'
$pluginDir = 'C:\path\to\vanderweb-wordpress-headless'
$zipPath   = 'C:\vanderweb-wordpress-headless.zip'
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
$zip = [System.IO.Compression.ZipFile]::Open($zipPath, 'Create')
$files = Get-ChildItem -Path $pluginDir -Recurse -File | Where-Object {
    $rel = $_.FullName.Substring($pluginDir.Length + 1)
    $rel -notmatch '^vendor\\' -and $rel -notmatch '^gutenberg\\node_modules\\' -and
    $rel -notmatch '\.mo$' -and $rel -notmatch '^\.git\\'
}
foreach ($file in $files) {
    $rel = $file.FullName.Substring($pluginDir.Length + 1)
    $entry = 'vanderweb-wordpress-headless/' + $rel.Replace('\', '/')
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
        $zip, $file.FullName, $entry,
        [System.IO.Compression.CompressionLevel]::Optimal
    ) | Out-Null
}
$zip.Dispose()
```

The zip uses forward-slash entry paths, ensuring correct extraction on Linux servers.

---

## Requirements

- WordPress 6.6+
- PHP 8.0+
- Node.js 18+ (build only)
