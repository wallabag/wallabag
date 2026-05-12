---
title: Parameters
weight: 7
---

## Environment-first configuration

wallabag is configured with environment variables.

- For local development, put overrides in `.env.local`.
- For tests, put overrides in `.env.test.local`.
- For Docker-based local development, copy `docker/php/env.example` to
  `docker/php/env` for Docker-specific overrides.
- For production, export the variables through your web server, PHP-FPM pool,
  systemd unit, or container runtime.

Keep `DATABASE_URL` in `.env.local` and `.env.test.local` so development and
test can choose different databases, even when you run wallabag in Docker.

{{< callout type="info" >}}
Fresh installs should not create `app/config/parameters.yml`. Upgraded installs
may keep it temporarily: wallabag still reads that file for backward
compatibility, but booting with it emits a deprecation notice and support will
be removed in wallabag 3.0.
{{< /callout >}}

{{< callout type="info" >}}
After changing configuration in production, clear the cache with
`bin/console cache:clear --env=prod`.
{{< /callout >}}

## Symfony runtime variables

| Name | Description | Default |
| -----|-------------|---------|
| APP_ENV | Symfony environment | `dev` |
| APP_DEBUG | Symfony debug flag | `1` in dev, `0` in prod |
| APP_SECRET | Secret used for security-related operations | `ch4n63m31fy0uc4n` in `.env`, set your own value in production |

## Application variables

| Name | Description | Default |
| -----|-------------|---------|
| DEFAULT_LOCALE | Default language of your wallabag instance | `en` |
| WALLABAG_BASE_URL | Full URL of your wallabag instance without a trailing slash | `http://127.0.0.1:8000` |
| WALLABAG_REGISTRATION_ENABLED | Enable public registration | `0` |
| WALLABAG_CONFIRMATION_ENABLED | Send a confirmation email for each registration | `1` |
| WALLABAG_FROM_EMAIL | Address used in the `From:` field for application emails | `wallabag@example.com` |
| WALLABAG_SERVER_NAME | User-friendly name of your instance for 2FA issuer strings | `Your wallabag instance` |
| WALLABAG_TWOFACTOR_SENDER | Sender address for emailed 2FA codes | `no-reply@wallabag.org` |
| WALLABAG_USER_AGENT | Default User-Agent used when wallabag fetches content | `Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.92 Safari/535.2` |
| WALLABAG_OAUTH_ACCESS_TOKEN_LIFETIME | OAuth access-token lifetime in seconds | `3600` |
| WALLABAG_OAUTH_REFRESH_TOKEN_LIFETIME | OAuth refresh-token lifetime in seconds | `1209600` |
| WALLABAG_TABLE_PREFIX | Prefix added to wallabag database tables | `wallabag_` |
| WALLABAG_SITE_CONFIG_FOLDERS | Optional comma-separated list of extra graby site-config folders | empty |
| SENTRY_DSN | Optional Sentry DSN used to report application errors | empty |

## Service variables

| Name | Description | Default |
| -----|-------------|---------|
| DATABASE_URL | Doctrine connection string for SQLite, MySQL/MariaDB, or PostgreSQL | `sqlite:///%kernel.project_dir%/data/db/wallabag.sqlite` |
| MAILER_DSN | Symfony Mailer DSN | `smtp://127.0.0.1` |
| REDIS_URL | Redis connection string used by the async import worker | `redis://127.0.0.1:6379` |
| RABBITMQ_URL | RabbitMQ connection string used by the async import worker | `amqp://guest:guest@127.0.0.1:5672` |
| WALLABAG_RABBITMQ_PREFETCH_COUNT | RabbitMQ consumer prefetch value | `10` |

## Legacy mapping for upgraded installs

If an upgraded installation still has `app/config/parameters.yml`, wallabag maps
the legacy keys below to the new environment-variable interface at boot time.

| Legacy key | Environment variable |
| ---------- | -------------------- |
| `secret` | `APP_SECRET` |
| `locale` | `DEFAULT_LOCALE` |
| `domain_name` | `WALLABAG_BASE_URL` |
| `mailer_dsn` | `MAILER_DSN` |
| `fosuser_registration` | `WALLABAG_REGISTRATION_ENABLED` |
| `fosuser_confirmation` | `WALLABAG_CONFIRMATION_ENABLED` |
| `from_email` | `WALLABAG_FROM_EMAIL` |
| `server_name` | `WALLABAG_SERVER_NAME` |
| `twofactor_sender` | `WALLABAG_TWOFACTOR_SENDER` |
| `rabbitmq_prefetch_count` | `WALLABAG_RABBITMQ_PREFETCH_COUNT` |
| `database_*` | `DATABASE_URL` |
| `redis_*` | `REDIS_URL` |
| `rabbitmq_*` | `RABBITMQ_URL` |
| `sentry_dsn` | `SENTRY_DSN` |
| `wallabag_user_agent` | `WALLABAG_USER_AGENT` |
| `fos_oauth_server_access_token_lifetime` | `WALLABAG_OAUTH_ACCESS_TOKEN_LIFETIME` |
| `fos_oauth_server_refresh_token_lifetime` | `WALLABAG_OAUTH_REFRESH_TOKEN_LIFETIME` |
| `database_table_prefix` | `WALLABAG_TABLE_PREFIX` |
