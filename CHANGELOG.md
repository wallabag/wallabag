# Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## [2.1.1] - 2016-10-03

### Changed

- [#2340](https://github.com/wallabag/wallabag/pull/2340) Updated german translation (Strubbl)
- [#2341](https://github.com/wallabag/wallabag/pull/2341) Updated polish translation (Mateusz Rumiński)

### Fixed

- [#2338](https://github.com/wallabag/wallabag/pull/2338) Fixed 2.1 installation (Jeremy Benoist)
- [#2345](https://github.com/wallabag/wallabag/issues/2345) Fixed 2.0.x update (Jeremy Benoist)

## [2.1.0] - 2016-10-03

### Added

- [#1990](https://github.com/wallabag/wallabag/pull/1990) Added command line import for wallabag files (Nicolas Lœuillet)
- [#2142](https://github.com/wallabag/wallabag/pull/2142) Manage assets through npm (Thomas Citharel)
- [#2174](https://github.com/wallabag/wallabag/pull/2174) Added filter for tags on API (Thomas Citharel)
- [#2176](https://github.com/wallabag/wallabag/pull/2176) Added `since` parameter in API (Thomas Citharel)
- [#2170](https://github.com/wallabag/wallabag/pull/2170), [#2183](https://github.com/wallabag/wallabag/pull/2183) Added tags on entries view (Thomas Citharel)
- [#2186](https://github.com/wallabag/wallabag/pull/2186) Added option to disable registration (Thomas Citharel)
- [#1904](https://github.com/wallabag/wallabag/pull/1904) Share entry with a public URL (Nicolas Lœuillet)
- [#2243](https://github.com/wallabag/wallabag/pull/2243) Added list of untagged articles (Nicolas Lœuillet)
- [#2255](https://github.com/wallabag/wallabag/pull/2255) Added Readability import (Jeremy Benoist)
- [#2002](https://github.com/wallabag/wallabag/pull/2002) Added articles counter in sidebar (Nicolas Lœuillet)
- [#2275](https://github.com/wallabag/wallabag/pull/2275) Added date from entries in export (Jeremy Benoist)
- [#2266](https://github.com/wallabag/wallabag/pull/2266) Added tags counter in sidebar (Nicolas Lœuillet)
- [#1941](https://github.com/wallabag/wallabag/pull/1941) Added asynchronous import (Jeremy Benoist, Nicolas Lœuillet)
- [#2192](https://github.com/wallabag/wallabag/pull/2192) Added Firefox / Chrome bookmarks import (Thomas Citharel)
- [#2310](https://github.com/wallabag/wallabag/pull/2310) Added Instapaper import (Jeremy Benoist)
- [#2322](https://github.com/wallabag/wallabag/pull/2322) Added customized errors templates (Jeremy Benoist)
- [#2323](https://github.com/wallabag/wallabag/pull/2323) Added simple stats in footer (Jeremy Benoist)
- [#2324](https://github.com/wallabag/wallabag/pull/2324) Added ability to edit a tagging rule (Jeremy Benoist)
- [#2325](https://github.com/wallabag/wallabag/pull/2325) Added an `exists` endpoint in API (Jeremy Benoist)

### Changed

- [#2170](https://github.com/wallabag/wallabag/pull/2170) Entry titles are now smaller on entry view (Thomas Citharel)
- [#2245](https://github.com/wallabag/wallabag/pull/2245) Changed where page title is displayed (Nicolas Lœuillet)
- [#2274](https://github.com/wallabag/wallabag/pull/2274) Re use JsonResponse in API (Jeremy Benoist)
- [#2257](https://github.com/wallabag/wallabag/pull/2257) Use created date for imported content (Jeremy Benoist)
- [#2326](https://github.com/wallabag/wallabag/pull/2326) Quickstart layout (Nicolas Lœuillet)

### Fixed 

- [#2328](https://github.com/wallabag/wallabag/pull/2328) Fixed duplicate URL with accents (Jeremy Benoist)
- [#2319](https://github.com/wallabag/wallabag/pull/2319) Fixed `gd` extension missing in Dockerfile (Pascal Martin)
- [#2313](https://github.com/wallabag/wallabag/pull/2313) Fixed long loading on Firefox (Nicolas Lœuillet)
- [#2320](https://github.com/wallabag/wallabag/pull/2320) Fixed user config which wasn't created in some cases (Jeremy Benoist)
- [#2301](https://github.com/wallabag/wallabag/pull/2301) Fixed feeds not syncing on android application (Thomas Citharel)
- [#2308](https://github.com/wallabag/wallabag/pull/2308) Fixed duplicate tags on import (Jeremy Benoist)
- [#2297](https://github.com/wallabag/wallabag/pull/2297) Fixed epub export with special characters in title (morhelluin)
- [#2292](https://github.com/wallabag/wallabag/pull/2292) Fixed label for mark as read link in entry view (Nicolas Lœuillet)
- [#2260](https://github.com/wallabag/wallabag/pull/2260) Fixed different font-size for labels in config screen (Nicolas Lœuillet)
- [#2242](https://github.com/wallabag/wallabag/pull/2242) Fixed print / article views (Nicolas Lœuillet)
- [#2328](https://github.com/wallabag/wallabag/pull/2328) Avoid duplicate url with accents (Jeremy Benoist)
- [#2330](https://github.com/wallabag/wallabag/pull/2330) Remove error message when creating ePub versions (Paulino Michelazzo)
- [#2331](https://github.com/wallabag/wallabag/pull/2331) Fix parameters in API links (Jeremy Benoist)

### Removed

- [#2318](https://github.com/wallabag/wallabag/pull/2318) Removed duplicated templates files (Nicolas Lœuillet)
- [#2287](https://github.com/wallabag/wallabag/pull/2287) Useless area in footer for material theme (Nicolas Lœuillet)

## [2.0.8] - 2016-09-07

### Added

- [#2262](https://github.com/wallabag/wallabag/pull/2262) Added a check for the database connection during installation (Jeremy Benoist)
- [#2235](https://github.com/wallabag/wallabag/pull/2235) Added configuration for german documentation website, [available here](http://doc.wallabag.org/de/latest/) (Nicolas Lœuillet)

### Changed

- [graby](https://github.com/j0k3r/graby/releases/tag/1.4.3) Update Graby version, which now handles ZIP files (Jeremy Benoist)
- [#2230](https://github.com/wallabag/wallabag/pull/2230) Changed title display in card view (Danilow Alexandr)

### Fixed 

- [#2234](https://github.com/wallabag/wallabag/pull/2234) Fixed mailto link in documentation (Christian Studer)
- [#2241](https://github.com/wallabag/wallabag/pull/2241) Fixed the height of the "Add new article" field in Chrome (Danilow Alexandr)
- [#2238](https://github.com/wallabag/wallabag/pull/2238) Fixed login page in Qupzilla (Danilow Alexandr)

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
