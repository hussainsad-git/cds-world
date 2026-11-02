# CDS World — Laravel CMS (cdsworld.org)

Full requirements: `docs/SPEC.md` (source of truth). Architecture/schema/RBAC decisions: `docs/ARCHITECTURE.md`.
Implement one SPEC section per session, commit, then `/clear`. Do not re-read the whole spec.

## Stack
Laravel 12, PHP 8.4 (lockfile needs >= 8.4), MySQL, Filament v4 (admin at /admin), Filament Shield + spatie/laravel-permission (RBAC),
spatie medialibrary (gd driver, WebP), spatie activitylog, laravel-sitemap, laravel-sluggable, mews/purifier.
Public site: Blade + Bootstrap 5 + jQuery (no Node build on server; built assets are committed).

## Hard rules
- ISOLATION: this app shares a cPanel account (user `websgene`) with ~35 other sites incl. hisabifi, pos-aljabbal, leadflow.
  Never touch other directories, DBs, crontab lines or global composer. Own DB/user, own .env, prefixes `cds_`.
- Production PHP: `/opt/cpanel/ea-php84/root/usr/bin/php`. No Node on server. Disk is 96% full: cap uploads
  (video 200 MB, images resized), default videos to YouTube/Facebook embeds.
- Keep existing public URLs: /articles /events /media-coverage /gallery /about /who-we-are /board-of-directors
  /contact /privacy-policy /terms-conditions (no redirects needed for these).
- No invented organisational facts. Legal name unresolved: "Development and Stability" vs "Development Studies" — ask.
  Verified: info@cdsworld.org, +92 312 9402354, +92 311 5148607; Facebook/Instagram/WhatsApp links from live site.
- Every public page reads from DB; no hardcoded listings. All CRUD: validation + authorization (policies via Shield).
- Soft deletes on content; sanitize rich text (Purifier); activity log on create/update/delete/publish.
- Commands run by the user on Windows/Herd (PHP not available to Claude's shell); keep commands copy-paste exact.

## Status
Phase 1 done (schema, models, seeders, content). Phase 3 in progress: Filament admin. Done: Articles resource (workflow, revisions, SEO tab). Needs: filament/spatie-laravel-media-library-plugin, shield:generate, role->permission seeder. Next: Pages, Events, Categories, MediaCoverage, Videos, Gallery, Team, Navigation, Popups, Newsletter, Redirects, Settings/Theme, dashboard widgets, then public site.
