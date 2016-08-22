# Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## [2.0.7] - 2016-08-22

### Added

- [#2222](https://github.com/wallabag/wallabag/pull/2222) Added creation date and reading time on article view (Nicolas Lœuillet)
- [#2134](https://github.com/wallabag/wallabag/pull/2134) Run tests on an uptodate HHVM (Jeremy Benoist)

### Changed

- [#2221](https://github.com/wallabag/wallabag/pull/2221) Replaced favorite word/icon with star one (Nicolas Lœuillet)

### Fixed

- [#2224](https://github.com/wallabag/wallabag/pull/2224) Avoid breaking import when fetching fail (Jeremy Benoist)
- [#2216](https://github.com/wallabag/wallabag/pull/2216), [#2220](https://github.com/wallabag/wallabag/pull/2220) Enable CORS headers for OAUTH part (Rurik19)
- [#2095](https://github.com/wallabag/wallabag/pull/2095) Fix form user display when 2FA is disabled (Nicolas Lœuillet)

## [2.0.6] - 2016-08-10

### Changed

- [#2199](https://github.com/wallabag/wallabag/pull/2199) Handling socials links into a config file (Simon Alberny)
- [#2172](https://github.com/wallabag/wallabag/pull/2172) Change the way to login user in tests (Jeremy Benoist)
- [#2155](https://github.com/wallabag/wallabag/pull/2155) Use friendsofphp instead of fabpot for PHP CS Fixer (Jeremy Benoist)

### Fixed

- [#2200](https://github.com/wallabag/wallabag/pull/2200) Fixed typo in entry:notice:entry_saved (charno6)
- [#2185](https://github.com/wallabag/wallabag/pull/2185) Fix 3rd-Party Apps links (Chrome & Firefox) (Thomas Citharel)
- [#2165](https://github.com/wallabag/wallabag/pull/2165) Fix a few french translations typos (Thomas Citharel)
- [#2157](https://github.com/wallabag/wallabag/pull/2157) Handle only upper or only lower reading filter (Jeremy Benoist)
- [#2156](https://github.com/wallabag/wallabag/pull/2156) Try to find bad redirection after delete (Jeremy Benoist)

## [2.0.5] - 2016-05-31

### Added

- [#2052](https://github.com/wallabag/wallabag/pull/2052) Add unread filter to entries pages (Dan Bartram)

### Changed

- [#2093](https://github.com/wallabag/wallabag/pull/2093) Replace vertical dots in material theme with horizontal dots (Nicolas Lœuillet)
- [#2054](https://github.com/wallabag/wallabag/pull/2054) Update italian translation (Daniele Conca)
- [#2068](https://github.com/wallabag/wallabag/pull/2068), [#2049](https://github.com/wallabag/wallabag/pull/2049) Update documentation (Josh Panter, Mario Vormstein)

### Fixed

- [#2122](https://github.com/wallabag/wallabag/pull/2122) Fix the deletion of Tags/Entries relation when delete an entry (Jeremy Benoist, Nicolas Lœuillet)
- [#2095](https://github.com/wallabag/wallabag/pull/2095) Fix form user display when 2FA is disabled (Nicolas Lœuillet)
- [#2092](https://github.com/wallabag/wallabag/pull/2092) API: Starred and archived clears if article is already exists (Rurik19)
- [#2097](https://github.com/wallabag/wallabag/issues/2097) Fix image path in 2-factor authentification email (Baptiste Mille-Mathias)
- [#2069](https://github.com/wallabag/wallabag/pull/2069) Do not specify language in Firefox addon link (Merouane Atig)

## [2.0.4] - 2016-05-07

### Added

- [#2016](https://github.com/wallabag/wallabag/pull/2016) Big updates in [our documentation](http://doc.wallabag.org/en/master/) (Nicolas Lœuillet)
- [#2028](https://github.com/wallabag/wallabag/pull/2028) Documentation about android application (Strubbl)
- [#2019](https://github.com/wallabag/wallabag/pull/2019) Italian translation (Daniele Conca)
- [#2011](https://github.com/wallabag/wallabag/pull/2011) Documentation about wallabag upgrade (biva)
- [#1985](https://github.com/wallabag/wallabag/pull/1985) Documentation about rights access (FoxMaSk)
- [#1969](https://github.com/wallabag/wallabag/pull/1969) Third resources for API in documentation (Nicolas Lœuillet)
- [#1967](https://github.com/wallabag/wallabag/pull/1967) FAQ page in documentation (Nicolas Lœuillet)

### Changed

- [#1977](https://github.com/wallabag/wallabag/pull/1977) Spanish documentation (jami7)

### Fixed

- [#2023](https://github.com/wallabag/wallabag/pull/2023) Fix translation for validators (Nicolas Lœuillet)
- [#2020](https://github.com/wallabag/wallabag/pull/2020) Fix number of entries in tag/list (Nicolas Lœuillet)
- [#2022](https://github.com/wallabag/wallabag/pull/2022) Fix pagination bar on small devices (Nicolas Lœuillet)
- [#2013](https://github.com/wallabag/wallabag/pull/2013) Fix tag listing (Nicolas Lœuillet)
- [#1976](https://github.com/wallabag/wallabag/pull/1976) Fix filter reading time (Nicolas Lœuillet)
- [#2005](https://github.com/wallabag/wallabag/pull/2005) Fix reading speed not defined when user was created via config page (Nicolas Lœuillet)
- [#2010](https://github.com/wallabag/wallabag/pull/2010) Set the title via POST /api/entries (Nicolas Lœuillet)

## [2.0.3] - 2016-04-22

### Added

- [#1962](https://github.com/wallabag/wallabag/pull/1962) cURL examples in documentation about API (Dirk Deimeke)

### Fixed

- Forgot `composer.lock` file in 2.0.2 release so some users may encounter `Fatal error: Out of memory` error during installation

## [2.0.2] - 2016-04-21

### Added

- [#1945](https://github.com/wallabag/wallabag/pull/1945) cURL examples in documentation about API (Dirk Deimeke)
- [#1911](https://github.com/wallabag/wallabag/pull/1911) Add title hover on entry titles (Thomas Citharel)

### Changed

- [#1944](https://github.com/wallabag/wallabag/pull/1944), [#1953](https://github.com/wallabag/wallabag/pull/1953), [#1954](https://github.com/wallabag/wallabag/pull/1954) Due to branches renaming, update documentation and configuration (maxi62330, Nicolas Lœuillet)
- [#1942](https://github.com/wallabag/wallabag/pull/1942) Optimize import (Nicolas Lœuillet)
- [#1935](https://github.com/wallabag/wallabag/pull/1935) Update spanish translation (jami7)
- [#1929](https://github.com/wallabag/wallabag/pull/1929) Change guid and link in RSS feeds to give original entry URL (Nicolas Lœuillet)
- [#1918](https://github.com/wallabag/wallabag/pull/1918) Improve pagination when user has lot of entries (Nicolas Lœuillet)
- [#1916](https://github.com/wallabag/wallabag/pull/1916) Change PHP arrays and move test parameters in a separated file (Jeremy Benoist)

### Fixed

- [#1957](https://github.com/wallabag/wallabag/pull/1957) Fix translation for `Go to your account` button (Nicolas Lœuillet)
- [#1925](https://github.com/wallabag/wallabag/pull/1925) Redirect to homepage if refered is null (Nicolas Lœuillet)
- [#1912](https://github.com/wallabag/wallabag/pull/1912) Fix 500 Internal Server Error -> "Setting piwik_enabled couldn't be found" (Jeremy Benoist)

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
