# Source-built deployment

This image overlays the verified Wallabag 2.6.14 runtime with tracked application
source. Its release-matched Composer lock is checked and autoload regenerated
offline. Startup never runs Composer, installs accounts, grants DB permissions or
applies migrations. Existing configuration and encryption keys are runtime mounts.

Keep the actual Compose file, domain, port, host paths, DB configuration and all
secrets **outside the repository**. `docker/production/compose.example.yml` is only
a reusable template; integrate it with the operator's existing DB/Redis/network.
Never recreate dependencies as collateral work.

1. Record the existing image ID, Compose source, health, mounts, entry/user counts,
   RSS owner and state counts. Preserve the existing rendered `parameters.yml`,
   rendered PHP ini and `data/site-credentials-secret-key.txt` server-locally.
   The application secret and encryption key must remain identical.
2. Make a server-local, permission-restricted backup of the exact Compose source,
   app configuration/key and media. Dump the existing DB using its native
   single-transaction backup tool and credentials already available inside the DB
   container. Do not print dumps/credentials or copy them into this checkout.
   Verify dump integrity and preferably restore it into an isolated disposable DB
   on the same server before production migration. Preserve the previous image
   under an immutable rollback tag; do not remove production volumes.
3. Have the server fetch this repository, check out the reviewed **full source
   commit**, verify a clean tree and run
   `sh scripts/build-production-image local-image:COMMIT`. No production values
   are build arguments. Inspect `org.opencontainers.image.revision` and
   `/var/www/wallabag/.source-revision` before rollout.
4. Prepare a server-local Compose candidate using the built image and existing
   media/dependencies. Both application and worker need `/run/wallabag/parameters.yml`,
   `/run/wallabag/php.ini` and `/run/wallabag/site-credentials-key` read-only mounts.
   The entrypoint copies them into private runtime files owned by the existing
   unprivileged application user. Validate candidate Compose without printing its
   secret-bearing expanded configuration.
   `scripts/prepare-source-compose.py` (operator-host PyYAML required) can generate
   that candidate from existing Compose, an immutable image/revision and the
   server-local runtime-config directory. It refuses overwrite/custom entrypoints,
   keeps dependency definitions and published ports unchanged, and never copies
   app environment credentials into the new worker. The worker is bounded to one
   CPU/256 MiB. Review and validate the candidate before replacing any live file.
5. Stop **only the legacy RSS bridge**, wait for its process to settle, and create
   a consistent SQLite backup with SQLite's backup API. Export that offline copy
   with `scripts/export-rss-wallabag.py` server-locally. Do not start the integrated
   worker yet. The final export must preserve every feed and seen record; the
   original mirror URLs must not replace original seen identities.
6. Pause only the old application for the migration window. Run the new image's
   `console doctrine:migrations:migrate --env=prod --no-interaction --no-debug`
   using the server-local configuration and existing DB network. Verify migration
   ledger and new tables/columns. Never run `wallabag:install` on existing data.
7. For the verified legacy bridge owner, run
   `console wallabag:rss:import USER_ID /dev/stdin --env=prod --no-debug` with the
   export on stdin. It validates URLs and merges transactionally and idempotently.
   Explicit deployment consent may add `--enable-x-article-reader` for that owner
   only. Compare imported subscription/seen counts with the stopped bridge's
   snapshot before scheduling. If any URL fails validation, keep workers stopped
   and resolve/report the specific hashed record; do not silently discard it.
8. Atomically install the validated server-local Compose candidate. Recreate only
   the application, confirm health and source revision, then start **only** its
   integrated `rss-worker` after import verification. It runs the due-feed command
   every minute; each feed remains on a 30-minute cadence. Never overlap legacy
   and integrated ingestion.
9. Verify normal authenticated UI and OAuth API using existing authorized account
   credentials without logging them. Check old entry counts/content hashes and
   unchanged authentication, RSS ownership/status, Special grabber settings and
   the requested public X example. Confirm no private saved data is sent to a
   public reader. Inspect bounded logs, health and exact image/source IDs.

## Rollback

Keep the previous image, original Compose and server-local backups. Stop only the
integrated worker before rollback. The added schema is additive; the original
2.6.14 app can run against the expanded schema, so prefer restoring the exact old
image/config without deleting tables or restoring an older dump over new entries.
Restore the DB backup only if recovery requires it and after accounting for writes
since the snapshot. Keep legacy RSS stopped until deciding which scheduler owns
the imported interval; resuming its old SQLite snapshot may reprocess newer items.
Verify authentication, existing content and health after any rollback. Do not
prune images, reset volumes, restart ingress or change unrelated services.

## Special grabbers

`/grabbers` and OAuth `GET /api/grabbers.json` list fixed X (priority 10) and Medium
(priority 20) rules, per-user enabled state, services and fallback/privacy status.
`PATCH /api/grabbers/{x|medium}.json` accepts boolean `enabled`; X additionally
accepts boolean `article_reader_enabled`. UI writes use CSRF. Unknown keys,
endpoints, executable code and arbitrary URL regex are rejected.

Medium matches canonical story IDs on exact `medium.com`/subdomain hosts. It
extracts only the direct public response with DNS/redirect validation and no
secondary fetches. Completeness is always marked unverified (206); unavailable
content is a 502 bookmark. The legacy substring match and Freedium rewrite are
not retained because they can route around access restrictions. Disabled rules
and unrelated URLs use Wallabag's existing normal extraction path.
