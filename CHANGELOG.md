# Changelog

## Upcoming changes

* **[BC BREAK]** Convert 403 errors to 404 errors by @yguedidi in https://github.com/wallabag/wallabag/pull/8075
* `wallassets/` folder renamed to `build/`

## [2.6.12](https://github.com/wallabag/wallabag/tree/2.6.12)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.11...2.6.12)

### Technical stuff

* Fix changelog by @yguedidi in [https://github.com/wallabag/wallabag/pull/8135](https://github.com/wallabag/wallabag/pull/8135)
* Update dependencies by @yguedidi in [https://github.com/wallabag/wallabag/pull/8136](https://github.com/wallabag/wallabag/pull/8136)


## [2.6.11](https://github.com/wallabag/wallabag/tree/2.6.11)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.10...2.6.11)

### Security fix
* Protect actions with a CSRF token by @yguedidi in https://github.com/wallabag/wallabag/commit/99c8a06594d6ee7480ce4d041ccff3025b353656

### Fixes

* Fix redirection after action in search results by @nicosomb in [https://github.com/wallabag/wallabag/pull/7827](https://github.com/wallabag/wallabag/pull/7827)
* Fix title tag filter by @nicosomb in [https://github.com/wallabag/wallabag/pull/7846](https://github.com/wallabag/wallabag/pull/7846)
* Change NB_ELEMENTS in pocket importer to 30 by @j0k3r in [https://github.com/wallabag/wallabag/pull/7993](https://github.com/wallabag/wallabag/pull/7993)
* Fix entries counter for annotated entries in the menu by @j0k3r in [https://github.com/wallabag/wallabag/pull/7999](https://github.com/wallabag/wallabag/pull/7999)

### Technical stuff

* Prepare 2.6.11 release by @yguedidi in [https://github.com/wallabag/wallabag/pull/8133](https://github.com/wallabag/wallabag/pull/8133)

## [2.6.10](https://github.com/wallabag/wallabag/tree/2.6.10)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.9...2.6.10)

### Improvement

* Add Omnivore import by @nicosomb in https://github.com/wallabag/wallabag/pull/7754

### Fixes

* Update site config & tests by @j0k3r in https://github.com/wallabag/wallabag/pull/7582 (fixes "Key provided is shorter
  than 256 bits, only 240 bits provided" https://github.com/wallabag/wallabag/issues/7531)
* Update site config by @yguedidi in https://github.com/wallabag/wallabag/pull/7623
* Replace gitter with matrix by @nicosomb in https://github.com/wallabag/wallabag/pull/7753

## [2.6.9](https://github.com/wallabag/wallabag/tree/2.6.9)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.8...2.6.9)

### Fixes
* Fix same domain pagination by @yguedidi in https://github.com/wallabag/wallabag/pull/7266
* Upgrade PHP dependencies by @yguedidi in https://github.com/wallabag/wallabag/pull/7272
* Use a proper "how to" for elCurator by @j0k3r in https://github.com/wallabag/wallabag/pull/7323

## [2.6.8](https://github.com/wallabag/wallabag/tree/2.6.8)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.7...2.6.8)

### Fixes
* Update deps & Node 20 by @j0k3r in https://github.com/wallabag/wallabag/pull/7134
* Fix dark mode disabled url 2.6 by @Simounet in https://github.com/wallabag/wallabag/pull/7133
* Make database dependent commands lazy by @yguedidi in https://github.com/wallabag/wallabag/pull/7142
* Fix docker setup by @yguedidi in https://github.com/wallabag/wallabag/pull/7141
* Remove session-based redirection by @yguedidi in https://github.com/wallabag/wallabag/pull/7140

## [2.6.7](https://github.com/wallabag/wallabag/tree/2.6.7)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.6...2.6.7)

### Security fix
* A user can disable her 2FA unintentionally by @kdecherf in https://github.com/wallabag/wallabag/commit/0cfdddc2eb0aee5ffb69bf499d377d75655ba157

### Fixes
* Fix deprecated null tag parameter by @Simounet in https://github.com/wallabag/wallabag/pull/6985
* Full clickable card on mass action by @Simounet in https://github.com/wallabag/wallabag/pull/6991
* Add tag form submit button always displayed by @Simounet in https://github.com/wallabag/wallabag/pull/6986

## [2.6.6](https://github.com/wallabag/wallabag/tree/2.6.6)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.5...2.6.6)

### Security fix
* Force secure cookie on HTTPS connection by @j0k3r in https://github.com/wallabag/wallabag/pull/6924

### Fixes
* Fix checkboxes pointer events issue by @Simounet in https://github.com/wallabag/wallabag/pull/6897
* Add Google mailer by @j0k3r in https://github.com/wallabag/wallabag/pull/6899
* Improve performance on homepage by @Simounet in https://github.com/wallabag/wallabag/pull/6909
* Mass action layout improved by @Simounet in https://github.com/wallabag/wallabag/pull/6912

## [2.6.5](https://github.com/wallabag/wallabag/tree/2.6.5)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.4...2.6.5)

### Fixes
* "Fix checkboxes pointer-events disabled" by @Simounet https://github.com/wallabag/wallabag/pull/6874
* "Fix nav input styles" by @Simounet https://github.com/wallabag/wallabag/pull/6877
* "Change domain status filters html types" by @Simounet https://github.com/wallabag/wallabag/pull/6888

## [2.6.4](https://github.com/wallabag/wallabag/tree/2.6.4)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.3...2.6.4)

### Fixes
* Fix API token generation by @nicosomb https://github.com/wallabag/wallabag/pull/6869
* Fix checkboxes which were broken by @nicosomb https://github.com/wallabag/wallabag/pull/6864

## [2.6.3](https://github.com/wallabag/wallabag/tree/2.6.3)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.2...2.6.3)

### Security fixes
* Replace GET way to POST way to reset data user by @nicosomb https://github.com/wallabag/wallabag/commit/78b0b55c40511e1f22d5bbb4897aa10fca68441c
* Replace GET way to POST way to delete API client by @nicosomb https://github.com/wallabag/wallabag/commit/ffcc5c9062fcc8cd922d7d6d65edbe5efae96806

### Improvement
* Add confirmation before reload entry by @nicosomb https://github.com/wallabag/wallabag/pull/6778
* Remove external dependencies on howto page by @viktoriussuwandi https://github.com/wallabag/wallabag/pull/6775

### Fixes
* Use Session instead of Referrer for Redirection by @Spoons https://github.com/wallabag/wallabag/pull/6119
* Reduce risk of collision for #content by @kdecherf https://github.com/wallabag/wallabag/pull/6829
* Update dependencies to fix vulnerabilities by @nicosomb https://github.com/wallabag/wallabag/pull/6783
* Fix failing randomly test by @nicosomb https://github.com/wallabag/wallabag/pull/6763

### Technical stuff
* Add check if git is installed by @nicosomb https://github.com/wallabag/wallabag/pull/6788
* Replace kernel.root_dir by kernel.project_dir by @yguedidi https://github.com/wallabag/wallabag/pull/6809
* Split symfony/symfony by @yguedidi https://github.com/wallabag/wallabag/pull/6817
* Make Crawler::extract get an array by @yguedidi https://github.com/wallabag/wallabag/pull/6812
* Use PSR-17 and PSR-18 by @yguedidi https://github.com/wallabag/wallabag/pull/6816
* Replace Client by KernelBrowser by @yguedidi https://github.com/wallabag/wallabag/pull/6813
* Replace Debug component by ErrorHandler component by @yguedidi https://github.com/wallabag/wallabag/pull/6810
* Make ImportController extends AbstractController by @yguedidi https://github.com/wallabag/wallabag/pull/6808
* Use Twig instead of templating by @yguedidi https://github.com/wallabag/wallabag/pull/6797
* Identify platforms by their class by @yguedidi https://github.com/wallabag/wallabag/pull/6799
* Move from transchoice to trans by @yguedidi https://github.com/wallabag/wallabag/pull/6800
* Replace GetResponseEvent by RequestEvent by @yguedidi https://github.com/wallabag/wallabag/pull/6811
* Ensure the kernel is shut down before calling createClient by @yguedidi https://github.com/wallabag/wallabag/pull/6803
* Ignore docker-compose.override.yml by @yguedidi https://github.com/wallabag/wallabag/pull/6814
* Add composer normalizer by @nicosomb https://github.com/wallabag/wallabag/pull/6762

## [2.6.2](https://github.com/wallabag/wallabag/tree/2.6.2)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.1...2.6.2)

### Fixes
* Fix mass action input on dark theme by @simounet https://github.com/wallabag/wallabag/pull/6673
* Fix undefined variable by @nicosomb https://github.com/wallabag/wallabag/pull/6672
* Fix table name in migration by @nicosomb https://github.com/wallabag/wallabag/pull/6653

### Technical stuff
* Add a new build to test when no database table prefix are defined by @j0k3r https://github.com/wallabag/wallabag/pull/6731
* Keep escaped table name while migrating by @Glandos https://github.com/wallabag/wallabag/pull/6710
* Remove twofactor_auth parameter by @nicosomb https://github.com/wallabag/wallabag/pull/6723
* ApiDoc: Add response description to UserRestController by @caspermeijn https://github.com/wallabag/wallabag/pull/6684
* ApiDoc: Add response description to WallabagRestController by @caspermeijn https://github.com/wallabag/wallabag/pull/6102
* Skip migration if the table was already renamed by @gramakri https://github.com/wallabag/wallabag/pull/6678

### Meta
* Document how to run tests and formatter for new contributors by @caspermeijn https://github.com/wallabag/wallabag/pull/6685
* Add link to wallabag ecosystem resources by @nicosomb https://github.com/wallabag/wallabag/pull/6700

## [2.6.1](https://github.com/wallabag/wallabag/tree/2.6.1)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.6.0...2.6.1)

### Fixes
* Do not autoload fixtures by @j0k3r https://github.com/wallabag/wallabag/pull/6648
* Add confirmation alert when deleting articles from list view by @nicosomb https://github.com/wallabag/wallabag/pull/6644

## [2.6.0](https://github.com/wallabag/wallabag/tree/2.6.0)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.5.4...2.6.0)

### Features
* Add tag deletion from tags list by @nicosomb https://github.com/wallabag/wallabag/pull/5861
* Add support of mass action to tag entries by @kdecherf https://github.com/wallabag/wallabag/pull/5838
* Mass action interface by @Simounet https://github.com/wallabag/wallabag/pull/6547
* Empty space on the top bar used for more add url toggle clickable target by @Simounet https://github.com/wallabag/wallabag/pull/6612
* Add new setting to show / hide articles thumbnails by @nicosomb https://github.com/wallabag/wallabag/pull/6609

### Fixes
* Add prefix for tag slugs by @kdecherf https://github.com/wallabag/wallabag/pull/6226
* Fix open all external links in new tab in Config by @wyntonfranklin https://github.com/wallabag/wallabag/pull/6256
* Fix dark theme for pre HTML tags by @Simounet https://github.com/wallabag/wallabag/pull/6495
* Fix dark mode top bar contrast by @Simounet https://github.com/wallabag/wallabag/pull/6510
* Dark mode contrast improved by @Simounet https://github.com/wallabag/wallabag/pull/6512
* Fix dark mode URL add input color by @Simounet https://github.com/wallabag/wallabag/pull/6525
* Fix round reading time in export by @mart-e https://github.com/wallabag/wallabag/pull/6545
* Fix images downloading with numeric HTML entity by @Simounet https://github.com/wallabag/wallabag/pull/6563
* Fix DownloadImages not following redirections by @Simounet https://github.com/wallabag/wallabag/pull/6562
* Fix auto dark theme detection flickering by @Simounet https://github.com/wallabag/wallabag/pull/6584
* Fix RSS feed_route not set by @Simounet https://github.com/wallabag/wallabag/pull/6606
* Add flash message when we try to add too much tags by @nicosomb https://github.com/wallabag/wallabag/pull/6607
* Changed default value for domain_name parameter by @nicosomb https://github.com/wallabag/wallabag/pull/6616
* Improved tags display by @Simounet https://github.com/wallabag/wallabag/pull/6613
* Fix mousetrap enter issue by @Simounet https://github.com/wallabag/wallabag/pull/6624
* Fix duplicate tags creation when assigning search results to tag by @nicosomb https://github.com/wallabag/wallabag/pull/6629

### Meta
* Removed Carrot & Scuttle share by @nicosomb https://github.com/wallabag/wallabag/pull/6047
* Remove old, not so maintained and buggy baggy theme by @nicosomb https://github.com/wallabag/wallabag/pull/4332
* Remove Scrutinizer badge by @j0k3r https://github.com/wallabag/wallabag/pull/6179
* Add mention to unofficial linux client by @imhemish https://github.com/wallabag/wallabag/pull/6203

### Technical stuff
* Remove SensioDistributionBundle by @yguedidi https://github.com/wallabag/wallabag/pull/5761
* Back to latest composer version by @yguedidi https://github.com/wallabag/wallabag/pull/5810
* Clean composer.lock after SensioDistributionBundle removal by @yguedidi https://github.com/wallabag/wallabag/pull/5839
* Remove transitive dependencies by @yguedidi https://github.com/wallabag/wallabag/pull/5784
* Register missed commands by @yguedidi https://github.com/wallabag/wallabag/pull/5928
* Extend right FOSRestBundle controller class by @yguedidi https://github.com/wallabag/wallabag/pull/5929
* Remove PHP-CS-Fixer deprecations by @yguedidi https://github.com/wallabag/wallabag/pull/5914
* Upgrade FOSUserBundle to 2.1 by @yguedidi https://github.com/wallabag/wallabag/pull/5782
* Add TwigCS by @yguedidi https://github.com/wallabag/wallabag/pull/5759
* Use FQCN as service name by @yguedidi https://github.com/wallabag/wallabag/pull/5748
* Migrate to new template reference notation by @yguedidi https://github.com/wallabag/wallabag/pull/5758
* Migrate from old colon notation to FQCN by @yguedidi https://github.com/wallabag/wallabag/pull/5943
* Use autowiring by @yguedidi https://github.com/wallabag/wallabag/pull/5946
* Use FQCN to fetch services by @yguedidi https://github.com/wallabag/wallabag/pull/5951
* Run tests without memory limit by @yguedidi https://github.com/wallabag/wallabag/pull/5953
* Import used classes by @yguedidi https://github.com/wallabag/wallabag/pull/5952
* Rework command tests by @yguedidi https://github.com/wallabag/wallabag/pull/5954
* Switch to Swagger for api documentation by @caspermeijn https://github.com/wallabag/wallabag/pull/6062
* Remove some deprecation by @j0k3r https://github.com/wallabag/wallabag/pull/6085
* Remove deprecated options from FOSRest by @j0k3r https://github.com/wallabag/wallabag/pull/6095
* Remove LiipThemeBundle by @j0k3r https://github.com/wallabag/wallabag/pull/6097
* Upgrade PHPStan and move to level 2 with baseline by @j0k3r https://github.com/wallabag/wallabag/pull/6098
* Upgrade to Symfony 4.4 by @j0k3r https://github.com/wallabag/wallabag/pull/6099
* Update to FOSUserBundle 3.1 by @j0k3r https://github.com/wallabag/wallabag/pull/6136
* Update to scheb/2fa-bundle by @j0k3r https://github.com/wallabag/wallabag/pull/6144
* Upgrade to Twig 3 by @j0k3r https://github.com/wallabag/wallabag/pull/6151
* Move translations files to /translations by @j0k3r https://github.com/wallabag/wallabag/pull/6153
* Fix EventDispatcher & events by @j0k3r https://github.com/wallabag/wallabag/pull/6154
* Replace SwiftMailer by Symfony Mailer by @j0k3r https://github.com/wallabag/wallabag/pull/6150
* Remove ContainerAwareCommand from commands by @j0k3r https://github.com/wallabag/wallabag/pull/6152
* Update all Doctrine deps by @j0k3r https://github.com/wallabag/wallabag/pull/6143
* Update PagerFanta by @j0k3r https://github.com/wallabag/wallabag/pull/6145
* Move to controller as a service by @j0k3r https://github.com/wallabag/wallabag/pull/6159
* Add RabbitMQConsumerTotalProxy to lazy RabbitMQ services for messages by @j0k3r https://github.com/wallabag/wallabag/pull/6166
* Properly handle json_array type removal by @j0k3r https://github.com/wallabag/wallabag/pull/6171
* Fix database_path in Docker env by @j0k3r https://github.com/wallabag/wallabag/pull/6174
* Docker: database_table_prefix may be configured from environment by @fcatt https://github.com/wallabag/wallabag/pull/6196
* Update annotations to OpenApi 3 by @caspermeijn https://github.com/wallabag/wallabag/pull/6182
* Fix public folder for Symfony 4+ by @kdecherf https://github.com/wallabag/wallabag/pull/6217
* Fix API allowed_registration by @caspermeijn https://github.com/wallabag/wallabag/pull/6315
* Enable PHP 8.2 in CI by @j0k3r https://github.com/wallabag/wallabag/pull/6469
* Fix/build stylelint error by @Simounet https://github.com/wallabag/wallabag/pull/6586

## [2.5.4](https://github.com/wallabag/wallabag/tree/2.5.4)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.5.3...2.5.4)

### Security fixes
* Fix adding tag to entries from other people by @j0k3r in https://github.com/wallabag/wallabag/pull/6290
* Fix XSS on username on share page by @j0k3r in https://github.com/wallabag/wallabag/pull/6288
* Fix CSRF on user deletion by @j0k3r in https://github.com/wallabag/wallabag/pull/6289

### Meta
* Fix release script by @j0k3r in https://github.com/wallabag/wallabag/pull/6275

## [2.5.3](https://github.com/wallabag/wallabag/tree/2.5.3)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.5.2...2.5.3)

### Security fixes
* Fix GHSA-qwx8-mxxx-mg96 https://github.com/wallabag/wallabag/commit/0f7460dbab9e29f4f7d2944aca20210f828b6abb by @Kdecherf, thanks to @bAuh0lz
* Fix GHSA-mrqx-mjc4-vfh3 https://github.com/wallabag/wallabag/commit/5ac6b6bff9e2e3a87fd88c2904ff3c6aac40722e by @Kdecherf, thanks to @bAuh0lz

### Meta
* Update deps before 2.5.3 by @j0k3r in https://github.com/wallabag/wallabag/pull/6241

## [2.5.2](https://github.com/wallabag/wallabag/tree/2.5.2)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.5.1...2.5.2)

### Fixes
* Add `nbEntries` to the API tags list response by @j0k3r in https://github.com/wallabag/wallabag/pull/5984
* Typofixes by @nexxai in https://github.com/wallabag/wallabag/pull/6004
* Add `domain_name` to entries api endpoint by @yotamN in https://github.com/wallabag/wallabag/pull/6019
* Add support to download SVG locally by @j0k3r in https://github.com/wallabag/wallabag/pull/5992

### Meta
* Automatically create the package after a release by @j0k3r in https://github.com/wallabag/wallabag/pull/5851
* Add .gitattributes, exclude tests from archive by @iamvar in https://github.com/wallabag/wallabag/pull/5910
* Allow auto merging of Dependabot JS minor or patch by @j0k3r in https://github.com/wallabag/wallabag/pull/6003
* Fix random failing tests by @j0k3r in https://github.com/wallabag/wallabag/pull/6013

## [2.5.1](https://github.com/wallabag/wallabag/tree/2.5.1)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.5.0...2.5.1)

### Fixes
- Jump to Node 16 to build assets by @j0k3r in https://github.com/wallabag/wallabag/pull/5822
- Update some deps by @j0k3r in https://github.com/wallabag/wallabag/pull/5834
- Fix error about template not found in 2fa form by @j0k3r in https://github.com/wallabag/wallabag/pull/5833
- Translations update from Hosted Weblate by @weblate in https://github.com/wallabag/wallabag/pull/5846
- material: fix "untagged entries" chip style by @Kdecherf in https://github.com/wallabag/wallabag/pull/5840

## [2.5.0](https://github.com/wallabag/wallabag/tree/2.5.0)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.4.3...2.5.0)

### Features

- Add Delicious import by @j0k3r in https://github.com/wallabag/wallabag/pull/5022
- Add route to list entries with annotations by @nicosomb in https://github.com/wallabag/wallabag/pull/4341
- Add an option to sort the feed entries by `updated_at` by @agallou in https://github.com/wallabag/wallabag/pull/5659
- Add support to download webp image locally by @j0k3r in https://github.com/wallabag/wallabag/pull/5687
- Update readme and docker development env by @Kdecherf in https://github.com/wallabag/wallabag/pull/5662
- Add action to assign a search as a tag to each result by @nicosomb in https://github.com/wallabag/wallabag/pull/5381
- Drop PHP prior to 7.4 by @yguedidi in https://github.com/wallabag/wallabag/pull/5791

### Deprecated features
- Deprecate Baggy theme by @j0k3r in https://github.com/wallabag/wallabag/pull/5742
- Deprecate mobi export by @Kdecherf in https://github.com/wallabag/wallabag/pull/5775

### API

- Add new endpoint for API: config by @nicosomb in https://github.com/wallabag/wallabag/pull/5673

### Technical stuff

- Fix test with `usinenouvelle.com` being flaky these times by @j0k3r in https://github.com/wallabag/wallabag/pull/5686
- Replace `iconv()` calls with Transliterator by @Kdecherf in https://github.com/wallabag/wallabag/pull/5680
- Composer 2.3 isn't compatible with wallabag by @j0k3r in https://github.com/wallabag/wallabag/pull/5708
- CI workaround for PHP < 7.4 & MySQL 8 by @j0k3r in https://github.com/wallabag/wallabag/pull/5729
- Set permissions for GitHub actions by @naveensrinivasan in https://github.com/wallabag/wallabag/pull/5732
- Included githubactions in the dependabot config by @naveensrinivasan in https://github.com/wallabag/wallabag/pull/5737
- Remove bundle inheritance by @yguedidi in https://github.com/wallabag/wallabag/pull/5746
- Fix SQLite database location by @yguedidi in https://github.com/wallabag/wallabag/pull/5771
- Disable XDebug in test make recipe by @yguedidi in https://github.com/wallabag/wallabag/pull/5770
- Exclude local phpunit.xml by @yguedidi in https://github.com/wallabag/wallabag/pull/5757
- Upgrade PHP dependencies by @yguedidi in https://github.com/wallabag/wallabag/pull/5778

### Translations

- Enabled greek translation by @nicosomb in https://github.com/wallabag/wallabag/pull/5656
- Translations update from Hosted Weblate by @weblate see https://github.com/wallabag/wallabag/pulls?q=is%3Apr+author%3Aweblate+milestone%3A2.5.0+is%3Amerged

### Fixes

- Fix tagging rule match when user has a custom reading speed by @j0k3r in https://github.com/wallabag/wallabag/pull/5646
- Feat/home entries updated by @Simounet in https://github.com/wallabag/wallabag/pull/5664
- Fix row and card style regressions by @Simounet in https://github.com/wallabag/wallabag/pull/5676
- Fix epub language by @j0k3r in https://github.com/wallabag/wallabag/pull/5647
- material: improve mass buttons style, mostly for smartphone screens by @Kdecherf in https://github.com/wallabag/wallabag/pull/5695
- Fix composer version in docker dev configuration by @nicosomb in https://github.com/wallabag/wallabag/pull/5715
- Use site with subdomain as tagging rule example by @jonas-hagen in https://github.com/wallabag/wallabag/pull/5744
- Fix PHP Docker image by @yguedidi in https://github.com/wallabag/wallabag/pull/5772

## [2.4.3](https://github.com/wallabag/wallabag/tree/2.4.3)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.4.2...2.4.3)

### Fixes
* Tag view: order tags by label @Kdecherf in https://github.com/wallabag/wallabag/pull/5296
* Pagination style updated by @Simounet in https://github.com/wallabag/wallabag/pull/5202
* Improve performance of REST exists call by @Kdecherf in https://github.com/wallabag/wallabag/pull/5261
* Make RSS feed entry links valid and accessible by @hennevogel in https://github.com/wallabag/wallabag/pull/5347
* Fixed search on homepage by @nicosomb in https://github.com/wallabag/wallabag/pull/5379
* Fixed timeout when assigning tags to entry by @nicosomb in https://github.com/wallabag/wallabag/pull/5156
* Fixed reading time in epub/pdf exports by @nicosomb in https://github.com/wallabag/wallabag/pull/5405
* Fix dark theme developper page by @Simounet in https://github.com/wallabag/wallabag/pull/5453
* Fix dark mode background color for pre tags by @Kdecherf in https://github.com/wallabag/wallabag/pull/5465
* Show when articles is read by @nicosomb in https://github.com/wallabag/wallabag/pull/5378
* Added random button on article view by @nicosomb in https://github.com/wallabag/wallabag/pull/5380
* Fixed wrong number of entries for tag feeds by @christianjunk in https://github.com/wallabag/wallabag/pull/5585
* Add support for RTL content by @Monirzadeh in https://github.com/wallabag/wallabag/pull/5599
* Avoid overlapping images when downloading them by @j0k3r in https://github.com/wallabag/wallabag/pull/5594

### Meta
* Fix/tests by @Simounet in https://github.com/wallabag/wallabag/pull/5217
* dependabot: change schedule to check updates once a month by @Kdecherf in https://github.com/wallabag/wallabag/pull/5260
* Update dependencies by @Kdecherf in https://github.com/wallabag/wallabag/pull/5326
* Create SECURITY.md by @zidingz in https://github.com/wallabag/wallabag/pull/5406
* Add build test on PHP 8.0 & 8.1 by @j0k3r in https://github.com/wallabag/wallabag/pull/5591
* Fix Dependabot alerts for `tar` & `json-schema` by @j0k3r in https://github.com/wallabag/wallabag/pull/5541
* Change the name of the static package's root directory by @oupala in https://github.com/wallabag/wallabag/pull/5542
* Fixed unavailable russian website in test by @nicosomb in https://github.com/wallabag/wallabag/pull/5376
* Update deps by @j0k3r in https://github.com/wallabag/wallabag/pull/5540

### Translations
* Translations update from Weblate by @weblate in https://github.com/wallabag/wallabag/pull/5187
* Translations update from Weblate by @weblate in https://github.com/wallabag/wallabag/pull/5391
* Add Czech language by @marapavelka in https://github.com/wallabag/wallabag/pull/5535
* Translations update from Hosted Weblate by @weblate in https://github.com/wallabag/wallabag/pull/5580
* Translations update from Hosted Weblate by @weblate in https://github.com/wallabag/wallabag/pull/5581

## [2.4.2](https://github.com/wallabag/wallabag/tree/2.4.2)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.4.1...2.4.2)

### Fixes
- Fix nav lang filter [#4908](https://github.com/wallabag/wallabag/pull/4908)
- Fix accessibility problem with the 2FA QR code [#4915](https://github.com/wallabag/wallabag/pull/4915)
- Preselect currently active section in the filter menu [#4972](https://github.com/wallabag/wallabag/pull/4972)
- Fix translation of date in the footer using IntlDateFormatter [#4971](https://github.com/wallabag/wallabag/pull/4971)
- Update dark theme [#4921](https://github.com/wallabag/wallabag/pull/4921)
- Dark theme updated [#4983](https://github.com/wallabag/wallabag/pull/4983)
- Fix account dropdown width [#4969](https://github.com/wallabag/wallabag/pull/4969)
- Fix auto prefered color scheme [#5071](https://github.com/wallabag/wallabag/pull/5071)
- Convert tag label to lowercase in RuleBasedTagger [#5111](https://github.com/wallabag/wallabag/pull/5111)
- Fix myreadspeed links [#5113](https://github.com/wallabag/wallabag/pull/5113)
- Internal server error while exporting to epub [#5052](https://github.com/wallabag/wallabag/issues/5052)
- Error parsing image URL (with scrset) [#4914](https://github.com/wallabag/wallabag/issues/4914)

## [2.4.1](https://github.com/wallabag/wallabag/tree/2.4.1)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.4.0...2.4.1)

### Fixes
- Fix path icons from manifest.json [#4811](https://github.com/wallabag/wallabag/pull/4811)
- Fix missing apple-touch-icon-512.png from manifest.json [#4823](https://github.com/wallabag/wallabag/pull/4823)
- migrations: fix mysql migration for an edge case [#4832](https://github.com/wallabag/wallabag/pull/4832)
- Add a command to clean downloaded images [#4844](https://github.com/wallabag/wallabag/pull/4844)
- Add a query to parse only non-hashed URL [#4865](https://github.com/wallabag/wallabag/pull/4865) [#4878](https://github.com/wallabag/wallabag/pull/4878)
- material: fix issue with cookie check for light/dark theme [#4866](https://github.com/wallabag/wallabag/pull/4866)
- Show OTP secret next to the QR Code [#4868](https://github.com/wallabag/wallabag/pull/4868)
- views: add title to tag li elements, for consistency [#4894](https://github.com/wallabag/wallabag/pull/4894)
- Add label and issuer to otp config [#4301](https://github.com/wallabag/wallabag/pull/4301)

### Meta
- Update release process and introduce 2.4.1-dev [#4808](https://github.com/wallabag/wallabag/pull/4808)
- Remove community.wallabag.org from the issue template [#4817](https://github.com/wallabag/wallabag/pull/4817)
- Update issue templates [#4843](https://github.com/wallabag/wallabag/pull/4843)
- Allow GitHub Actions to run on 2.* branches [#4896](https://github.com/wallabag/wallabag/pull/4896)
- Prepare 2.4.1 [#4904](https://github.com/wallabag/wallabag/pull/4904)

## [2.4.0](https://github.com/wallabag/wallabag/tree/2.4.0)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.3.8...2.4.0)

### Features

- Allow to rename tags from the web interface [#3574](https://github.com/wallabag/wallabag/pull/3574)
- Added possibility to change locale from login/register pages [#3216](https://github.com/wallabag/wallabag/pull/3216)
- Add ability to import/export tagging rules [#4028](https://github.com/wallabag/wallabag/pull/4028)
- Add quickstart into the menu [#3992](https://github.com/wallabag/wallabag/pull/3992)
- Enable OTP 2FA via third apps (Google Authenticator, Authy or FreeOTP) [#3798](https://github.com/wallabag/wallabag/pull/3798)
- Show untagged entries count on tag list [#3993](https://github.com/wallabag/wallabag/pull/3993)
- Add random feature [#3526](https://github.com/wallabag/wallabag/pull/3526)
- Added ability to revoke feed token [#3994](https://github.com/wallabag/wallabag/pull/3994)
- Display login in config / user [#3995](https://github.com/wallabag/wallabag/pull/3995)
- Move Ignore Origin rules to database [#4026](https://github.com/wallabag/wallabag/pull/4026)
- Add ability to manually define the reading speed [#4053](https://github.com/wallabag/wallabag/pull/4053)
- Added MathJax support [#4128](https://github.com/wallabag/wallabag/pull/4128)
- Improved tags list [#4136](https://github.com/wallabag/wallabag/pull/4136)
- Add untagged list link to filter menu [#4150](https://github.com/wallabag/wallabag/pull/4150)
- Allow custom styles system wide [#4151](https://github.com/wallabag/wallabag/pull/4151)
- Added elCurator import [#4175](https://github.com/wallabag/wallabag/pull/4175)
- Added language support for Simplified Chinese（简体中文）[#4281](https://github.com/wallabag/wallabag/pull/4281)
- Added publication date on epub export [#4315](https://github.com/wallabag/wallabag/pull/4315)
- Enhanced article display [#4317](https://github.com/wallabag/wallabag/pull/4317)
- Added mass actions for Material design in list view [#4326](https://github.com/wallabag/wallabag/pull/4326)
- Added a button to disable 2FA when enabled [#4329](https://github.com/wallabag/wallabag/pull/4329)
- Enhanced navigation menu in material theme [#4343](https://github.com/wallabag/wallabag/pull/4343)
- Copy client info to clipboard [#4058](https://github.com/wallabag/wallabag/pull/4058)
- Upgrade Piwik Twig Extension to Matomo [#4363](https://github.com/wallabag/wallabag/pull/4363)
- Dark theme to Material [#4725](https://github.com/wallabag/wallabag/pull/4725)

### API

- Storage of hash of URL to check if pages already saved [#3158](https://github.com/wallabag/wallabag/pull/3158)
- Added a basic search endpoint [#3627](https://github.com/wallabag/wallabag/pull/3627)
- Entries: added add parameter detail to exclude or include content in response [#3960](https://github.com/wallabag/wallabag/pull/3960)
- Add a new endpoint to retrieve information from the wallabag instance [#3845](https://github.com/wallabag/wallabag/pull/3845)
- API return an error with empty quote [#4161](https://github.com/wallabag/wallabag/pull/4161)
- Fixed bug when we delete entry via API [#4316](https://github.com/wallabag/wallabag/pull/4316)
- Add ability to define lifetime token for the API [#4319](https://github.com/wallabag/wallabag/pull/4319)
- Added archived_at property and updateArchived method [#3630](https://github.com/wallabag/wallabag/pull/3630)
- Add `given_url` in Entry table to check if a redirected url has already added [#3271](https://github.com/wallabag/wallabag/pull/3271)

### Technical stuff

- Added support for secured emails via SMTP [#3596](https://github.com/wallabag/wallabag/pull/3596)
- Changed RSS to Atom feed and improve paging [#3223](https://github.com/wallabag/wallabag/pull/3223)
- MySQL: change collation of tag label [#3959](https://github.com/wallabag/wallabag/pull/3959)
- Use httplug and graby 2.0 [#3390](https://github.com/wallabag/wallabag/pull/3390)
- Use Imagick to keep GIF animation [#3955](https://github.com/wallabag/wallabag/pull/3955)
- Jump to Symfony 3.4 [#3731](https://github.com/wallabag/wallabag/pull/3731)
- Removed hardcoded database version [#3864](https://github.com/wallabag/wallabag/pull/3864)
- Dropping PHP < 7.1 [#3758](https://github.com/wallabag/wallabag/pull/3758)
- Dropping PHP < 7.2 [#4441](https://github.com/wallabag/wallabag/pull/4441)
- Force default_protocol to generate an url input [#3879](https://github.com/wallabag/wallabag/pull/3879)
- Cascade delete on oauth2 table when deleting a user [#3956](https://github.com/wallabag/wallabag/pull/3956)
- Preview picture: use the 1st pic retrieved if no og:image set [#3965](https://github.com/wallabag/wallabag/pull/3965)
- Force PHP version in Dockerfile [#3977](https://github.com/wallabag/wallabag/pull/3977)
- Add Sentry support [#3990](https://github.com/wallabag/wallabag/pull/3990)
- Fix slow/unindexed queries [#4081](https://github.com/wallabag/wallabag/pull/4081)
- Rename internal settings table [#4084](https://github.com/wallabag/wallabag/pull/4084)
- Add environment variables to control scripts/dev.sh behavior [#4152](https://github.com/wallabag/wallabag/pull/4152)
- Increase php memory limit PHP Docker [#4163](https://github.com/wallabag/wallabag/pull/4163)
- Ensure ENV is well defined when using make [#4249](https://github.com/wallabag/wallabag/pull/4249)
- Update translations from Weblate [#4442](https://github.com/wallabag/wallabag/pull/4442)
- Ditch Travis to use GitHub Actions [#4747](https://github.com/wallabag/wallabag/pull/4747)
- Upgrade Webpack from v4 to v5 [#4727](https://github.com/wallabag/wallabag/pull/4727)

### Translations

- Add Japanese translation [#4274](https://github.com/wallabag/wallabag/pull/4274)
- Add Croatian translation [#4518](https://github.com/wallabag/wallabag/pull/4518), [#4517](https://github.com/wallabag/wallabag/pull/4517)
- Add Ukrainian translation [#4489](https://github.com/wallabag/wallabag/pull/4489)
- Add language support for Simplified Chinese（简体中文）[#4281](https://github.com/wallabag/wallabag/pull/4281)
- Add Dutch translation to the settings [#4632](https://github.com/wallabag/wallabag/pull/4632)
- Support for Weblate [#4442](https://github.com/wallabag/wallabag/pull/4442) & [#4447](https://github.com/wallabag/wallabag/pull/4447)

### Fixes

- Fixed Instapaper import date order [#3980](https://github.com/wallabag/wallabag/pull/3980)
- Add missing entries in craue_config_setting. [#3868](https://github.com/wallabag/wallabag/pull/3868)
- Fix `reading_time` being NULL on SQLite [#4020](https://github.com/wallabag/wallabag/pull/4020)
- Fix createdAt filter on material [#4299](https://github.com/wallabag/wallabag/pull/4299)
- Fix duplicated icons on "Search engine" and "new link" forms [#4149](https://github.com/wallabag/wallabag/pull/4149)
- Fix entries export from search view [#4278](https://github.com/wallabag/wallabag/pull/4278)
- Fixed URL to share entry via twitter [#4250](https://github.com/wallabag/wallabag/pull/4250)
- Sidebar sub entries not conditioned by hover anymore [#4254](https://github.com/wallabag/wallabag/pull/4254)
- Fix print of long pages [#4263](https://github.com/wallabag/wallabag/pull/4263)
- Fixed duplicated tags when renaming them [#4310](https://github.com/wallabag/wallabag/pull/4310)
- Added authentication during import command for paywalled websites [#4325](https://github.com/wallabag/wallabag/pull/4325)
- Added more coherence with color links for material theme [#4346](https://github.com/wallabag/wallabag/pull/4346)
- Changed visibility for article stats [#4347](https://github.com/wallabag/wallabag/pull/4347)
- PHP 7.4 support [#4309](https://github.com/wallabag/wallabag/pull/4309)

## [2.3.8](https://github.com/wallabag/wallabag/tree/2.3.8)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.3.7...2.3.8)

### Fixes

- Jump to 2.3.8-dev [#3897](https://github.com/wallabag/wallabag/pull/3897)
- material: fix left padding on non-entry pages [#3901](https://github.com/wallabag/wallabag/pull/3901)
- Make dev/install/update script posix compatible [#3860](https://github.com/wallabag/wallabag/pull/3860)
- epub: fix exception when articles have the same title [#3908](https://github.com/wallabag/wallabag/pull/3908)
- Fix PHP warning [#3909](https://github.com/wallabag/wallabag/pull/3909)
- Add ability to match many domains for credentials [#3937](https://github.com/wallabag/wallabag/pull/3937)
- material: add metadata to list view [#3942](https://github.com/wallabag/wallabag/pull/3942)
- Enable no-referrer on img tags, enable strict-origin-when-cross-origin by default [#3943](https://github.com/wallabag/wallabag/pull/3943)
- Remove preview picture from share view page#3922
- Fix Intl Locale issue [#3964](https://github.com/wallabag/wallabag/pull/3964)

## [2.3.7](https://github.com/wallabag/wallabag/tree/2.3.7)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.3.6...2.3.7)

### Fixes

- Jump to 2.3.7-dev [#3837](https://github.com/wallabag/wallabag/pull/3837)
- Fix bad order parameter in the API [#3841](https://github.com/wallabag/wallabag/pull/3841)
- Update composer.json to add php-tidy (ext-tidy) [#3853](https://github.com/wallabag/wallabag/pull/3853)
- Add dedicated email for site config issue [#3861](https://github.com/wallabag/wallabag/pull/3861)
- Fix read & starred status in Pocket import [#3819](https://github.com/wallabag/wallabag/pull/3819)
- Fix broken 2 factor auth logo image [#3869](https://github.com/wallabag/wallabag/pull/3869)
- Fix CORS for API [#3882](https://github.com/wallabag/wallabag/pull/3882)
- Add support of expect parameter to change return object when deleting entry [#3887](https://github.com/wallabag/wallabag/pull/3887)
- epub export: fix missing cover image, only for exports of one article [#3886](https://github.com/wallabag/wallabag/pull/3886)
- Allow optional --ignore-root-warning [#3885](https://github.com/wallabag/wallabag/pull/3885)
- material: fix left padding of content on medium screens [#3893](https://github.com/wallabag/wallabag/pull/3893)
- material: hide creation date from card actions on specific sizes [#3894](https://github.com/wallabag/wallabag/pull/3894)

## [2.3.6](https://github.com/wallabag/wallabag/tree/2.3.6)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.3.5...2.3.6)

### Fixes

- Jump to 2.3.6-dev and update release process [#3814](https://github.com/wallabag/wallabag/pull/3814)
- Fix tag API leak [#3823](https://github.com/wallabag/wallabag/pull/3823)
- Validate imported entry to avoid error on import [#3816](https://github.com/wallabag/wallabag/pull/3816)
- Fix incorrect reading time calculation for entries with CJK characters [#3820](https://github.com/wallabag/wallabag/pull/3820)
- EntriesExport/epub: replace epub identifier with unique urn [#3827](https://github.com/wallabag/wallabag/pull/3827)
- Fix settings field inverted [#3833](https://github.com/wallabag/wallabag/pull/3833)
- Cast client id to avoid PG error [#3831](https://github.com/wallabag/wallabag/pull/3831)
- Rework of EPUB/PDF exports [#3826](https://github.com/wallabag/wallabag/pull/3826)

## [2.3.5](https://github.com/wallabag/wallabag/tree/2.3.5)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.3.4...2.3.5)

### Fixes

- Jump to 2.3.5-dev and update release process [#3778](https://github.com/wallabag/wallabag/pull/3778)
- Remove preview picture from single entry view page [#3765](https://github.com/wallabag/wallabag/pull/3765)
- Fix Android app login issue [#3784](https://github.com/wallabag/wallabag/pull/3784)
- material: fix missing thumbnail on list view [#3782](https://github.com/wallabag/wallabag/pull/3782)
- material: decrease size of tags on list view [#3783](https://github.com/wallabag/wallabag/pull/3783)
- build: upgrade yarn dependencies, update prod assets [#3781](https://github.com/wallabag/wallabag/pull/3781)
- No more dev for guzzle-site-authenticator [#3810](https://github.com/wallabag/wallabag/pull/3810)

## [2.3.4](https://github.com/wallabag/wallabag/tree/2.3.4)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.3.3...2.3.4)

### Fixes

- Fix image downloading on null image path [#3684](https://github.com/wallabag/wallabag/pull/3684)
- Remove remaining deprecation notices [#3686](https://github.com/wallabag/wallabag/pull/3686)
- Fix mobile viewport on big iframe and video elements [#3683](https://github.com/wallabag/wallabag/pull/3683)
- Autofocus the username field on the login page [#3691](https://github.com/wallabag/wallabag/pull/3691)
- Feature/svg logo [#3692](https://github.com/wallabag/wallabag/pull/3692)
- Fixes a typo [#3702](https://github.com/wallabag/wallabag/pull/3702)
- Update release script [#3705](https://github.com/wallabag/wallabag/pull/3705)
- Removing failing test from Travis [#3707](https://github.com/wallabag/wallabag/pull/3707)
- Replace SO url by lemonde.fr to avoid random failing test [#3685](https://github.com/wallabag/wallabag/pull/3685)
- php-cs-fixer: native_function_invocation [#3716](https://github.com/wallabag/wallabag/pull/3716)
- PHP 7.2 shouldn't fail [#3717](https://github.com/wallabag/wallabag/pull/3717)
- Liberation goes https [#3726](https://github.com/wallabag/wallabag/pull/3726)
- Bugfix: Sanitize the title of a saved webpage from invalid UTF-8 characters. [#3725](https://github.com/wallabag/wallabag/pull/3725)
- Fix dockerfile php72 [#3734](https://github.com/wallabag/wallabag/pull/3734)
- Fix sort parameters [#3719](https://github.com/wallabag/wallabag/pull/3719)
- Add note on GitHub PR template to auto-close issues [#3763](https://github.com/wallabag/wallabag/pull/3763)
- Fix link to wallabag requirements in documentation [#3766](https://github.com/wallabag/wallabag/pull/3766)
- Update translation when marking as read [#3772](https://github.com/wallabag/wallabag/pull/3772)
- Makefile fixes for non GNU systems [#3706](https://github.com/wallabag/wallabag/pull/3706)
- Card no preview replaced by wallabag logo [#3774](https://github.com/wallabag/wallabag/pull/3774)

### Changes

- Propose YunoHost badge for installing [#3678](https://github.com/wallabag/wallabag/pull/3678)
- More robust srcset image attribute handling [#3690](https://github.com/wallabag/wallabag/pull/3690)
- Rename getBuilderByUser and refactor query for untagged entries [#3712](https://github.com/wallabag/wallabag/pull/3712)
- Show tags on non-image gallery preview card [#3743](https://github.com/wallabag/wallabag/pull/3743)
- add manifest.json for android pwa [#3606](https://github.com/wallabag/wallabag/pull/3606)
- Add placeholder image to card-based gallery entries page [#3745](https://github.com/wallabag/wallabag/pull/3745)
- Abort running install and update script if root [#3733](https://github.com/wallabag/wallabag/pull/3733)
- Swap entry url with origin url if graby provides an updated one [#3553](https://github.com/wallabag/wallabag/pull/3553)

## [2.3.3](https://github.com/wallabag/wallabag/tree/2.3.3)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.3.2...2.3.3)

### Fixes

- Fix error when withRemove variable is not defined. [#3573](https://github.com/wallabag/wallabag/pull/3573)
- Fix title card HTML parsing [#3592](https://github.com/wallabag/wallabag/pull/3592)
- Fix tests [#3597](https://github.com/wallabag/wallabag/pull/3597)
- Fix tests [#3619](https://github.com/wallabag/wallabag/pull/3619)
- Better encoding of the URI for the bookmarklet [#3616](https://github.com/wallabag/wallabag/pull/3616)
- Fix overflow wrap issue [#3652](https://github.com/wallabag/wallabag/pull/3652)
- Fix/firefox mobile unneeded resize [#3653](https://github.com/wallabag/wallabag/pull/3653)
- Fix srcset attribute on images downloaded [#3661](https://github.com/wallabag/wallabag/pull/3661)
- Fix authors and preview alt encoding display [#3664](https://github.com/wallabag/wallabag/pull/3664)
- Spelling: GitHub, Log out, of the dev [#3614](https://github.com/wallabag/wallabag/pull/3614)
- Fix tests [#3668](https://github.com/wallabag/wallabag/pull/3668)
- Fixed migrations with dash into db names [#3538](https://github.com/wallabag/wallabag/pull/3538)

### Changes

- Allow login by email [#3615](https://github.com/wallabag/wallabag/pull/3615)
- Occitan update [#3646](https://github.com/wallabag/wallabag/pull/3646)
- Highlight code in articles using highlight.js [#3636](https://github.com/wallabag/wallabag/pull/3636)

## [2.3.2](https://github.com/wallabag/wallabag/tree/2.3.2)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.3.1...2.3.2)

### Fixes

- Add `set -eu` to update.sh [#3546](https://github.com/wallabag/wallabag/pull/3546)
- Fix broken link to remove tags from entries [#3536](https://github.com/wallabag/wallabag/pull/3536)

### Changes

- Nav actions updated [#3541](https://github.com/wallabag/wallabag/pull/3541)
- Replaced Create new client link with a button [#3539](https://github.com/wallabag/wallabag/pull/3539)

## [2.3.1](https://github.com/wallabag/wallabag/tree/2.3.1)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.3.0...2.3.1)

### Fixes

- Changed the way to check for initial migration [#3487](https://github.com/wallabag/wallabag/pull/3487)
- Displayed the RSS icon on homepage route [#3490](https://github.com/wallabag/wallabag/pull/3490)
- Hided the share toggle button if no service is enabled [#3492](https://github.com/wallabag/wallabag/pull/3492)
- Updated robots.txt to prevent crawling [#3510](https://github.com/wallabag/wallabag/pull/3510)
- Fixed lower case tags migration [#3507](https://github.com/wallabag/wallabag/pull/3507)
- Fixed initial migration when using custom table prefix [#3504](https://github.com/wallabag/wallabag/pull/3504)
- Fixed assets for subfolder install [#3524](https://github.com/wallabag/wallabag/pull/3524)
- Fixed empty card title link [#3525](https://github.com/wallabag/wallabag/pull/3525)
- Fixed empty title and domain_name when exception is thrown during fetch [#3442](https://github.com/wallabag/wallabag/pull/3442)
- API: copied entry object before sending, to keep id [#3516](https://github.com/wallabag/wallabag/pull/3516)

### Changes

- Added custom driver & schema manager for PostgreSQL 10 [#3488](https://github.com/wallabag/wallabag/pull/3488)
- Replaced exit_to_app, redo and autorenew icons [#3513](https://github.com/wallabag/wallabag/pull/3513)
- Added PHP 7.2 compatibility [#3515](https://github.com/wallabag/wallabag/pull/3515)

## [2.3.0](https://github.com/wallabag/wallabag/tree/2.3.0) (2017-12-11)
   [Full Changelog](https://github.com/wallabag/wallabag/compare/2.2.3...2.3.0)

### API

- API `exists` returns `id` if article exists [#2919](https://github.com/wallabag/wallabag/pull/2919)
- Added API endpoint to handle a list of URL and to add/delete tags [#3055](https://github.com/wallabag/wallabag/pull/3055)
- Added API endpoint to handle a list of URL [#3053](https://github.com/wallabag/wallabag/pull/3053)
- Retrieve tag / tags value from query or request [#3103](https://github.com/wallabag/wallabag/pull/3103)
- Register through API [#3065](https://github.com/wallabag/wallabag/pull/3065)
- API user creation behind a toggle [#3177](https://github.com/wallabag/wallabag/pull/3177)
- Allow other fields to be sent using API [#3106](https://github.com/wallabag/wallabag/pull/3106)
- Add ability to patch an entry with more fields [#3181](https://github.com/wallabag/wallabag/pull/3181)
- Create (and return) a client after creating a new user using the API [#3187](https://github.com/wallabag/wallabag/pull/3187)
- Fix PATCH method [#3256](https://github.com/wallabag/wallabag/pull/3256)

### Technical stuff

- Dropping PHP 5.5 [#2861](https://github.com/wallabag/wallabag/pull/2861), migrated to Symfony 3.3 [#3376](https://github.com/wallabag/wallabag/pull/3376), defined MySQL as the default rdbms for wallabag [#3171](https://github.com/wallabag/wallabag/pull/3171)
- Add Cloudron as installation method [#3000](https://github.com/wallabag/wallabag/pull/3000)
- Added migrations execution after fresh install [#3088](https://github.com/wallabag/wallabag/pull/3088)
- Upgraded CraueConfigBundle to 2.0 [#3113](https://github.com/wallabag/wallabag/pull/3113)
- Removed embedded documentation. [The repository is now here](https://github.com/wallabag/doc). [#3122](https://github.com/wallabag/wallabag/pull/3122)
- Fix some Scrutinizer issues [#3161](https://github.com/wallabag/wallabag/pull/3161) [#3172](https://github.com/wallabag/wallabag/pull/3172)
- Isolated tests [#3137](https://github.com/wallabag/wallabag/pull/3137)
- Log an error level message when user auth fail [#3195](https://github.com/wallabag/wallabag/pull/3195)
- Add a real configuration for CS-Fixer [#3258](https://github.com/wallabag/wallabag/pull/3258)
- Replace ant with Makefile [#3398](https://github.com/wallabag/wallabag/pull/3398)

### Features

- Share articles to Scuttle (https://github.com/scronide/scuttle) instance [#2999](https://github.com/wallabag/wallabag/pull/2999)
- Allow to remove all archived entries [#3020](https://github.com/wallabag/wallabag/pull/3020)
- Added publication date and author [#3024](https://github.com/wallabag/wallabag/pull/3024)
- Added `notmatches` operator for automatic tagging rule [#3047](https://github.com/wallabag/wallabag/pull/3047)
- Search & paginate users [#3060](https://github.com/wallabag/wallabag/pull/3060)
- **Clean duplicates entries** command [#2920](https://github.com/wallabag/wallabag/pull/2920)
- Added headers field in Entry [#3108](https://github.com/wallabag/wallabag/pull/3108)
- Add some deletion confirmation to avoid mistake [#3147](https://github.com/wallabag/wallabag/pull/3147)
- Add support for tag in Instapaper import [#3168](https://github.com/wallabag/wallabag/pull/3168)
- Added tags on list view [#3077](https://github.com/wallabag/wallabag/pull/3077)
- **Show user** command [#3179](https://github.com/wallabag/wallabag/pull/3179)
- Add ability to filter public entries & use it in the API [#3208](https://github.com/wallabag/wallabag/pull/3208)
- Store credentials for restricted site in database [#2683](https://github.com/wallabag/wallabag/pull/2683)
- Add RSS for tags & All entries [#3207](https://github.com/wallabag/wallabag/pull/3207)
- Add **list users** command [#3301](https://github.com/wallabag/wallabag/pull/3301)
- Add **reload entry** command [#3326](https://github.com/wallabag/wallabag/pull/3326)
- Add starred_at field which is set when an entry is starred [#3330](https://github.com/wallabag/wallabag/pull/3330)
- Add originUrl property to Entry [#3346](https://github.com/wallabag/wallabag/pull/3346)

### Changes

- Changed default value for list mode (grid instead of list) [#3014](https://github.com/wallabag/wallabag/pull/3014)
- Remove `isPublic` from Entry entity [#3030](https://github.com/wallabag/wallabag/pull/3030)
- Use username to import [#3080](https://github.com/wallabag/wallabag/pull/3080)
- Adds Webpack support and remove Grunt [#3022](https://github.com/wallabag/wallabag/pull/3022)
- Improved Guzzle subscribers extensibility [#2751](https://github.com/wallabag/wallabag/pull/2751)
- Added logger when we match Tagging rules [#3110](https://github.com/wallabag/wallabag/pull/3110)
- unify Download/Export wording. [#3130](https://github.com/wallabag/wallabag/pull/3130)
- Staying on an article view after removing a tag [#3138](https://github.com/wallabag/wallabag/pull/3138)
- Use an alternative way to detect images [#3184](https://github.com/wallabag/wallabag/pull/3184)
- Displays an error with an annotation with a too long quote [#3093](https://github.com/wallabag/wallabag/pull/3093)
- Validate language & preview picture fields [#3192](https://github.com/wallabag/wallabag/pull/3192)
- remove craueconfig domain name setting and add a proper one in parameters [#3173](https://github.com/wallabag/wallabag/pull/3173)
- Better public sharing page [#3204](https://github.com/wallabag/wallabag/pull/3204), [#3449](https://github.com/wallabag/wallabag/pull/3449)
- Improved pagination, navigation, tag's list and footer UI [#3459](https://github.com/wallabag/wallabag/pull/3459), [#3467](https://github.com/wallabag/wallabag/pull/3467), [#3461](https://github.com/wallabag/wallabag/pull/3461), [#3463](https://github.com/wallabag/wallabag/pull/3463)

### Fixes

- Use up-to-date Firefox extension and add F-Droid link for Android app [#3057](https://github.com/wallabag/wallabag/pull/3057)
- Fixed sandwich menu position in entry view (material theme) [#3073](https://github.com/wallabag/wallabag/pull/3073)
- Disabled shortcuts on login/register page [#3075](https://github.com/wallabag/wallabag/pull/3075)
- "+" in url not parsed correctly (when we click on original URL) [#3002](https://github.com/wallabag/wallabag/pull/3002)
- Skip auth when no credentials are found [#3101](https://github.com/wallabag/wallabag/pull/3101)
- Added migration to change length for user fields [#3104](https://github.com/wallabag/wallabag/pull/3104)
- Fix delete annotation when username is defined [#3120](https://github.com/wallabag/wallabag/pull/3120)
- Fixed is_starred for wallabag v2 import [#3143](https://github.com/wallabag/wallabag/pull/3143)
- Replace images with & in url [#3176](https://github.com/wallabag/wallabag/pull/3176)
- Ignore tag's case [#3139](https://github.com/wallabag/wallabag/pull/3139)
- Multiple tag search, which was broken from API [#3309](https://github.com/wallabag/wallabag/pull/3309)
- In RSS feeds, pubDate now conformant to DateTime RFC822 specifications [#3471](https://github.com/wallabag/wallabag/pull/3471)

### Translations

- Add Russian language [#3378](https://github.com/wallabag/wallabag/pull/3378)

## [2.2.3](https://github.com/wallabag/wallabag/tree/2.2.3) (2017-05-17)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.2.2...2.2.3)

- Lock guzzle-site-authenticator to avoid errors [\#3124](https://github.com/wallabag/wallabag/pull/3124) ([j0k3r](https://github.com/j0k3r))
- reorder contrib strings in about page [\#3123](https://github.com/wallabag/wallabag/pull/3123) ([X-dark](https://github.com/X-dark))
- Fixed documentation URL [\#3117](https://github.com/wallabag/wallabag/pull/3117) ([nicosomb](https://github.com/nicosomb))
- Update graby\* licenses [\#3097](https://github.com/wallabag/wallabag/pull/3097) ([j0k3r](https://github.com/j0k3r))
- Fix API pagination is broken if perPage is custom value [\#3096](https://github.com/wallabag/wallabag/pull/3096) ([aaa2000](https://github.com/aaa2000))
- Create a new entry via API even when its content can't be retrieved [\#3095](https://github.com/wallabag/wallabag/pull/3095) ([aaa2000](https://github.com/aaa2000))
- Translate error message in login page [\#3090](https://github.com/wallabag/wallabag/pull/3090) ([aaa2000](https://github.com/aaa2000))
- Fix display the form errors correctly [\#3082](https://github.com/wallabag/wallabag/pull/3082) ([aaa2000](https://github.com/aaa2000))
- Disable negative numbers in filters [\#3076](https://github.com/wallabag/wallabag/pull/3076) ([bourvill](https://github.com/bourvill))
- Small typo in documentation fix \#3061 [\#3072](https://github.com/wallabag/wallabag/pull/3072) ([bourvill](https://github.com/bourvill))
- Ignore tests exported files [\#3066](https://github.com/wallabag/wallabag/pull/3066) ([tcitworld](https://github.com/tcitworld))
- Correct create\_application en string [\#3064](https://github.com/wallabag/wallabag/pull/3064) ([gileri](https://github.com/gileri))
- Make symfony-assets-install use `relative` symlinks [\#3052](https://github.com/wallabag/wallabag/pull/3052) ([shtrom](https://github.com/shtrom))
- Add export notice at the end of the epub [\#3023](https://github.com/wallabag/wallabag/pull/3023) ([mart-e](https://github.com/mart-e))
- Save alpha channel when downloading PNG images [\#3017](https://github.com/wallabag/wallabag/pull/3017) ([Kdecherf](https://github.com/Kdecherf))
- Update paywall.rst \(more details and clear cache\) [\#2985](https://github.com/wallabag/wallabag/pull/2985) ([etiess](https://github.com/etiess))
- Update paywall.rst \(EN\) with details + clear cache [\#2971](https://github.com/wallabag/wallabag/pull/2971) ([j0k3r](https://github.com/j0k3r))
- remove language on html tag [\#2968](https://github.com/wallabag/wallabag/pull/2968) ([chrido](https://github.com/chrido))

## [2.2.2](https://github.com/wallabag/wallabag/tree/2.2.2) (2017-03-02)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.2.1...2.2.2)

- Update Polish translation [\#2932](https://github.com/wallabag/wallabag/pull/2932) ([mruminski](https://github.com/mruminski))
- Update Spanish translation [\#2917](https://github.com/wallabag/wallabag/pull/2917) ([ngosang](https://github.com/ngosang))
- Remove word repetition from german translation [\#2911](https://github.com/wallabag/wallabag/pull/2911) ([jlnostr](https://github.com/jlnostr))
- Italian documentation added [\#2878](https://github.com/wallabag/wallabag/pull/2878) ([matteocoder](https://github.com/matteocoder))
- Add informations about Apache 2.4 [\#2874](https://github.com/wallabag/wallabag/pull/2874) ([kgaut](https://github.com/kgaut))
- Fixed symlinks issue during release creation [\#2950](https://github.com/wallabag/wallabag/pull/2950) ([nicosomb](https://github.com/nicosomb))
- Use wallabag/tcpdf [\#2931](https://github.com/wallabag/wallabag/pull/2931) ([j0k3r](https://github.com/j0k3r))
- Add activation of 'rewrite' mod of Apache [\#2926](https://github.com/wallabag/wallabag/pull/2926) ([qtheuret](https://github.com/qtheuret))
- Updated CHANGELOG with latest changes [\#2916](https://github.com/wallabag/wallabag/pull/2916) ([nicosomb](https://github.com/nicosomb))
- Import: we now skip messages when user is null [\#2915](https://github.com/wallabag/wallabag/pull/2915) ([nicosomb](https://github.com/nicosomb))
- Added wallabag.it link in README [\#2913](https://github.com/wallabag/wallabag/pull/2913) ([nicosomb](https://github.com/nicosomb))
- Moved :it: documentation into it folder [\#2908](https://github.com/wallabag/wallabag/pull/2908) ([nicosomb](https://github.com/nicosomb))
- Alert that 2FA must be authorized in app/config/parameters.yml [\#2905](https://github.com/wallabag/wallabag/pull/2905) ([nicofrand](https://github.com/nicofrand))
- Update Spanish translation [\#2892](https://github.com/wallabag/wallabag/pull/2892) ([ngosang](https://github.com/ngosang))
- Doc: translated mobile apps configuration in french [\#2882](https://github.com/wallabag/wallabag/pull/2882) ([nicosomb](https://github.com/nicosomb))
- Fixed typo in "first\_steps" [\#2879](https://github.com/wallabag/wallabag/pull/2879) ([matteocoder](https://github.com/matteocoder))
- Doc - information about Apache 2.4 [\#2875](https://github.com/wallabag/wallabag/pull/2875) ([kgaut](https://github.com/kgaut))
- Log restricted access value [\#2869](https://github.com/wallabag/wallabag/pull/2869) ([j0k3r](https://github.com/j0k3r))
- docs 3rd party tools: update java wrapper, add cmd tool to add article [\#2860](https://github.com/wallabag/wallabag/pull/2860) ([Strubbl](https://github.com/Strubbl))
- fix misspells in polish translation [\#2846](https://github.com/wallabag/wallabag/pull/2846) ([mruminski](https://github.com/mruminski))
- Update RulerZ [\#2842](https://github.com/wallabag/wallabag/pull/2842) ([K-Phoen](https://github.com/K-Phoen))
- Show active list in the left menu during search [\#2841](https://github.com/wallabag/wallabag/pull/2841) ([Kdecherf](https://github.com/Kdecherf))
- Restored correct version for framework-extra-bundle [\#2840](https://github.com/wallabag/wallabag/pull/2840) ([nicosomb](https://github.com/nicosomb))
- scripts/update.sh: 18: scripts/update.sh: composer.phar: not found [\#2839](https://github.com/wallabag/wallabag/pull/2839) ([foxmask](https://github.com/foxmask))
- Update Oc version [\#2838](https://github.com/wallabag/wallabag/pull/2838) ([Quent-in](https://github.com/Quent-in))
- Search by term: extend to entries url [\#2832](https://github.com/wallabag/wallabag/pull/2832) ([Kdecherf](https://github.com/Kdecherf))
- Update of CraueConfigBundle in Occitan [\#2831](https://github.com/wallabag/wallabag/pull/2831) ([Quent-in](https://github.com/Quent-in))
- Fix rendering of entry title in Twig views [\#2830](https://github.com/wallabag/wallabag/pull/2830) ([Kdecherf](https://github.com/Kdecherf))
- Translate missing strings for de-DE. [\#2826](https://github.com/wallabag/wallabag/pull/2826) ([jlnostr](https://github.com/jlnostr))
- Renamed Developer section to API client management [\#2824](https://github.com/wallabag/wallabag/pull/2824) ([nicosomb](https://github.com/nicosomb))
- Fix nav-panel-search height [\#2818](https://github.com/wallabag/wallabag/pull/2818) ([Kdecherf](https://github.com/Kdecherf))
- Added details about upgrade from 2.1.x or 2.2.0 [\#2816](https://github.com/wallabag/wallabag/pull/2816) ([nicosomb](https://github.com/nicosomb))
- Documentation on how to configure mobile apps to work with wallabag.it [\#2788](https://github.com/wallabag/wallabag/pull/2788) ([Zettt](https://github.com/Zettt))
- first\_article.rst already inside articles.rst [\#2785](https://github.com/wallabag/wallabag/pull/2785) ([matteocoder](https://github.com/matteocoder))
- share.rst already integrated inside articles.rst [\#2784](https://github.com/wallabag/wallabag/pull/2784) ([matteocoder](https://github.com/matteocoder))

## [2.2.1](https://github.com/wallabag/wallabag/tree/2.2.1) (2017-01-31)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.2.0...2.2.1)

- Fixed duplicate entry for share\_public in craue\_setting\_table [\#2809](https://github.com/wallabag/wallabag/pull/2809) ([nicosomb](https://github.com/nicosomb))

## [2.2.0](https://github.com/wallabag/wallabag/tree/2.2.0) (2017-01-28)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.1.6.1...2.2.0)

- Added indexes on is\_archived and is\_starred [\#2789](https://github.com/wallabag/wallabag/pull/2789) ([nicosomb](https://github.com/nicosomb))
- Fix \#2056 update config.yml [\#2624](https://github.com/wallabag/wallabag/pull/2624) ([Rurik19](https://github.com/Rurik19))

## [2.1.6.1](https://github.com/wallabag/wallabag/tree/2.1.6.1) (2017-01-23)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.1.6...2.1.6.1)

## [2.1.6](https://github.com/wallabag/wallabag/tree/2.1.6) (2017-01-18)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.1.5...2.1.6)

- Update copyright year [\#2736](https://github.com/wallabag/wallabag/pull/2736) ([lex111](https://github.com/lex111))
- Fixed possible JS injection via the title edition [\#2758](https://github.com/wallabag/wallabag/pull/2758) ([nicosomb](https://github.com/nicosomb))

## [2.1.5](https://github.com/wallabag/wallabag/tree/2.1.5) (2016-11-21)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.1.4...2.1.5)

- Force composer to run as PHP 5.5.9 [\#2623](https://github.com/wallabag/wallabag/pull/2623) ([j0k3r](https://github.com/j0k3r))

## [2.1.4](https://github.com/wallabag/wallabag/tree/2.1.4) (2016-11-19)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.1.3...2.1.4)

- Add .travis.yml change to RELEASE\_PROCESS [\#2605](https://github.com/wallabag/wallabag/pull/2605) ([j0k3r](https://github.com/j0k3r))
- wallabag can’t work on PostgreSQL \<= 9.1 [\#2604](https://github.com/wallabag/wallabag/pull/2604) ([j0k3r](https://github.com/j0k3r))
- Fix clear-cache problem using —no-dev [\#2603](https://github.com/wallabag/wallabag/pull/2603) ([j0k3r](https://github.com/j0k3r))
- User-agents have moved to site-config [\#2587](https://github.com/wallabag/wallabag/pull/2587) ([j0k3r](https://github.com/j0k3r))
- fix \#2582 - Documentation, Nginx config: disable all other PHP file from symphony [\#2584](https://github.com/wallabag/wallabag/pull/2584) ([blankoworld](https://github.com/blankoworld))
- Added help on config screen [\#2578](https://github.com/wallabag/wallabag/pull/2578) ([nicosomb](https://github.com/nicosomb))
- Added tooltips in header bar [\#2577](https://github.com/wallabag/wallabag/pull/2577) ([nicosomb](https://github.com/nicosomb))
- Changed behavior when we change language [\#2571](https://github.com/wallabag/wallabag/pull/2571) ([nicosomb](https://github.com/nicosomb))
- Added creation date on entries view [\#2570](https://github.com/wallabag/wallabag/pull/2570) ([nicosomb](https://github.com/nicosomb))
- Removed support website on about page [\#2565](https://github.com/wallabag/wallabag/pull/2565) ([nicosomb](https://github.com/nicosomb))
- Improve PR template [\#2563](https://github.com/wallabag/wallabag/pull/2563) ([j0k3r](https://github.com/j0k3r))
- Bigger image preview in case of only image content [\#2562](https://github.com/wallabag/wallabag/pull/2562) ([j0k3r](https://github.com/j0k3r))
- Improve tags list on small screen [\#2561](https://github.com/wallabag/wallabag/pull/2561) ([Rurik19](https://github.com/Rurik19))
- Replaced TokenStorage with TokenStorageInterface [\#2556](https://github.com/wallabag/wallabag/pull/2556) ([nicosomb](https://github.com/nicosomb))
- Reorder variable assignation in update.sh script, fix \#2554 [\#2555](https://github.com/wallabag/wallabag/pull/2555) ([dkrmr](https://github.com/dkrmr))
- Round readingtime to avoid crazy number [\#2552](https://github.com/wallabag/wallabag/pull/2552) ([j0k3r](https://github.com/j0k3r))
- Reordered documentation [\#2550](https://github.com/wallabag/wallabag/pull/2550) ([nicosomb](https://github.com/nicosomb))
- Updated default parameters.yml file in documentation [\#2546](https://github.com/wallabag/wallabag/pull/2546) ([nicosomb](https://github.com/nicosomb))
- Update the upgrade documentation [\#2545](https://github.com/wallabag/wallabag/pull/2545) ([nicosomb](https://github.com/nicosomb))

## [2.1.3](https://github.com/wallabag/wallabag/tree/2.1.3) (2016-11-04)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.1.2...2.1.3)

- Force user-agent for .slashdot.org [\#2528](https://github.com/wallabag/wallabag/pull/2528) ([Kdecherf](https://github.com/Kdecherf))
- Translation update - French [\#2519](https://github.com/wallabag/wallabag/pull/2519) ([Jibec](https://github.com/Jibec))
- docs: fix link to wallabag-stats project [\#2518](https://github.com/wallabag/wallabag/pull/2518) ([Strubbl](https://github.com/Strubbl))
- docs: update 3rd party projects by Strubbl [\#2514](https://github.com/wallabag/wallabag/pull/2514) ([Strubbl](https://github.com/Strubbl))
- Fix missing words in Android application documentation [\#2485](https://github.com/wallabag/wallabag/pull/2485) ([bmillemathias](https://github.com/bmillemathias))
- Removed MD5 hash in documentation [\#2466](https://github.com/wallabag/wallabag/pull/2466) ([nicosomb](https://github.com/nicosomb))
- Use created\_at as default sort [\#2534](https://github.com/wallabag/wallabag/pull/2534) ([j0k3r](https://github.com/j0k3r))
- Added documentation about failed to load external entity error [\#2530](https://github.com/wallabag/wallabag/pull/2530) ([nicosomb](https://github.com/nicosomb))
- Add Instapaper to CLI import [\#2524](https://github.com/wallabag/wallabag/pull/2524) ([lologhi](https://github.com/lologhi))
- fix path for  the install scripts [\#2521](https://github.com/wallabag/wallabag/pull/2521) ([foxmask](https://github.com/foxmask))
- Inject parameter instead of service [\#2520](https://github.com/wallabag/wallabag/pull/2520) ([j0k3r](https://github.com/j0k3r))
- Updated Capistrano configuration [\#2513](https://github.com/wallabag/wallabag/pull/2513) ([nicosomb](https://github.com/nicosomb))
- Exploded WallabagRestController into many controllers [\#2509](https://github.com/wallabag/wallabag/pull/2509) ([nicosomb](https://github.com/nicosomb))
- Added the whole path to parameters.yml file [\#2508](https://github.com/wallabag/wallabag/pull/2508) ([nicosomb](https://github.com/nicosomb))
- Added require.sh to check if composer is installed [\#2507](https://github.com/wallabag/wallabag/pull/2507) ([nicosomb](https://github.com/nicosomb))
- Fixed entries export filtered with a tag [\#2506](https://github.com/wallabag/wallabag/pull/2506) ([nicosomb](https://github.com/nicosomb))
- Added tag label in the page title [\#2504](https://github.com/wallabag/wallabag/pull/2504) ([nicosomb](https://github.com/nicosomb))
- Added a check in Makefile to see if composer is installed [\#2500](https://github.com/wallabag/wallabag/pull/2500) ([nicosomb](https://github.com/nicosomb))
- Add relevant links to fetch content error page [\#2493](https://github.com/wallabag/wallabag/pull/2493) ([bmillemathias](https://github.com/bmillemathias))
- Added :fr: documentation for wallabag backup [\#2486](https://github.com/wallabag/wallabag/pull/2486) ([nicosomb](https://github.com/nicosomb))
- Document what to backup in Wallabag [\#2484](https://github.com/wallabag/wallabag/pull/2484) ([bmillemathias](https://github.com/bmillemathias))
- If reload content failed, don’t update it [\#2482](https://github.com/wallabag/wallabag/pull/2482) ([j0k3r](https://github.com/j0k3r))
- Some fixes [\#2481](https://github.com/wallabag/wallabag/pull/2481) ([j0k3r](https://github.com/j0k3r))
- Portuguese \(Brazilian\) translation [\#2473](https://github.com/wallabag/wallabag/pull/2473) ([pmichelazzo](https://github.com/pmichelazzo))
- Update wallabag version for master branch [\#2467](https://github.com/wallabag/wallabag/pull/2467) ([nicosomb](https://github.com/nicosomb))
- UI Changes [\#2460](https://github.com/wallabag/wallabag/pull/2460) ([tcitworld](https://github.com/tcitworld))

## [2.1.2](https://github.com/wallabag/wallabag/tree/2.1.2) (2016-10-17)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.1.1...2.1.2)

- German: improve existing and add missing translation [\#2459](https://github.com/wallabag/wallabag/pull/2459) ([Strubbl](https://github.com/Strubbl))
- add link to German documentation in about page [\#2457](https://github.com/wallabag/wallabag/pull/2457) ([Strubbl](https://github.com/Strubbl))
- Bring make dev [\#2451](https://github.com/wallabag/wallabag/pull/2451) ([tcitworld](https://github.com/tcitworld))
- Update ISSUE\_TEMPLATE.md [\#2432](https://github.com/wallabag/wallabag/pull/2432) ([j0k3r](https://github.com/j0k3r))
- Define a dev version for the master [\#2417](https://github.com/wallabag/wallabag/pull/2417) ([j0k3r](https://github.com/j0k3r))
- try to reduce assets build npm connection failing by updating nodejs [\#2375](https://github.com/wallabag/wallabag/pull/2375) ([tcitworld](https://github.com/tcitworld))
- Fixed hardcoded title for internal settings [\#2464](https://github.com/wallabag/wallabag/pull/2464) ([nicosomb](https://github.com/nicosomb))
- Fix tabs on material [\#2455](https://github.com/wallabag/wallabag/pull/2455) ([tcitworld](https://github.com/tcitworld))
- Fix baggy display on small screens [\#2454](https://github.com/wallabag/wallabag/pull/2454) ([tcitworld](https://github.com/tcitworld))
- View improvements [\#2450](https://github.com/wallabag/wallabag/pull/2450) ([nicosomb](https://github.com/nicosomb))
- Fixed french and german doc homepages [\#2447](https://github.com/wallabag/wallabag/pull/2447) ([nicosomb](https://github.com/nicosomb))
- Added information about tagging rules in documentation [\#2446](https://github.com/wallabag/wallabag/pull/2446) ([nicosomb](https://github.com/nicosomb))
- Mention example instance in docs [\#2444](https://github.com/wallabag/wallabag/pull/2444) ([Kaligule](https://github.com/Kaligule))
- Minor fixes in the english documentation [\#2439](https://github.com/wallabag/wallabag/pull/2439) ([zertrin](https://github.com/zertrin))
- Added french documentation for upgrade [\#2435](https://github.com/wallabag/wallabag/pull/2435) ([nicosomb](https://github.com/nicosomb))
- Added french documentation for parameters.yml [\#2434](https://github.com/wallabag/wallabag/pull/2434) ([nicosomb](https://github.com/nicosomb))
- Lock deps for FOSUser [\#2429](https://github.com/wallabag/wallabag/pull/2429) ([j0k3r](https://github.com/j0k3r))
- Fix links on english documentation homepage [\#2426](https://github.com/wallabag/wallabag/pull/2426) ([nicosomb](https://github.com/nicosomb))
- Fixed display for note in installation page [\#2422](https://github.com/wallabag/wallabag/pull/2422) ([nicosomb](https://github.com/nicosomb))
- Avoid error when Redis isn't here in tests [\#2420](https://github.com/wallabag/wallabag/pull/2420) ([j0k3r](https://github.com/j0k3r))
- Fixed Twitter Cards by adding a description tag [\#2419](https://github.com/wallabag/wallabag/pull/2419) ([nicosomb](https://github.com/nicosomb))
- Added support of Twitter Cards for public articles [\#2418](https://github.com/wallabag/wallabag/pull/2418) ([nicosomb](https://github.com/nicosomb))
- Remove automatic closing of the window from bookmarklet [\#2414](https://github.com/wallabag/wallabag/pull/2414) ([szafranek](https://github.com/szafranek))
- When a sub command fail, display error message [\#2413](https://github.com/wallabag/wallabag/pull/2413) ([j0k3r](https://github.com/j0k3r))
- Fix PostgreSQL migrations [\#2412](https://github.com/wallabag/wallabag/pull/2412) ([j0k3r](https://github.com/j0k3r))
- Fix entities definition [\#2411](https://github.com/wallabag/wallabag/pull/2411) ([j0k3r](https://github.com/j0k3r))
- Optimize tag list display [\#2410](https://github.com/wallabag/wallabag/pull/2410) ([j0k3r](https://github.com/j0k3r))
- Show number of annotations instead of nbAnnotations placeholder [\#2406](https://github.com/wallabag/wallabag/pull/2406) ([szafranek](https://github.com/szafranek))
- Fix few invalid HTML tags [\#2405](https://github.com/wallabag/wallabag/pull/2405) ([szafranek](https://github.com/szafranek))
- Cleaned up documentation for installation process [\#2403](https://github.com/wallabag/wallabag/pull/2403) ([nicosomb](https://github.com/nicosomb))
- Removed 1.x stuff in CHANGELOG [\#2402](https://github.com/wallabag/wallabag/pull/2402) ([nicosomb](https://github.com/nicosomb))
- Set env to prod in documentation [\#2400](https://github.com/wallabag/wallabag/pull/2400) ([j0k3r](https://github.com/j0k3r))
- Use default locale for user config [\#2399](https://github.com/wallabag/wallabag/pull/2399) ([j0k3r](https://github.com/j0k3r))
- Ensure orphan tag are remove in API [\#2397](https://github.com/wallabag/wallabag/pull/2397) ([j0k3r](https://github.com/j0k3r))
- Update messages.pl.yml [\#2396](https://github.com/wallabag/wallabag/pull/2396) ([mruminski](https://github.com/mruminski))
- Add ability to use socket [\#2395](https://github.com/wallabag/wallabag/pull/2395) ([j0k3r](https://github.com/j0k3r))
- Ability to check multiple urls in API [\#2393](https://github.com/wallabag/wallabag/pull/2393) ([j0k3r](https://github.com/j0k3r))
- Added default picture if preview picture is null [\#2389](https://github.com/wallabag/wallabag/pull/2389) ([nicosomb](https://github.com/nicosomb))
- Fixed two-factor checkbox display in user admin panel [\#2388](https://github.com/wallabag/wallabag/pull/2388) ([nicosomb](https://github.com/nicosomb))
- Changed Changelog by using github-changelog-generator from @skywinder [\#2386](https://github.com/wallabag/wallabag/pull/2386) ([nicosomb](https://github.com/nicosomb))
- Added documentation about siteconfig fix [\#2385](https://github.com/wallabag/wallabag/pull/2385) ([nicosomb](https://github.com/nicosomb))
- Added OpenGraph support for public articles [\#2383](https://github.com/wallabag/wallabag/pull/2383) ([nicosomb](https://github.com/nicosomb))
- Fix exists API call [\#2377](https://github.com/wallabag/wallabag/pull/2377) ([tcitworld](https://github.com/tcitworld))
- Clickable tags [\#2374](https://github.com/wallabag/wallabag/pull/2374) ([tcitworld](https://github.com/tcitworld))
- Remove mouf/nodejs-installer from composer [\#2363](https://github.com/wallabag/wallabag/pull/2363) ([j0k3r](https://github.com/j0k3r))
- Changed relation between API client and refresh token [\#2351](https://github.com/wallabag/wallabag/pull/2351) ([nicosomb](https://github.com/nicosomb))
- Fix relations export for Entry [\#2332](https://github.com/wallabag/wallabag/pull/2332) ([j0k3r](https://github.com/j0k3r))

## [2.1.1](https://github.com/wallabag/wallabag/tree/2.1.1) (2016-10-04)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.1.0...2.1.1)

- Create config even if user is disabled [\#2359](https://github.com/wallabag/wallabag/pull/2359) ([j0k3r](https://github.com/j0k3r))
- Add php-bcmath extension to requirements [\#2354](https://github.com/wallabag/wallabag/pull/2354) ([Zayon](https://github.com/Zayon))
- Basically, fix everything [\#2353](https://github.com/wallabag/wallabag/pull/2353) ([tcitworld](https://github.com/tcitworld))
- Update messages.pl.yml [\#2341](https://github.com/wallabag/wallabag/pull/2341) ([mruminski](https://github.com/mruminski))
-  small improvement for german translation [\#2340](https://github.com/wallabag/wallabag/pull/2340) ([Strubbl](https://github.com/Strubbl))
- Fix for 2.1 installation [\#2338](https://github.com/wallabag/wallabag/pull/2338) ([j0k3r](https://github.com/j0k3r))

## [2.1.0](https://github.com/wallabag/wallabag/tree/2.1.0) (2016-10-03)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.8...2.1.0)

- Docker : install PHP 'gd' extension [\#2319](https://github.com/wallabag/wallabag/pull/2319) ([pmartin](https://github.com/pmartin))
- Fix issue \#2296: epub export with special chars in the title. [\#2297](https://github.com/wallabag/wallabag/pull/2297) ([egilli](https://github.com/egilli))
- Remove error message when creating ePub versions [\#2330](https://github.com/wallabag/wallabag/pull/2330) ([pmichelazzo](https://github.com/pmichelazzo))

## [2.0.8](https://github.com/wallabag/wallabag/tree/2.0.8) (2016-09-07)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.7...2.0.8)

- Allow failure for PHP 7.1 [\#2236](https://github.com/wallabag/wallabag/pull/2236) ([j0k3r](https://github.com/j0k3r))
- Add a check for the database connection [\#2262](https://github.com/wallabag/wallabag/pull/2262) ([j0k3r](https://github.com/j0k3r))
- Fix issue \#1991: correction of the height field to add articles [\#2241](https://github.com/wallabag/wallabag/pull/2241) ([modos189](https://github.com/modos189))
- V2 improve view [\#2238](https://github.com/wallabag/wallabag/pull/2238) ([modos189](https://github.com/modos189))
- Add configuration for german documentation [\#2235](https://github.com/wallabag/wallabag/pull/2235) ([nicosomb](https://github.com/nicosomb))
- Fixes mailto link in documentation [\#2234](https://github.com/wallabag/wallabag/pull/2234) ([cstuder](https://github.com/cstuder))
- Cut entries title in card view: continued [\#2230](https://github.com/wallabag/wallabag/pull/2230) ([modos189](https://github.com/modos189))

## [2.0.7](https://github.com/wallabag/wallabag/tree/2.0.7) (2016-08-22)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.6...2.0.7)

- Avoid breaking import when fetching fail [\#2224](https://github.com/wallabag/wallabag/pull/2224) ([j0k3r](https://github.com/j0k3r))
- Added creation date and reading time on article view [\#2222](https://github.com/wallabag/wallabag/pull/2222) ([nicosomb](https://github.com/nicosomb))
- Replaced favorite word/icon with star one [\#2221](https://github.com/wallabag/wallabag/pull/2221) ([nicosomb](https://github.com/nicosomb))
- Enable PATCH method for CORS in API part [\#2220](https://github.com/wallabag/wallabag/pull/2220) ([Rurik19](https://github.com/Rurik19))
- Enable CORS headers for OAUTH part [\#2216](https://github.com/wallabag/wallabag/pull/2216) ([Rurik19](https://github.com/Rurik19))
- Run tests on an uptodate HHVM [\#2134](https://github.com/wallabag/wallabag/pull/2134) ([j0k3r](https://github.com/j0k3r))
- Fix form user display when 2FA is disabled [\#2095](https://github.com/wallabag/wallabag/pull/2095) ([nicosomb](https://github.com/nicosomb))

## [2.0.6](https://github.com/wallabag/wallabag/tree/2.0.6) (2016-08-10)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.5...2.0.6)

- Run PHP 7.1 on Travis [\#2048](https://github.com/wallabag/wallabag/pull/2048) ([j0k3r](https://github.com/j0k3r))
- Fixed typo in entry:notice:entry\_saved [\#2200](https://github.com/wallabag/wallabag/pull/2200) ([charno6](https://github.com/charno6))
- Handling socials links into a config file [\#2199](https://github.com/wallabag/wallabag/pull/2199) ([Simounet](https://github.com/Simounet))
- FIX image inside a figure element max-width [\#2198](https://github.com/wallabag/wallabag/pull/2198) ([Simounet](https://github.com/Simounet))
- Remove binary from repo [\#2195](https://github.com/wallabag/wallabag/pull/2195) ([j0k3r](https://github.com/j0k3r))
- Fixed spelling Artúclos --\> Artículos [\#2194](https://github.com/wallabag/wallabag/pull/2194) ([benages](https://github.com/benages))
- Fix 3rd-Party Apps links \(Chrome & Firefox\) [\#2185](https://github.com/wallabag/wallabag/pull/2185) ([tcitworld](https://github.com/tcitworld))
- Change the way to login user in tests [\#2172](https://github.com/wallabag/wallabag/pull/2172) ([j0k3r](https://github.com/j0k3r))
- Fix a few french translations typos [\#2165](https://github.com/wallabag/wallabag/pull/2165) ([tcitworld](https://github.com/tcitworld))
- Update symlink to php-cs-fixer [\#2160](https://github.com/wallabag/wallabag/pull/2160) ([j0k3r](https://github.com/j0k3r))
- Handle only upper or only lower reading filter [\#2157](https://github.com/wallabag/wallabag/pull/2157) ([j0k3r](https://github.com/j0k3r))
- Try to find bad redirection after delete [\#2156](https://github.com/wallabag/wallabag/pull/2156) ([j0k3r](https://github.com/j0k3r))
- Use friendsofphp instead of fabpot [\#2155](https://github.com/wallabag/wallabag/pull/2155) ([j0k3r](https://github.com/j0k3r))
- translate documentation to German [\#2148](https://github.com/wallabag/wallabag/pull/2148) ([Strubbl](https://github.com/Strubbl))
- Corrected Regex for lighttpd rewrite [\#2145](https://github.com/wallabag/wallabag/pull/2145) ([even-allmighty](https://github.com/even-allmighty))
- Jump to Symfony 3.1 [\#2132](https://github.com/wallabag/wallabag/pull/2132) ([j0k3r](https://github.com/j0k3r))

## [2.0.5](https://github.com/wallabag/wallabag/tree/2.0.5) (2016-05-31)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.4...2.0.5)

- Improve English translation [\#2109](https://github.com/wallabag/wallabag/pull/2109) ([Poorchop](https://github.com/Poorchop))
- Update api.rst [\#2044](https://github.com/wallabag/wallabag/pull/2044) ([joshp23](https://github.com/joshp23))
- new details in the doc about the rights access again;\) [\#2038](https://github.com/wallabag/wallabag/pull/2038) ([foxmask](https://github.com/foxmask))
- Fix the deletion of Tags/Entries relation when delete an entry [\#2122](https://github.com/wallabag/wallabag/pull/2122) ([nicosomb](https://github.com/nicosomb))
- Docs proposal [\#2112](https://github.com/wallabag/wallabag/pull/2112) ([Poorchop](https://github.com/Poorchop))
- add screenshots of android docu in English [\#2111](https://github.com/wallabag/wallabag/pull/2111) ([Strubbl](https://github.com/Strubbl))
- CS [\#2098](https://github.com/wallabag/wallabag/pull/2098) ([j0k3r](https://github.com/j0k3r))
- Fix image path in 2-factor authentification email [\#2097](https://github.com/wallabag/wallabag/pull/2097) ([bmillemathias](https://github.com/bmillemathias))
- Update CONTRIBUTING file [\#2094](https://github.com/wallabag/wallabag/pull/2094) ([nicosomb](https://github.com/nicosomb))
- Replace vertical dots in material theme with horizontal dots [\#2093](https://github.com/wallabag/wallabag/pull/2093) ([nicosomb](https://github.com/nicosomb))
- Starred and Archived clears if article is already exists [\#2092](https://github.com/wallabag/wallabag/pull/2092) ([Rurik19](https://github.com/Rurik19))
- Do not specify language in Firefox addon link [\#2069](https://github.com/wallabag/wallabag/pull/2069) ([merwan](https://github.com/merwan))
- Added information about permissions on data/ [\#2068](https://github.com/wallabag/wallabag/pull/2068) ([mariovor](https://github.com/mariovor))
- Update CraueConfigBundle.it.yml [\#2054](https://github.com/wallabag/wallabag/pull/2054) ([jamiroconca](https://github.com/jamiroconca))
- Add unread filter to entries pages [\#2052](https://github.com/wallabag/wallabag/pull/2052) ([danbartram](https://github.com/danbartram))
- Update api.rst [\#2049](https://github.com/wallabag/wallabag/pull/2049) ([joshp23](https://github.com/joshp23))

## [2.0.4](https://github.com/wallabag/wallabag/tree/2.0.4) (2016-05-07)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.3...2.0.4)

- Change Travis/Scrutinizer pictures in README [\#2029](https://github.com/wallabag/wallabag/pull/2029) ([nicosomb](https://github.com/nicosomb))
- Docu for android app [\#2028](https://github.com/wallabag/wallabag/pull/2028) ([Strubbl](https://github.com/Strubbl))
- Update messages.it.yml [\#2024](https://github.com/wallabag/wallabag/pull/2024) ([jamiroconca](https://github.com/jamiroconca))
- Fix translation for validators [\#2023](https://github.com/wallabag/wallabag/pull/2023) ([nicosomb](https://github.com/nicosomb))
- Fix pagination bar on small devices [\#2022](https://github.com/wallabag/wallabag/pull/2022) ([nicosomb](https://github.com/nicosomb))
- Fix number of entries in tag/list [\#2020](https://github.com/wallabag/wallabag/pull/2020) ([nicosomb](https://github.com/nicosomb))
- Create CraueConfigBundle.it.yml [\#2019](https://github.com/wallabag/wallabag/pull/2019) ([jamiroconca](https://github.com/jamiroconca))
- Update config.yml, add italian as available language [\#2018](https://github.com/wallabag/wallabag/pull/2018) ([jamiroconca](https://github.com/jamiroconca))
- Create messages.it.yml [\#2017](https://github.com/wallabag/wallabag/pull/2017) ([jamiroconca](https://github.com/jamiroconca))
- Update documentation [\#2016](https://github.com/wallabag/wallabag/pull/2016) ([nicosomb](https://github.com/nicosomb))
- Fix tags listing [\#2013](https://github.com/wallabag/wallabag/pull/2013) ([nicosomb](https://github.com/nicosomb))
- integrate upgrade.rst [\#2012](https://github.com/wallabag/wallabag/pull/2012) ([biva](https://github.com/biva))
- upgrade.rst \(Creation of an upgrade page in the documentation\) [\#2011](https://github.com/wallabag/wallabag/pull/2011) ([biva](https://github.com/biva))
- Set the title via POST /api/entries [\#2010](https://github.com/wallabag/wallabag/pull/2010) ([nicosomb](https://github.com/nicosomb))
- Fix reading speed not defined when user was created via config page [\#2005](https://github.com/wallabag/wallabag/pull/2005) ([nicosomb](https://github.com/nicosomb))
- Fix old branch name urls [\#2001](https://github.com/wallabag/wallabag/pull/2001) ([tcitworld](https://github.com/tcitworld))
- Update CraueConfigBundle.es.yml [\#1992](https://github.com/wallabag/wallabag/pull/1992) ([jami7](https://github.com/jami7))
- Rights access to the folders of the project [\#1985](https://github.com/wallabag/wallabag/pull/1985) ([foxmask](https://github.com/foxmask))
- Es translation [\#1977](https://github.com/wallabag/wallabag/pull/1977) ([j0k3r](https://github.com/j0k3r))
- Fix filter reading time [\#1976](https://github.com/wallabag/wallabag/pull/1976) ([nicosomb](https://github.com/nicosomb))
- Fix typos in API documentation [\#1970](https://github.com/wallabag/wallabag/pull/1970) ([nicosomb](https://github.com/nicosomb))
- Create 3rd Resources chapter in API documentation [\#1969](https://github.com/wallabag/wallabag/pull/1969) ([nicosomb](https://github.com/nicosomb))
- Add FAQ page in documentation [\#1967](https://github.com/wallabag/wallabag/pull/1967) ([nicosomb](https://github.com/nicosomb))

## [2.0.3](https://github.com/wallabag/wallabag/tree/2.0.3) (2016-04-22)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.2...2.0.3)

- Update API documentation with cURL examples [\#1962](https://github.com/wallabag/wallabag/pull/1962) ([nicosomb](https://github.com/nicosomb))

## [2.0.2](https://github.com/wallabag/wallabag/tree/2.0.2) (2016-04-21)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9.2...2.0.2)

- Fix translation for Go to your account button after subscription [\#1957](https://github.com/wallabag/wallabag/pull/1957) ([nicosomb](https://github.com/nicosomb))
- Update links in documentation [\#1954](https://github.com/wallabag/wallabag/pull/1954) ([nicosomb](https://github.com/nicosomb))
- Actualisation des liens morts \(Documentation de traduction\) [\#1953](https://github.com/wallabag/wallabag/pull/1953) ([maxi62330](https://github.com/maxi62330))
- Added some curl examples [\#1945](https://github.com/wallabag/wallabag/pull/1945) ([ddeimeke](https://github.com/ddeimeke))
- Update Travis configuration with branches renaming [\#1944](https://github.com/wallabag/wallabag/pull/1944) ([nicosomb](https://github.com/nicosomb))
- Optimize import [\#1942](https://github.com/wallabag/wallabag/pull/1942) ([nicosomb](https://github.com/nicosomb))

## [1.9.2](https://github.com/wallabag/wallabag/tree/1.9.2) (2016-04-18)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.1...1.9.2)

## [2.0.1](https://github.com/wallabag/wallabag/tree/2.0.1) (2016-04-11)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.0...2.0.1)

## [2.0.0](https://github.com/wallabag/wallabag/tree/2.0.0) (2016-04-03)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.0-beta.2...2.0.0)

## [2.0.0-beta.2](https://github.com/wallabag/wallabag/tree/2.0.0-beta.2) (2016-03-12)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.0-beta.1...2.0.0-beta.2)

## [2.0.0-beta.1](https://github.com/wallabag/wallabag/tree/2.0.0-beta.1) (2016-03-01)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.0-alpha.2...2.0.0-beta.1)

## [2.0.0-alpha.2](https://github.com/wallabag/wallabag/tree/2.0.0-alpha.2) (2016-01-22)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.0-alpha.1...2.0.0-alpha.2)

## [2.0.0-alpha.1](https://github.com/wallabag/wallabag/tree/2.0.0-alpha.1) (2016-01-07)
[Full Changelog](https://github.com/wallabag/wallabag/compare/2.0.0-alpha.0...2.0.0-alpha.1)

## [2.0.0-alpha.0](https://github.com/wallabag/wallabag/tree/2.0.0-alpha.0) (2015-09-14)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9.1-b...2.0.0-alpha.0)

## [1.9.1-b](https://github.com/wallabag/wallabag/tree/1.9.1-b) (2015-08-04)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9.1...1.9.1-b)

## [1.9.1](https://github.com/wallabag/wallabag/tree/1.9.1) (2015-08-03)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9.1beta3...1.9.1)

## [1.9.1beta3](https://github.com/wallabag/wallabag/tree/1.9.1beta3) (2015-06-06)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9.1beta2...1.9.1beta3)

## [1.9.1beta2](https://github.com/wallabag/wallabag/tree/1.9.1beta2) (2015-05-09)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9.1beta1...1.9.1beta2)

## [1.9.1beta1](https://github.com/wallabag/wallabag/tree/1.9.1beta1) (2015-04-08)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9.1alpha2...1.9.1beta1)

## [1.9.1alpha2](https://github.com/wallabag/wallabag/tree/1.9.1alpha2) (2015-04-07)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9.1alpha1...1.9.1alpha2)

## [1.9.1alpha1](https://github.com/wallabag/wallabag/tree/1.9.1alpha1) (2015-03-08)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9...1.9.1alpha1)

## [1.9](https://github.com/wallabag/wallabag/tree/1.9) (2015-02-18)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9RC1...1.9)

## [1.9RC1](https://github.com/wallabag/wallabag/tree/1.9RC1) (2015-02-16)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9beta2...1.9RC1)

## [1.9beta2](https://github.com/wallabag/wallabag/tree/1.9beta2) (2015-02-15)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.9beta...1.9beta2)

## [1.9beta](https://github.com/wallabag/wallabag/tree/1.9beta) (2015-02-14)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.8.1old...1.9beta)

## [1.8.1old](https://github.com/wallabag/wallabag/tree/1.8.1old) (2014-11-16)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.8.1bis...1.8.1old)

## [1.8.1bis](https://github.com/wallabag/wallabag/tree/1.8.1bis) (2014-11-16)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.8.1b...1.8.1bis)

## [1.8.1b](https://github.com/wallabag/wallabag/tree/1.8.1b) (2014-11-16)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.8.1...1.8.1b)

## [1.8.1](https://github.com/wallabag/wallabag/tree/1.8.1) (2014-11-15)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.8.0...1.8.1)

## [1.8.0](https://github.com/wallabag/wallabag/tree/1.8.0) (2014-10-10)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.7.2...1.8.0)

## [1.7.2](https://github.com/wallabag/wallabag/tree/1.7.2) (2014-07-24)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.7.1...1.7.2)

## [1.7.1](https://github.com/wallabag/wallabag/tree/1.7.1) (2014-07-15)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.7.0...1.7.1)

## [1.7.0](https://github.com/wallabag/wallabag/tree/1.7.0) (2014-05-29)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.6.1b...1.7.0)

## [1.6.1b](https://github.com/wallabag/wallabag/tree/1.6.1b) (2014-04-11)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.6.1...1.6.1b)

## [1.6.1](https://github.com/wallabag/wallabag/tree/1.6.1) (2014-04-03)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.6.0...1.6.1)

## [1.6.0](https://github.com/wallabag/wallabag/tree/1.6.0) (2014-04-03)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.5.2...1.6.0)

## [1.5.2](https://github.com/wallabag/wallabag/tree/1.5.2) (2014-02-21)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.5.1.1...1.5.2)

## [1.5.1.1](https://github.com/wallabag/wallabag/tree/1.5.1.1) (2014-02-19)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.5.1...1.5.1.1)

## [1.5.1](https://github.com/wallabag/wallabag/tree/1.5.1) (2014-02-19)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.5.0...1.5.1)

## [1.5.0](https://github.com/wallabag/wallabag/tree/1.5.0) (2014-02-13)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.4.0...1.5.0)

## [1.4.0](https://github.com/wallabag/wallabag/tree/1.4.0) (2014-02-03)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.3.1...1.4.0)

## [1.3.1](https://github.com/wallabag/wallabag/tree/1.3.1) (2014-01-07)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.3.0...1.3.1)

## [1.3.0](https://github.com/wallabag/wallabag/tree/1.3.0) (2013-12-23)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.2.0...1.3.0)

## [1.2.0](https://github.com/wallabag/wallabag/tree/1.2.0) (2013-11-25)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.1.0...1.2.0)

## [1.1.0](https://github.com/wallabag/wallabag/tree/1.1.0) (2013-10-25)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.0.0...1.1.0)

## [1.0.0](https://github.com/wallabag/wallabag/tree/1.0.0) (2013-10-03)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.0-beta5.2...1.0.0)

## [1.0-beta5.2](https://github.com/wallabag/wallabag/tree/1.0-beta5.2) (2013-09-20)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.0-beta5.1...1.0-beta5.2)

## [1.0-beta5.1](https://github.com/wallabag/wallabag/tree/1.0-beta5.1) (2013-09-20)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.0-beta5...1.0-beta5.1)

## [1.0-beta5](https://github.com/wallabag/wallabag/tree/1.0-beta5) (2013-09-20)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.0-beta4...1.0-beta5)

## [1.0-beta4](https://github.com/wallabag/wallabag/tree/1.0-beta4) (2013-08-25)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.0-beta3...1.0-beta4)

## [1.0-beta3](https://github.com/wallabag/wallabag/tree/1.0-beta3) (2013-08-17)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.0-beta2...1.0-beta3)

## [1.0-beta2](https://github.com/wallabag/wallabag/tree/1.0-beta2) (2013-08-11)
[Full Changelog](https://github.com/wallabag/wallabag/compare/1.0-beta1...1.0-beta2)

## [1.0-beta1](https://github.com/wallabag/wallabag/tree/1.0-beta1) (2013-08-07)
[Full Changelog](https://github.com/wallabag/wallabag/compare/0.3...1.0-beta1)

## [0.3](https://github.com/wallabag/wallabag/tree/0.3) (2013-07-31)
[Full Changelog](https://github.com/wallabag/wallabag/compare/0.2.1...0.3)

## [0.2.1](https://github.com/wallabag/wallabag/tree/0.2.1) (2013-04-23)
[Full Changelog](https://github.com/wallabag/wallabag/compare/0.2...0.2.1)

## [0.2](https://github.com/wallabag/wallabag/tree/0.2) (2013-04-21)
[Full Changelog](https://github.com/wallabag/wallabag/compare/0.11...0.2)

## [0.11](https://github.com/wallabag/wallabag/tree/0.11) (2013-04-19)
[Full Changelog](https://github.com/wallabag/wallabag/compare/0.1...0.11)

## [0.1](https://github.com/wallabag/wallabag/tree/0.1) (2013-04-19)
