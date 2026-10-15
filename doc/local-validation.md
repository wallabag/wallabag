# Isolated local Docker validation

Use a clean isolated checkout with Python 3 and Docker. Do not mount serv01 data, host credentials, the Docker socket or another project's directories into the test container. Coordinate shared Mac runtime ownership before running.

```sh
scripts/local-validation setup
scripts/local-validation test
scripts/local-validation cleanup
```

`setup` builds a release-matched, digest-pinned Wallabag 2.6.14 CLI image. It caches public GitHub archives pinned by the app and dedicated PHPUnit lockfiles on the host. The missing signed Alpine XMLWriter package is downloaded on the host and installed during a network-none image build; APK signature verification stays enabled. No credentials or account setup is required. PHPUnit 9.6.29 is installed from locked source packages because its PHAR endpoint returned 403 during initial preparation. `deps` can resume interrupted offline dependency installation.

All application/Composer/test processes run with **network none, 1 CPU, 256 MiB, 128 PIDs**, no published ports and no Docker volumes. No new Docker network is created. The image entrypoint is overridden, so its web-server/production initialization does not run. The only bind mount is this checkout. Docker build also disables networking and bounds CPU/memory. Resource names derive from the absolute checkout path, and cleanup verifies exact ownership labels before removing anything.

The local app boots through the real Symfony kernel. BrowserKit exercises UI forms, CSRF, sessions, OAuth token issuance, authenticated JSON APIs and real SQLite persistence in process. No live server, host listener, external browser or Nomos resource is needed. **This is functional UI testing, not screenshot/visual or live-network browser verification.**

The test DB path is fixed to `/work/.validation/wallabag_test.sqlite`; the runner checks it before recreating synthetic fixtures. Production environment/DSN variables are not forwarded. The local parameter file contains only a fixed fixture secret, and setup refuses to overwrite an existing configuration file.

## Results (2026-10-15)

- Release runtime: PHP **8.1.32**, PHPUnit **9.6.29**, ARM64 Docker.
- Focused feature plus existing ContentProxy regressions: **90 tests, 380 assertions; 89 passed and 1 existing skipped**. The skip is the upstream PDF invalid-character encoding test, which explicitly skips on PHP >=8.1.
- Application migrations, fixtures, new RSS-table mappings and new preference-column comparison passed. RSS command entrypoint booted successfully against the empty fixture subscription set.
- The legacy SQLite schema is not globally synchronized with ORM metadata: the general SchemaTool diagnostic emits 93 statements in existing tables. `scripts/check-local-rss-schema.php` retains this SQL as evidence, checks new tables/column separately, and compares a legacy-only schema. This is **not** a blanket full-schema pass or MariaDB production migration validation.
- Actual public article replay: all **69 nonempty text blocks of 79 blocks** survived the real sanitizer; see [reader evidence](x-article-readers.md).
- Offline exporter regression: **1 passed**, input SQLite file hash unchanged.
- PHP syntax/style checks use the installed runtime and repository formatter. Legacy dependency deprecations and the upstream PHP >=8.1 skip remain documented.

Logs, JUnit, source receipts, sanitized replay and schema SQL are retained in ignored `.validation/`. The PHP test suite makes no Internet requests; the live provider request was performed separately on the host using only Jon's public example ID.

`cleanup` removes only this checkout's owned disposable containers/images and synthetic SQLite files, and deletes only the parameter file bearing the fixture marker. No network or volume is created, so no network/volume teardown is attempted. Preexisting images and all foreign resources are preserved. Reusable dependency caches and logs remain local; there is no global prune or cleanup of old Docker networks.
