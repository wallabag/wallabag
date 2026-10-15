# Isolated MariaDB and real-browser validation

Status: **executed successfully on 2026-10-15** after Startup and Integration's
explicit settled returns. MariaDB **10.11.19** completed all **57 migrations / 165
queries**, fixtures and ORM mapping validation. The feature regression suite
passed **58 tests / 162 assertions**. Real Chromium passed the flows below at
1440×1000 and 390×844; all **69 nonempty source blocks** of the public article
were also present in the rendered reading view. Earlier SQLite/BrowserKit results
remain documented separately in `local-validation.md`.

Visual inspection found and fixed Materialize checkbox markup in the RSS and
reader-consent forms, plus oversized RSS headings causing mobile overflow. The
final captures were inspected: both controls work through their visible labels,
RSS has no horizontal overflow, and article attribution and linked-media notices
are visible. The release image's CLI-server static shortcut produced invalid
responses for Chromium; the isolated router now serves static files explicitly.
This changes the test harness only.

Screenshots and receipts are retained locally under `.validation/visual-evidence`.
The screenshots have been delivered as native Library image attachments. The
article preview is a genuine 1440×500 browser clip; the full body was checked
separately. Library identity receipts remain in local validation evidence.

All owned containers and both synthetic socket/data volumes were removed; the
owned PHP image and newly pulled MariaDB/Chromium base images were removed too.
No network or published port was created, and foreign resources were preserved.
No production migration, deployment or merge was performed. This is MariaDB
compatibility evidence, not a production-version or full-schema certification.

`scripts/prepare-visual-validation.py` copies tracked application sources into
`.validation/visual-app`, excluding data, vendor, caches and local parameters.
The generated configuration selects a new `wallabag_visual_synthetic` MariaDB
database, native browser sessions, real translations, and a synthetic HTTP client.
Production entrypoints and configuration never load that client. Unexpected
fixture URLs fail closed; both application containers have network mode `none`.

After the shared Mac's runtime owner explicitly releases the agreed slot:

1. Run `python3 scripts/local-validation setup` to recreate the bounded PHP test
   image and offline dependencies using the existing documented workflow. Do not
   overlap its setup with another heavy runtime owner.
2. Cache official MariaDB 10.11 and Microsoft Playwright `v1.63.0-noble` images.
   Record their immutable repo digests and whether each image existed beforehand.
   This is a compatibility target, not a claim that production runs that DB version.
   Run `python3 scripts/visual-validation/cache-playwright.py` to cache the pinned
   public client in the task's own `.validation/playwright` directory. It verifies
   npm SHA-512 integrity and records a receipt, without executing package scripts.
   Do not borrow Nomos dependencies or caches.
3. Prepare the staging tree with `python3 scripts/prepare-visual-validation.py`.
   If it already exists, inspect and remove only that generated staging directory
   before recreating it. It contains synthetic configuration only.
4. Run:

   ```sh
   python3 scripts/visual-validation/run.py --runtime-slot-released \
     --mariadb-image 'mariadb:10.11@sha256:REPLACE_WITH_VERIFIED_DIGEST' \
     --browser-image 'mcr.microsoft.com/playwright:v1.63.0-noble@sha256:REPLACE_WITH_VERIFIED_DIGEST' \
     --public-x-receipt .validation/x-research/fx-v2.json
   ```

   The flag records an operator assertion; it cannot authorize a slot or establish
   that another task has released it. The runner never pulls images itself.

   The optional public receipt captures the user-supplied post's article,
   **Loops You Can Trust**, through the normal new-entry UI and real sanitizer.
   Its exact SHA-256 is verified before mounting it read-only. The receipt was
   previously retrieved through the authorized public reader; this replay adds
   no live X requests and does not claim independent verification against X.
   It is not checked into Git. Without that option, article screens are synthetic.

For server-only database regressions, `--database-only` omits the browser image
and Playwright package requirements. Supply a verified cached MariaDB digest and
prepare the same synthetic staging tree. This runs migrations, fixtures, mapping
validation and PHP tests over the private Unix socket, then removes its two own
containers and two volumes. It does not claim real Chromium coverage for that run.

The runner creates two labeled volumes (synthetic DB data and its Unix socket),
three labeled containers, and no Docker network. MariaDB disables TCP networking;
PHP connects over the private socket. The browser shares only the network-none
PHP container namespace and accesses its internal loopback server. There are no
published ports, host network/IPC, Docker-socket mounts, production data mounts,
host credential forwarding, or global cleanup commands.

Limits are one CPU per container, 256 MiB for PHP, 512 MiB for MariaDB and 768 MiB
for Chromium, with private 256 MiB shared memory for Chromium. These three
containers run together only within the coordinated validation interval. Browser
requests to other origins are aborted. The official image runs trusted synthetic
tests as root with Chromium's default sandbox disabled; no untrusted site is opened.

Validation first runs the real migration history, synthetic fixtures, ORM mapping
validation, and the feature regression suite against MariaDB. Mapping validation
uses `--skip-sync`; it does not certify absence of legacy schema drift. The real
Chromium suite then logs in normally and exercises RSS creation, disabling,
legacy import, deletion, reader consent and normal new-entry X saves, checking
full synthetic article text, partial capture and unavailable-content presentation.
It records desktop/mobile screenshots, checks mobile horizontal overflow and fails
on browser JavaScript exceptions. This is browser-flow coverage and visual evidence,
not pixel-baseline regression testing or independent verification of X's live body.
CLI tests use mock sessions; the generated staged configuration switches to native
sessions and clears its own test cache before actual browser requests. Bundle
assets are copied into the synthetic web directory with the standard Symfony
asset installer. The source checkout's configuration and data are not used for writes.

Receipts and logs are under `.validation/visual-evidence`; inspect the screenshots
before reporting visual success. `runtime-result.json` contains image IDs, DB
version, pass/failure state and cleanup results. Cleanup runs on exceptions and
removes only resources created in that invocation after verifying their ownership
labels. Interrupted execution may require exact-resource cleanup after inspecting
the recorded names. Never prune global Docker resources.

Finally run `python3 scripts/local-validation cleanup`, remove only newly pulled
unused base images by their exact verified references (without force), verify no
owned processes/listeners/containers/volumes remain, retain useful evidence, and
return the runtime slot explicitly. No production deployment or merge is included.

Official runtime references: [Playwright Docker documentation](https://playwright.dev/docs/docker)
and [MariaDB official image](https://hub.docker.com/_/mariadb).
