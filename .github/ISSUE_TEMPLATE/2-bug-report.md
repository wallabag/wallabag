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
Configuration: Paste the relevant environment variables or describe how you provide them (hide secrets if you want)
-->
### Environment

* **Version**:
* **Installation**:
* **PHP version**:
* **OS**:
* **Database**:
* **Configuration**:

<details>
  <summary>Relevant environment variables</summary>

  ```dotenv
  APP_ENV=prod
  DATABASE_URL=...
  MAILER_DSN=...
  REDIS_URL=...
  RABBITMQ_URL=...
  WALLABAG_BASE_URL=...
  WALLABAG_TABLE_PREFIX=...
  WALLABAG_REGISTRATION_ENABLED=...
  WALLABAG_CONFIRMATION_ENABLED=...
  ```
</details>

### What steps will reproduce the bug?

<!--
Enter details about your bug and how to reproduce it
-->
