# Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## [2.0.1] - 2016-04-11
### Added

- [Documentation about importing large file](http://doc.wallabag.org/en/v2/user/installation.html#installing-on-nginx) into nginx. (Nicolas Lœuillet)
- [Documentation about wallabag API](http://doc.wallabag.org/en/v2/developer/api.html) (Nicolas Lœuillet)
- [#1861](https://github.com/wallabag/wallabag/pull/1861) Round estimated time and add reading speed for Baggy (Nicolas Lœuillet)
- [Documentation about wallabag v1 CLI import](http://doc.wallabag.org/en/v2/user/migration.html#import-via-command-line-interface-cli) (Nicolas Lœuillet)
- [Add migrate link](http://doc.wallabag.org/en/v2/user/migration.html) in documentation (Nicolas Lœuillet)

### Changed

- [#1823](https://github.com/wallabag/wallabag/pull/1823) Persian translation (Masoud Abkenar)
- [#1901](https://github.com/wallabag/wallabag/pull/1901) Spanish translation (Jeremy Benoist)
- [#1879](https://github.com/wallabag/wallabag/pull/1879) Move tag form in Material theme (Nicolas Lœuillet)

### Fixed

- [#1903](https://github.com/wallabag/wallabag/pull/1903) Force server version to avoid connection error (Jeremy Benoist)
- [#1887](https://github.com/wallabag/wallabag/pull/1887) Fix duplicate article when added via the bookmarklet (Nicolas Lœuillet)
- [#1895](https://github.com/wallabag/wallabag/pull/1895) API: All the entries are fetched via GET /api/entries (Nicolas Lœuillet)
- [#1898](https://github.com/wallabag/wallabag/pull/1898) Fix estimated reading time in material view #1893 (Nicolas Lœuillet)
- [#1896](https://github.com/wallabag/wallabag/pull/1896) remove download_picture setting and manage show_printlink in material theme #1867 (Nicolas Lœuillet)
- [#1894](https://github.com/wallabag/wallabag/pull/1894) Fix documentation link in developer page (Nicolas Lœuillet)
- [#1891](https://github.com/wallabag/wallabag/pull/1891) Fix typo on configuration page (Nicolas Lœuillet)
- [#1884](https://github.com/wallabag/wallabag/pull/1884) Change the installation method in issue template (Nicolas Lœuillet)
- [#1844](https://github.com/wallabag/wallabag/pull/1844) Lock ocramius/proxy-manager (Jeremy Benoist)
- [#1840](https://github.com/wallabag/wallabag/pull/1840) Fix Archive page title translation (Nicolas Lœuillet)
- [#1801](https://github.com/wallabag/wallabag/pull/1804) Force user-agent for iansommerville.com (Jeremy Benoist)

### Removed

- [#1900](https://github.com/wallabag/wallabag/pull/1900) Remove empty portugese documentation (Nicolas Lœuillet)
- [#1868](https://github.com/wallabag/wallabag/pull/1868) Remove keyboard shortcut and add tagging rule panel in baggy (Nicolas Lœuillet)
- [#1824](https://github.com/wallabag/wallabag/pull/1824) Remove SMTP configuration environment overrides (Mathieu Bruyen)

## [2.0.0] - 2016-04-03
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
