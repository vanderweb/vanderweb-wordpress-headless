# VanderWeb WordPress Headless

WordPress plugin that converts a WordPress site into a headless CMS backend for a Nuxt 3 frontend. Provides structured page sections, global site settings (header/footer/general), and a clean REST API for the frontend to consume.

---

## Repositories

| Repo | Purpose |
|---|---|
| `vanderweb/vanderweb-wordpress-headless` | This plugin — install on every WordPress site |
| `vanderweb/vander-frontend` | Nuxt 3 frontend — clone per site |
| `vanderweb/wp-nuxt-core` | Shared Nuxt composables and TypeScript types |

---

## Live environment

- WordPress backend: https://headless.vanderweb.dk
- REST API base: https://headless.vanderweb.dk/wp-json/wp/v2

---

## Installing on a new site

### 1. WordPress plugin

Build the zip (see below) or grab it from the repo root, then in WordPress admin: **Plugins → Add New → Upload Plugin** → activate.

Add the following to `wp-config.php` **before** `require_once ABSPATH . 'wp-settings.php'`:

```php
// Allowed CORS origins for the Nuxt frontend (comma-separated).
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
cp .env.example .env
# set WP_API_BASE=https://newsite.com in .env
npm install
npm run dev
```

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

Under **VanderWeb Headless** in the WordPress admin menu:

- **General Settings** — site name, logo, favicon, Google Analytics ID, maintenance mode, brand colours and fonts
- **Header Settings** — logo, navigation links, CTA button, announcement bar, sticky/transparent behaviour
- **Footer Settings** — logo, tagline, contact info (address, phone, email), columns with links, social links, bottom text

---

## Page sections

Open any page in the Gutenberg editor. The **Page Sections** panel appears in the right sidebar (open by default). Use it to compose pages from typed content blocks.

Available section types:

| Type | Fields |
|---|---|
| Hero | heading, subheading, CTA (primary + secondary), image, overlay opacity, layout (split / fullbleed / headline) |
| Services | heading, subheading, repeatable items (title, icon, text) |
| Cases | heading, subheading, repeatable case post IDs |
| About | heading, text, image, CTA |
| Testimonials | heading, repeatable items (quote, author, role, avatar) |
| Contact | heading, subheading, email, phone, show form toggle |
| Text + Image | heading, text, image, layout (image left/right) |
| Free Text | heading, content, centered toggle |
| Team | heading, subheading, repeatable members (name, role, bio, photo) |
| FAQ | heading, subheading, repeatable items (question, answer) |
| CTA Banner | heading, subheading, CTA, dark background toggle |
| Featured Products | heading, subheading, all-products CTA, repeatable products (title, price, url, image) |
| Categories Grid | heading, layout (2×2 / 4 columns), repeatable categories (name, url, image) |

Image fields resolve to `{ id, url, alt }` objects server-side. Case post IDs resolve to `{ id, slug, title, excerpt, thumbnail_url }`. The frontend receives fully-enriched data — no additional API calls needed.

### Adding a new section type

1. Open `includes/section-definitions.php`
2. Add an entry to the array returned by `vander_get_section_types()`
3. The Gutenberg panel and REST endpoint pick it up automatically
4. Add a matching `Section<TypeName>.vue` component in `vander-frontend/components/sections/`

```php
[
    'type'   => 'my_block',
    'label'  => 'My Block',
    'fields' => [
        [ 'key' => 'heading',   'label' => 'Heading',   'type' => 'text' ],
        [ 'key' => 'cta_label', 'label' => 'CTA Label', 'type' => 'text' ],
        [ 'key' => 'cta_url',   'label' => 'CTA URL',   'type' => 'text' ],
    ],
],
```

---

## Building the JS bundle

Required only when modifying the React admin pages or Gutenberg panel. The compiled output is committed to the repo so the plugin works without a build step on the server.

```bash
cd gutenberg
npm install
npm run build    # production build → gutenberg/build/
npm run start    # dev watch mode
```

---

## Building the plugin zip

Run from the repo root:

```bash
cd /path/to/git-projekter
zip -r vanderweb-wordpress-headless.zip vanderweb-wordpress-headless/ \
  --exclude "vanderweb-wordpress-headless/gutenberg/node_modules/*" \
  --exclude "vanderweb-wordpress-headless/.git/*"
```

The zip is created in the parent directory (`git-projekter/vanderweb-wordpress-headless.zip`).

---

## Requirements

- WordPress 6.6+
- PHP 8.0+
- Node.js 18+ (build only)
