# Native RSS subscriptions and public X snapshots

## Verified starting point (2026-10-15)

- Production image: `wallabag/wallabag`, application release **2.6.14**. Healthy container `wallabag-wallabag-1`, compose workdir `/home/jon/docker/wallabag`, host port 32768. Only the images directory is bind-mounted into the application container. MariaDB and Redis remain production dependencies.
- `ContentProxy.php`, `EntryController.php`, and `composer.lock` hashes match upstream tag 2.6.14. This is representative verification, not proof that every deployed file is unmodified.
- RSS source: https://github.com/jonniesweb/rss-wallabag, branch main. Both deployed Python implementation files exactly match the local source hashes. Poll interval 30 minutes, default window 10 items.
- Fork: https://github.com/jonniesweb/wallabag. Development base: upstream tag `2.6.14` (`74cbfd945bc316b5cf0308ba56e32fd2ed3443c0`), rather than master, which has moved to a newer application layout/PHP requirement.
- Inspections used the existing trusted LAN SSH route. No service restart, production configuration change, production database write, credential creation, or deployment occurred.

## Implementation

1. Add owned subscriptions and seen-item tables through a reversible Doctrine schema migration.
2. Add an RSS subscriptions navigation item and management page, with escaped values, session authentication and CSRF protection.
3. Add authenticated JSON endpoints using Wallabag's existing OAuth firewall. Every lookup scopes to the current user; another user's ID returns 404.
4. Poll due subscriptions with `wallabag:rss:fetch`. An atomic database claim prevents simultaneous cron workers processing the same feed. A project-local CLI file lock serializes workers on the single deployment host; multi-host workers must share that lock directory or provide a distributed scheduler lock to preserve cross-feed deduplication. Disabled user accounts are not polled. Feed and article requests are bounded, use public-address validation, pin DNS, and validate each redirect. Feed XML forbids DTD/entities.
5. Extract articles from safely prefetched HTML with secondary/multipage fetching disabled. Sanitize through the existing ContentProxy/Graby HTML cleanup. Do not invoke the legacy server-side image downloader from RSS ingestion. External images may remain in sanitized content and render in the browser; offline media mirroring is not implemented by this importer.
6. Integrate X public embeds and an optional structured article reader into ContentProxy so the ordinary new-entry form, API entry creation and reload paths all use it. Safe attributed text/links only; no widget scripts, inline handlers, iframes or automatic media downloads. Every fallback public embed is explicitly partial (stored `http_status: 206`); unavailability is explicit (`502`). Failed reloads retain saved structured Articles and display a status notice. A nullable internal `x_capture_type` distinguishes Articles from ordinary embeds and unavailable placeholders, so retries can recover those states; it is not an API/import field.

RSS retains the old 30-minute schedule, enabled/disabled subscriptions, configurable recent-item window (default 10), relative URL resolution/fragment removal, publication dates, item categories and successful-item deduplication. Subscription tags are also applied. A pre-existing user entry counts as seen without overwriting it. Entries and seen markers flush together. Failures are visible on the UI/API and retry on the next cycle. A worker spends at most about 210 seconds on one feed (120-second loop budget plus a bounded in-flight request); the 30-minute claim exceeds that budget. The command limits each invocation to 10 feeds.

The previous Medium-to-Freedium URL rewrite is deliberately removed: original URLs are kept and extraction failures are shown. Authentication, anti-bot gates and paywalls are not bypassed.

## API

All endpoints require the same Bearer token as `/api/entries.json`.

| Method | Path | Behavior |
|---|---|---|
| GET | `/api/rss/feeds.json` | List current user's subscriptions and status |
| POST | `/api/rss/feeds.json` | Add subscription; 201 or 400 validation error |
| GET | `/api/rss/feeds/{id}.json` | Read owned subscription |
| PATCH | `/api/rss/feeds/{id}.json` | Change name, tags, max_items, enabled |
| DELETE | `/api/rss/feeds/{id}.json` | Remove subscription/seen history; imported entries remain |
| POST | `/api/rss/import.json` | Transactionally merge exported subscriptions and seen URLs |

Create/update fields: `url` (create only), `name` (max 255 bytes), `tags` (array, up to 20 strings of 100 bytes), `max_items` (integer 1–100), `enabled` (boolean). URL changes require a new subscription so old seen history is never silently assigned to another feed. Maximum 200 subscriptions per owner; JSON requests up to 8 MiB. Response includes `last_fetch_at`, `next_fetch_at`, `last_error`, and `imported_count`.

Import format: `{"feeds":[{"url":"https://example.org/feed","name":"Example","tags":[],"max_items":10,"enabled":true}],"seen_items":{"https://example.org/feed":[{"url":"https://example.org/article"}]}}`. The old seen-items dictionary format also works. Repeating an import preserves existing settings and skips duplicate markers. Historical feeds present only in seen_items are retained disabled.

## Migration and scheduling (not executed on production)

Deployment requires Jon's approval. Back up the application database using the existing database-aware procedure before any production migration. Do not run two ingestion systems concurrently during cutover.

1. Obtain a consistent **offline backup** of rss-wallabag's SQLite DB with its WAL data accounted for. Do not copy a running DB file alone. Do not mount production data into the development checkout.
2. `python3 scripts/export-rss-wallabag.py /path/to/offline-backup.sqlite > rss-export.json`. The exporter opens its input read-only and never includes Wallabag credentials.
3. After review and an approved application deployment/migration, sign in as the intended Wallabag owner and import that export through RSS subscriptions, or POST it with that owner's existing API token.
4. Stop the old tracker only at the approved cutover. Configure a scheduler to invoke `bin/console wallabag:rss:fetch --env=prod` every minute, under the app user. The native per-feed due time enforces 30 minutes. No scheduler is installed by this branch.
5. Check the subscriptions page after a cycle. Failures and partial captures must remain visible; do not infer full content from an API 201 alone.

Unsubscribing deletes subscription/seen history but retains saved articles. Migration rollback removes only the new RSS tables; export RSS settings before rollback if needed. The old SQLite backup remains a rollback artifact.

## Public X Article reader and validation

The public embed-only limit for the supplied post is now addressed by an optional FxTwitter reader. The actual article body was retrieved without credentials/payment and replayed through the application sanitizer: every nonempty text block survived. Enable the per-user Settings checkbox or PATCH `/api/config.json` with `{"x_article_reader_enabled":true}`. It defaults off, discloses public post ID/server IP sharing, and never sends private Wallabag data or credentials.

See [reader evaluation, sources and completeness evidence](x-article-readers.md) and [reproducible Docker setup/test/cleanup and verification limits](local-validation.md).

Current regression counts and separate review-remediation runs are recorded in `validation-2026-10-15.json`, including actual export resource-request checks. Legacy SQLite schema drift is recorded separately; the new RSS tables and preference column match mapping. No production migration or deployment was performed.
