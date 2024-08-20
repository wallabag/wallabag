---
title: Mailer
weight: 8
---

Out of the box, you can deliver emails over:
- SMTP (using a DSN like: `smtp://user:pass@smtp.example.com:25`)
- or `sendmail` (using a DSN like `sendmail://default`).

Since 2.6.6, the `gmail` transport is available again using the DSN: `gmail+smtp://USERNAME:PASSWORD@default`

| Name | Description | Default |
| -----|-------------|-------- |
| mailer_dsn | One liner with all the mailer parameters `smtp://user:pass@host:465`. Any characters considered special need to be urlencoded in `user`, `pass` and `host`. | smtp://127.0.0.1 |

note: In a case, that after supplying urlencoded characters, the start up process crashes, try "escaping" them with additional % character:
eg. pass%20word -> pass%%20word. Might be useful for Google App Passwords.

{{< callout type="info" >}}
Symfony can support other transports which aren't shipped by default with wallabag: Amazon SES, MailChimp, Mailgun, Postmark & SendGrid.

You can install them using Composer. It's a more complex step to do, [check the Symfony documentation about that](https://symfony.com/doc/4.4/mailer.html).
{{< /callout >}}

## Before wallabag 2.6.1

On wallabag < 2.6.1, the mailer was different (we were using an older version of Symfony).

Here are the previous parameters available.

| Name | Description | Default |
| -----|-------------|-------- |
| mailer_transport | The exact transport method to use to deliver emails. Valid values are: `smtp`, `gmail`, `sendmail`, `null` (which will disable the mailer) | smtp |
| mailer_user | The username when using `smtp` as the transport. | ~ |
| mailer_password | The password when using `smtp` as the transport. | ~ |
| mailer_host | The host to connect to when using `smtp` as the transport.| 127.0.0.1 |
| mailer_port (**new in 2.4.0**) | The port when using `smtp` as the transport. This defaults to 465 if encryption is `ssl` and 25 otherwise.| false |
| mailer_encryption (**new in 2.4.0**) | The encryption mode to use when using smtp as the transport. Valid values are `tls`, `ssl`, or `null` (indicating no encryption).| ~ |
| mailer_auth_mode (**new in 2.4.0**) | The authentication mode to use when using smtp as the transport. Valid values are `plain`, `login`, `cram-md5`, or `null`.| ~ |
