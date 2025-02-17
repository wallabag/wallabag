---
name: Bug report
about: Create a report to help us improve
title: ''
labels: ''
assignees: ''

---

<!--
Thank you for reporting an issue.

Please fill in as much of the template below as you're able.

Version:      if you know it, otherwise use the git revision
Installation: How did you install wallabag? Using git clone, the docker image, an installer, downloading the package, etc.
PHP version:  The version of PHP you are using
OS:           The host running wallabag
Database:     The storage system your instance is using (SQLite, MySQL/MariaDB or PostgreSQL) with the version
Parameters:   Put the content of your environment variables (hide sensitive stuff if you want)
-->
### Environment

* **Version**:
* **Installation**:
* **PHP version**:
* **OS**:
* **Database**:
* **Parameters**:

<details>
  <summary>My environment variables are:</summary>

  ```
  LOCALE=

  # Make sure to hide username and password below, if any
  DATABASE_URL=
  DATABASE_TABLE_PREFIX=

  FOSUSER_REGISTRATION=
  FOSUSER_CONFIRMATION=

  FOS_OAUTH_SERVER_ACCESS_TOKEN_LIFETIME=
  FOS_OAUTH_SERVER_REFRESH_TOKEN_LIFETIME=
  TWOFACTOR_SENDER=

  # Make sure to hide username and password below, if any
  MAILER_DSN=
  FROM_EMAIL=

  RABBITMQ_HOST=
  RABBITMQ_PORT=

  REDIS_SCHEME=
  REDIS_HOST=
  REDIS_PORT=
  REDIS_PATH=
  RABBITMQ_PREFETCH_COUNT=

  # Make sure to hide username and password below, if any
  SENTRY_DSN=
  ```
</details>

### What steps will reproduce the bug?

<!--
Enter details about your bug and how to reproduce it
-->
