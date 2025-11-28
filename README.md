# HohnePeople Website

HohnePeople is a lightweight PHP site for showcasing music videos, editorial reviews, and evergreen pages in multiple languages with switchable visual themes. Content is stored in MySQL and rendered through simple views for fast, low-maintenance publishing.

## Features
- **Video hub**: List all videos with pagination, detail pages per slug, platform embeds, external links (e.g., YouTube/SoundCloud), related videos, and linked reviews.
- **Editorial reviews**: Publish long-form reviews with associated videos and teaser excerpts for the reviews index.
- **Static pages**: Serve about/imprint/privacy pages (or any custom slug) with translated titles and bodies.
- **Navigation & footer**: Configurable header/footer menus plus active social link icons for YouTube, Instagram, TikTok, and more.
- **Themes**: Multiple CSS themes with cookie-based persistence and a default selector stored in the database.
- **Localization**: Language switcher with per-key translations and fallback to the default language.
- **SEO helpers**: Canonical/meta data per page type and an XML sitemap including videos, reviews, and key pages.

## Project layout
- `index.php`: Front controller, routing, and data-loading helpers for videos, reviews, pages, navigation, and meta generation.
- `views/`: PHP templates for the homepage, video/review detail and listing views, static pages, link hub, plus shared header/footer partials.
- `themes/`: CSS stylesheets registered in the `themes` table; the default theme is set in `config.php` or via DB flag.
- `assets/`: Static assets referenced by templates and themes.
- `sanitizer.php`: HTML/URL sanitization helpers used across views.
- `config.php.sample`: Example configuration with database credentials, site defaults, and security options.
- `schema.sql`: Database schema (tables for content, translations, navigation, social links, themes) with seed/demo data.
- `tests/`: Integration and unit tests for routing, rendering, and helpers.

## Requirements
- PHP 8.1+ with PDO MySQL extension
- MySQL 8.x (or compatible) database
- Web server capable of serving PHP (Apache with mod_php, Nginx + PHP-FPM, or PHP's built-in server for development)

## Setup
1. **Install dependencies**: Ensure PHP and MySQL are available and the PDO MySQL extension is enabled.
2. **Create database**: Import `schema.sql` into a new database (e.g., `hohnepeople`).
3. **Configure the app**:
   - Copy `config.php.sample` to `config.php`.
   - Update DB credentials, default language/theme, and optional `site.base_url` (used for canonical links and sitemap generation).
   - Set a strong `security.nonce_salt` value.
4. **Seed content** (optional): The schema includes sample texts, navigation, themes, and demo content to explore the site immediately after import.

## Running locally
- **PHP built-in server** (development):
  ```bash
  php -S 0.0.0.0:8000 -t .
  ```
  Visit http://localhost:8000. Pretty URLs are enabled by default; if routing fails, set `use_pretty_urls` to `false` in `config.php` and access pages via query params (e.g., `/index.php?page=videos`).

- **Apache/Nginx**:
  - Point the document root to the repository directory.
  - Ensure PHP files are executed (via mod_php or PHP-FPM).
  - If you use pretty URLs, configure rewrites to route all requests to `index.php` (or disable pretty URLs in `config.php`).

## Deployment
- **Production web server**: Deploy the repository to your web root (or a subdirectory) and configure virtual hosts/server blocks to pass `.php` files to PHP-FPM. Enable HTTPS and set `site.base_url` to the public origin to lock canonical links and sitemap entries.
- **Environment hardening**:
  - Store `config.php` outside version control and restrict file permissions.
  - Use secure database credentials and a unique `nonce_salt`.
  - Keep the server's PHP version patched and disable display_errors in production.
- **Database migrations/content**: Apply `schema.sql` (and any subsequent migrations) to the production database, then insert/update content through your preferred management workflow or SQL imports.
- **Static assets & themes**: Ensure the `assets/` and `themes/` directories are deployed and web-accessible; update `themes` table entries if you add custom stylesheets.

## Content management notes
- Videos, reviews, pages, navigation entries, social links, and themes are stored in the database; translations use language-specific rows with fallbacks.
- The sitemap and meta tags rely on `site.base_url` when provided and otherwise derive the origin from validated host headers.
- URL and HTML sanitization protects embeds and rich text; ensure any custom templates continue to pass untrusted data through the provided helpers.

## Testing
Run the existing test suite from the project root:
```bash
php -d variables_order=EGPCS vendor/bin/phpunit
```
If Composer dependencies are not installed, run `composer install` first.
