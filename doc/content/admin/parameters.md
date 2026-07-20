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
| WALLABAG_CAPTCHA_ENABLED | Require an image CAPTCHA for HTML user creation | `0` |
| WALLABAG_CONFIRMATION_ENABLED | Send a confirmation email for each registration | `1` |
| WALLABAG_FROM_EMAIL | Address used in the `From:` field for application emails | `wallabag@example.com` |
| WALLABAG_SERVER_NAME | User-friendly name of your instance for 2FA issuer strings | `Your wallabag instance` |
| WALLABAG_TWOFACTOR_SENDER | Sender address for emailed 2FA codes | `no-reply@wallabag.org` |
| WALLABAG_LOG_LEVEL | Minimum level written by the nested production log handler | `error` |
| WALLABAG_USER_AGENT | Default User-Agent used when wallabag fetches content | `Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.92 Safari/535.2` |
| WALLABAG_FETCH_BLOCKED_HOSTS | Optional comma-separated hostname deny-list for outgoing fetches | empty |
| WALLABAG_OAUTH_ACCESS_TOKEN_LIFETIME | OAuth access-token lifetime in seconds | `3600` |
| WALLABAG_OAUTH_REFRESH_TOKEN_LIFETIME | OAuth refresh-token lifetime in seconds | `1209600` |
| WALLABAG_TABLE_PREFIX | Prefix added to wallabag database tables | `wallabag_` |
| WALLABAG_SITE_CONFIG_FOLDERS | Optional comma-separated list of extra graby site-config folders | empty |
| WALLABAG_ARTICLE_REPORTING_URL | Destination used to report display problems with an article | `mailto:siteconfig@wallabag.org?subject=Wrong%20display%20in%20wallabag` |
| SENTRY_DSN | Optional Sentry DSN used to report application errors | empty |

### CAPTCHA protection for user creation

Set `WALLABAG_CAPTCHA_ENABLED=1` to require a CAPTCHA on public registration
and administrator user-creation forms. The default value, `0`, leaves both
forms unchanged. Clear the production cache after changing the value:

```console
bin/console cache:clear --env=prod
```

Each challenge is stored in the visitor's session. Loading a new image or
submitting the form invalidates the previous answer, including after an
incorrect submission. The challenge has no clock-based expiry; its lifetime is
defined by those refresh and submission events.

The CAPTCHA is image-only and has no audio alternative. For users who cannot
complete it, an administrator should create the account through the protected
administrator form. API user registration remains CAPTCHA-free and is outside
this feature's scope.

A CAPTCHA is only one abuse-prevention layer. It does not replace rate limits,
reverse-proxy controls, or a web application firewall.

### Production log verbosity

`WALLABAG_LOG_LEVEL` controls the minimum level written by the nested Monolog
handler in production. Monolog accepts `debug`, `info`, `notice`, `warning`,
`error`, `critical`, `alert`, and `emergency`.

The main `fingers_crossed` handler continues to activate only at `error`.
Lowering `WALLABAG_LOG_LEVEL` therefore adds buffered context when a request
ends in an error without logging successful requests. For example:

```dotenv
WALLABAG_LOG_LEVEL=debug
```

After changing the value, clear the production cache:

```console
bin/console cache:clear --env=prod
```

### Outgoing fetch hostname deny-list

Set `WALLABAG_FETCH_BLOCKED_HOSTS` to prevent wallabag fetch clients from
requesting selected hostnames. Separate multiple rules with commas:

```dotenv
WALLABAG_FETCH_BLOCKED_HOSTS=example.com,.internal.example,192.0.2.1,2001:db8::1
```

A rule without a leading dot matches only that exact hostname. A rule with one
leading dot matches both the named hostname and all descendants.

| Rule | Blocked examples | Not blocked examples |
| ---- | ---------------- | -------------------- |
| `example.com` | `example.com` | `www.example.com` |
| `.example.com` | `example.com`, `www.example.com`, `deep.api.example.com` | `badexample.com` |
| `my.example.com` | `my.example.com` | `api.my.example.com` |
| `.my.example.com` | `my.example.com`, `api.my.example.com` | `example.com`, `your.example.com` |

Rules are trimmed, lowercased, stripped of a terminal DNS dot, and converted
from internationalized names to UTS46 ASCII form. A port in a requested URL is
ignored while matching, but configuration rules must not contain ports. IPv4
and IPv6 literals are supported as exact rules only; leading-dot IP rules are
invalid.

wallabag checks the initial destination and every HTTP redirect before sending
the next request. Invalid configuration prevents the fetch clients from being
created. Schemes, paths, user information, wildcards, regular expressions,
malformed hostnames, and more than one leading dot are not accepted. After
changing the value in production, run `bin/console cache:clear --env=prod`.

This setting is an operator-managed hostname deny-list, not complete SSRF
protection. It does not resolve DNS names or classify public and private address
ranges. Use network-level egress controls when requests must be isolated from
internal services.

## Service variables

| Name | Description | Default |
| -----|-------------|---------|
| DATABASE_URL | Doctrine connection string for SQLite, MySQL/MariaDB, or PostgreSQL | `sqlite:///%kernel.project_dir%/data/db/wallabag.sqlite` |
| MAILER_DSN | Symfony Mailer DSN | `smtp://127.0.0.1` |
| REDIS_URL | Redis connection string used by the async import worker | `redis://127.0.0.1:6379` |
| RABBITMQ_URL | RabbitMQ connection string used by the async import worker | `amqp://guest:guest@127.0.0.1:5672` |
| WALLABAG_RABBITMQ_PREFETCH_COUNT | RabbitMQ consumer prefetch value | `10` |

## Article problem-reporting destination

`WALLABAG_ARTICLE_REPORTING_URL` accepts an absolute `https:` URL or a
`mailto:` URI with one recipient. An unset or empty value uses the default
address shown above. Invalid values stop the Symfony container from compiling.

Query names and values in the configured URL must use RFC 3986 percent
encoding. wallabag preserves configured query parameters, including a custom
`subject`, but always replaces `body` with the URL of the article being
reported. Fragments are preserved.

```dotenv
WALLABAG_ARTICLE_REPORTING_URL="https://support.example.com/issues/new?template=article"
WALLABAG_ARTICLE_REPORTING_URL="mailto:support@example.com?subject=Article%20display%20problem"
```

Because the value is validated and stored while building the container, clear
the application cache after changing it.

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
