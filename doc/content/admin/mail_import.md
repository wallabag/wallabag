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
  address on a dedicated domain. The private address is read from the `To` and
  `Cc` headers, then from `Delivered-To` and `X-Original-To`, so emails sent
  in Bcc work when your mail server adds one of them.
- The PHP extensions `zip` and `fileinfo`, required by the IMAP library.
- The database migrations must be up to date
  (`bin/console doctrine:migrations:migrate --env=prod`).

## Configuration

Set these environment variables, for example in `.env.local`:

| Name | Description | Default |
| -----|-------------|---------|
| WALLABAG_MAIL_IMPORT_ADDRESS | Address of the dedicated mailbox. The private addresses are built from it | empty |
| WALLABAG_MAIL_IMPORT_DSN | Connection to the mailbox, see below | empty |
| WALLABAG_MAIL_IMPORT_MAX_LINKS | Maximum number of links saved from a single email, at least `1` | `10` |

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

## Check the configuration

```console
bin/console wallabag:import:mail --check --env=prod
```

The command connects to the mailbox, lists its folders and tells you what
is wrong when it fails (unreachable host, wrong credentials, missing folder).
It does not change anything in the mailbox.

## Run the import periodically

Add a cron job, for example every 5 minutes:

```
*/5 * * * * cd /var/www/wallabag && php bin/console wallabag:import:mail --env=prod
```

Each run reads at most 50 emails, the oldest first, the next ones are read
on the following run. Change it with `--limit`. Two imports never run at the
same time.

The command prints nothing when no article was saved, so a cron setup sending
the output by email stays quiet. Errors are always printed, and the command
exits with a non-zero status.

## What happens to the emails

- Processed email: deleted from the mailbox.
- Email for an unknown or revoked address, or for a disabled user: moved to
  the `wallabag-rejected` folder (created automatically) and deleted 30 days
  after the rejection.
- Email that could not be read or saved (database error, invalid message…): kept in the
  mailbox, the next run tries again. The other emails are still processed.

When a website cannot be fetched, wallabag saves the link as an entry without
content, like when you add it from the interface, and deletes the email. The
entry can be reloaded later from wallabag: the command does not retry it.

## Security

Anyone who knows a private address can add links to the account of its owner.
Users can regenerate or revoke it at any time: the old one stops working and
the emails sent to it are moved to `wallabag-rejected`.
