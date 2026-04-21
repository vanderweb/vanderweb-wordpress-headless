# VanderWeb WordPress Headless — Plugin Reference

## Purpose & Architecture

This plugin converts WordPress into a headless CMS backend for a Nuxt 3 frontend. It:

- Adds a **Page Sections** sidebar panel in the Gutenberg editor, letting editors compose pages from typed, structured content blocks
- Exposes page sections and global settings (header/footer/general) as clean JSON via the WordPress REST API
- Provides admin settings pages (General / Header / Footer) built in React, accessible under **Vander Headless** in the WP admin menu

All React/JSX lives in `gutenberg/src/` and is compiled by `@wordpress/scripts` (webpack) into `gutenberg/build/`.

---

## REST API Endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/wp-json/wp/v2/pages/:id` | public | Standard WP endpoint — includes `page_sections` array (decoded from JSON meta) |
| GET | `/wp-json/vander/v1/settings` | public | Returns `{ general, header, footer }` objects |
| POST | `/wp-json/vander/v1/settings` | `manage_options` | Saves one or more of `{ general?, header?, footer? }` |
| GET | `/wp-json/vander/v1/section-types` | public | Returns full section type schema (type, label, fields) |

### Settings response shape

```json
{
  "general": {
    "siteName": "",
    "siteDescription": "",
    "logoUrl": "",
    "faviconUrl": "",
    "googleAnalyticsId": "",
    "maintenanceMode": false
  },
  "header": {
    "logoUrl": "",
    "logoAlt": "",
    "navLinks": [{ "label": "", "url": "", "target": false }],
    "ctaLabel": "",
    "ctaUrl": "",
    "stickyHeader": false,
    "transparentHeader": false
  },
  "footer": {
    "logoUrl": "",
    "logoAlt": "",
    "tagline": "",
    "columns": [{ "heading": "", "links": [{ "label": "", "url": "" }] }],
    "bottomText": "",
    "socialLinks": [{ "platform": "facebook", "url": "" }]
  }
}
```

---

## Section Types & Field Schemas

Section types are the **single source of truth** — defined in `includes/section-definitions.php` → `vander_get_section_types()` and exposed to JS via `window.vanderSectionTypes` (wp_localize_script).

| Type | Label | Fields |
|------|-------|--------|
| `hero` | Hero | heading, subheading, cta_label, cta_url, background_image (image), overlay_opacity (number) |
| `services` | Services | heading, subheading, items (repeater: title, icon, text) |
| `cases` | Cases | heading, subheading, case_ids (repeater: post_id) |
| `about` | About | heading, text (textarea), image, cta_label, cta_url |
| `testimonials` | Testimonials | heading, items (repeater: quote, author, role, avatar image) |
| `contact` | Contact | heading, subheading, email, phone, show_form (toggle) |
| `text_image` | Text + Image | heading, text (textarea), image, layout (select: image_left\|image_right) |
| `freetext` | Free Text | heading, content (textarea), centered (toggle) |

### Field type values

- `text` — single-line string
- `textarea` — multi-line string
- `number` — numeric value
- `toggle` — boolean
- `select` — string from predefined options (`field.options` array)
- `image` — WordPress media attachment ID (integer)
- `repeater` — array of objects; `field.fields` defines sub-fields

---

## Adding a New Section Type

1. Open `includes/section-definitions.php`
2. Add a new entry to the array returned by `vander_get_section_types()`
3. The Gutenberg panel and REST endpoint automatically pick it up — no JS changes needed
4. Rebuild JS if you changed any component logic: `npm run build` inside `gutenberg/`

Example entry:

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

## Building the Gutenberg JS

```bash
cd gutenberg
npm install
npm run build   # production build → gutenberg/build/
npm run start   # dev watch mode
```

The build output (`gutenberg/build/index.js`, `index.css`, `index.asset.php`) is committed to the repo so the plugin works without a build step on the server.

---

## Key Files

```
vanderweb-wordpress-headless.php      Main plugin bootstrap, asset enqueue
includes/section-definitions.php     Section type schema (PHP source of truth)
includes/register-meta.php           post meta + options registration
includes/rest-api.php                REST routes and page_sections filter
admin/admin-menu.php                 WP admin menu + asset enqueue for settings pages
admin/settings-{general,header,footer}.php  Mount point HTML for React apps
gutenberg/src/index.js               JS entry — registers plugin panel + mounts admin pages
gutenberg/src/panels/               React page components
gutenberg/src/components/           Shared React components (FieldTypes, SectionEditor, SectionList)
```
