# CDS World — Architecture (Phase 1)

## Decisions
- **Admin**: Filament v4 at `/admin` (own design; independent of public theme). RBAC = Spatie Permission via Filament Shield.
- **Public site**: Blade + Bootstrap 5 + jQuery. Theme = CSS variables rendered from `website_settings` (group `theme`), cached.
- **Media library** = Spatie `media` table (collections, alt text in `custom_properties`, usage tracking via `model_type/model_id`). No duplicate `media_library` table. Image driver `gd`, WebP conversions.
- **Activity log** = Spatie `activity_log`. **Taxonomy** = polymorphic `categories` (typed) + `categoryables`, `tags` + `taggables`.
- **SEO** = one polymorphic `seo_metadata` row per record (`seoable`).
- **Publishing**: `status` ∈ draft, pending_review, approved, rejected, scheduled, published. A record is LIVE when status ∈ {published, scheduled} AND `published_at <= now()` — scheduled content goes live without depending on cron; a scheduled command only tidies status.
- **Homepage / About / policies** are `pages`; the homepage is a page (`slug=home`, template `home`) whose `page_sections` are typed blocks (hero, ticker, research_focus, latest_articles, events, videos, media_coverage, gallery, about, team, newsletter) with `sort_order`, `is_visible`, JSON `data`.
- **Event status** (Upcoming/Ongoing/Completed/Cancelled) is derived from dates + `is_cancelled`; never stored.
- Disk is scarce (shared host at 96%): video uploads capped, YouTube/Facebook embeds are the default.

## Tables (custom)
users (+designation, bio, social_links, is_active, last_login_at, soft deletes) · categories · categoryables · tags · taggables · seo_metadata ·
pages · page_sections · articles · article_revisions · events · event_speakers · event_registrations · media_outlets · media_coverage ·
videos · gallery_albums · gallery_items · team_members · navigation_menus · navigation_items · website_settings · popups · redirects ·
newsletter_subscribers · newsletter_campaigns · contact_messages.
Package tables: roles, permissions, model_has_*, role_has_permissions, media, activity_log, jobs, cache, sessions.

## Roles (Shield permissions per resource: view_any, view, create, update, delete, restore, + custom)
| Role | Scope |
|---|---|
| super_admin | everything (Gate::before) |
| administrator | all content modules, menus, popups, newsletter; no roles/users-permissions/system settings unless granted |
| editor | review/approve/reject, edit any article, categories, media, feature, schedule |
| writer | create/edit OWN articles, submit for review; no publish unless `publish_article` granted |
| seo_manager | SEO fields on all models, redirects, sitemap settings, SEO dashboard |
| event_manager | events, speakers, registrations, event categories/media |
| media_manager | media coverage, gallery, videos, video categories, media library |
Custom permissions: `publish_article`, `approve_article`, `feature_article`, `schedule_article`, `manage_seo`, `manage_redirects`, `manage_theme`, `manage_settings`, `view_activity_log`, `manage_backups`.

## Article workflow
draft → (writer) submit → pending_review → (editor) approve → approved → publish/schedule → published/scheduled; editor may reject → rejected (with notes) → writer edits → resubmit. Every save stores a row in `article_revisions`.
