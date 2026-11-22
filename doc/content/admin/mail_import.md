---
title: Save articles by email
weight: 10
---

Users can save an article by sending its link to a private email address.
wallabag reads a mailbox dedicated to your instance and saves the links of
each email in the account of its recipient. The feature is disabled by default.

## How it works

1. You create a mailbox dedicated to wallabag, for example `wallabag@example.org`.
2. Each user creates a private address in **Config** → **Save by email**,
   like `wallabag+a1b2c3d4e5f6a7b8c9d0@example.org`.
3. A command, run periodically, reads the mailbox, finds the user from the
   token after the `+`, and saves the `http(s)` links found in the email.

## Requirements

- A mailbox reachable over IMAP, used only by wallabag: processed emails are
  deleted from it.
- Emails sent to `wallabag+anything@example.org` must reach this mailbox.
  Most providers support this "subaddressing", otherwise use a catch-all
  address on a dedicated domain.
- The database migrations must be up to date
  (`bin/console doctrine:migrations:migrate --env=prod`).

## Configuration

Set these environment variables, for example in `.env.local`:

| Name | Description | Default |
| -----|-------------|---------|
| WALLABAG_MAIL_IMPORT_ADDRESS | Address of the dedicated mailbox. The private addresses are built from it | empty |
| WALLABAG_MAIL_IMPORT_DSN | Connection to the mailbox, see below | empty |
| WALLABAG_MAIL_IMPORT_MAX_LINKS | Maximum number of links saved from a single email | `10` |

The feature is enabled when both `WALLABAG_MAIL_IMPORT_ADDRESS` and
`WALLABAG_MAIL_IMPORT_DSN` are set.

The DSN looks like `imaps://USER:PASSWORD@HOST:PORT/FOLDER`:

| Example | Connection |
| --------|------------|
| `imaps://wallabag%40example.org:secret@imap.example.org:993/INBOX` | SSL/TLS |
| `imap://wallabag%40example.org:secret@imap.example.org:143/INBOX?encryption=starttls` | STARTTLS |
| `imap://wallabag:secret@127.0.0.1:143/INBOX` | No encryption, for a local server only |

Special characters in the user and the password must be URL-encoded
(`@` becomes `%40`). The port defaults to `993` with `imaps` and `143` with
`imap`, the folder to `INBOX`.

## Run the import periodically

Add a cron job, for example every 5 minutes:

```
*/5 * * * * cd /var/www/wallabag && php bin/console wallabag:import:mail --env=prod
```

Each run reads at most 50 emails, the oldest first, and deletes them from the
mailbox, including the ones sent to an unknown or revoked address. An email
that could not be processed (website down, database error…) is kept, and the
next run tries again. The command then exits with a non-zero status.

## Security

Anyone who knows a private address can add links to the account of its owner.
Users can regenerate it at any time: the old one stops working.
