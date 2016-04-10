# Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added

- cea846d documentation about importing large file into nginx Fix #1849: configuration to avoid 413 Request Entity Too Large. (Nicolas Lœuillet)
- c802181 Documentation about wallabag API (Nicolas Lœuillet)
- 4a749ca Round estimated time and add reading speed for Baggy (Nicolas Lœuillet)
- af47742 Documentation about wallabag v1 CLI import (Nicolas Lœuillet)
- 48bb967 Add migrate link in documentation (Nicolas Lœuillet)

### Changed

- c6cbe75 Move tag form in Material theme (Nicolas Lœuillet)

### Fixed

- 7ead8a0 Fix estimated reading time in material view #1893 (Nicolas Lœuillet)
- cb48a56 remove download_picture setting and manage show_printlink in material theme #1867 (Nicolas Lœuillet)
- 808f541 Fix documentation link in developer page (Nicolas Lœuillet)
- 7a2157b Fix typo on configuration page (Nicolas Lœuillet)
- 0c608f1 Change the installation method in issue template (Nicolas Lœuillet)
- 9479ae8 Lock ocramius/proxy-manager (Jeremy Benoist)
- b5cf84b Fix Archive page title translation (Nicolas Lœuillet)
- e71c376 Force user-agent for iansommerville.com (Jeremy Benoist)

### Removed

- 466c0c6 Remove empty portugese documentation (Nicolas Lœuillet)
- 8687bcd Remove keyboard shortcut and add tagging rule panel in baggy (Nicolas Lœuillet)
- 0bb5669 Remove SMTP configuration environment overrides (Mathieu Bruyen)

## [2.0.0] - 2016-04-04
### Added

* save an article, read it, favorite it, archive it. (Hopefully)
* annotations: In each article you read, you can write annotations. ([read the doc](http://doc.wallabag.org/en/v2/user/annotations.html))
* filter entries by reading time, domain name, creation date, status, etc.
* assign tags to entries
* edit article titles
* a REST API ([you can have a look to the documentation](http://v2.wallabag.org/api/doc))
* authorization via oAuth2
* a new default theme, called `material`
* RSS feeds (with ability to limit number of articles)
* create a new account from the config page (for super admin only)
* recover passwords from login page (you have to fill your email on config page)
* picture preview, if available, is displayed for each entry
* Public registration
* migration from wallabag v1/v2 (based on JSON export) ([read the doc](http://doc.wallabag.org/en/v2/user/import.html))
* migration from Pocket (it works, but we need to implement asynchronous import: if you have too many articles, it can fail) ([read the doc](http://doc.wallabag.org/en/v2/user/import.html))
* exports in many formats (PDF, JSON, EPUB, MOBI, XML, CSV and TXT).
* 2-Factor authentication via email ([read the doc](http://doc.wallabag.org/en/v2/user/configuration.html#two-factor-authentication))
* Tagging rule: create a rule to automatically assign tags to entries! ([read the doc](http://doc.wallabag.org/en/v2/user/configuration.html#tagging-rules))
* Occitan, German, French, Turkish, Persian, Romanian, Polish, Danish, Spanish and English translations
* Quickstart for beginners (when you don't have any entries)
* Internal settings for administrator (the account created during installation)
* For 3rd apps developers, a developer page is available to create API token
