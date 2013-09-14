yum Cookbook CHANGELOG
======================
This file is used to list changes made in each version of the yum cookbook.


v2.3.2
------
### Bug
- **[COOK-3145](https://tickets.opscode.com/browse/COOK-3145)** - Use correct download URL for epel `key_url`

v2.3.0
------
### New Feature
- [COOK-2924]: Yum should allow type setting in repo file

v2.2.4
------
### Bug
- [COOK-2360]: last commit to `yum_repository` changes previous behaviour
- [COOK-3015]: Yum cookbook test minitest to fail

v2.2.2
------
### Improvement
- [COOK-2741]: yum::elrepo
- [COOK-2946]: update tests, test kitchen support in yum cookbook

### Bug
- [COOK-2639]: Yum cookbook - epel - always assumes url is a mirror list
- [COOK-2663]: Yum should allow metadata_expire setting in repo file
- [COOK-2751]: Update yum.ius_release version to 1.0-11

v2.2.0
------
- [COOK-2189] - yum::ius failed on install (caused from rpm dependency)
- [COOK-2196] - Make includepkgs and exclude configurable for each repos
- [COOK-2244] - Allow configuring caching using attributes
- [COOK-2399] - yum cookbook LWRPs fail FoodCritic
- [COOK-2519] - Add priority option to Yum repo files
- [COOK-2593] - allow integer or string for yum priority
- [COOK-2643] - don't use conditional attribute for `yum_key` `remote_file`

v2.1.0
------
- [COOK-2045] - add remi repository recipe
- [COOK-2121] - add `:create` action to `yum_repository`

v2.0.6
------
- [COOK-2037] - minor style fixes
- [COOK-2038] - updated README

v2.0.4
------
- [COOK-1908] - unable to install repoforge on CentOS 6 32 bit

v2.0.2
------
- [COOK-1758] - Add default action for repository resource

v2.0.0
------
This version changes the behavior of the EPEL recipe (most commonly used in other Opscode cookbooks) on Amazon, and removes an attribute, `node['yum']['epel_release']`. See the README for details.

- [COOK-1772] - Simplify management of EPEL with LWRP

v1.0.0
------
`mirrorlist` in the `yum_repository` LWRP must be set to the mirror list URI to use rather than setting it to true. See README.md.

- [COOK-1088] - use dl.fedoraproject.org for EPEL to prevent redirects
- [COOK-1653] - fix mirrorlist
- [COOK-1710] - support http proxy
- [COOK-1722] - update IUS version

v0.8.2
------
- [COOK-1521] - add :update action to `yum_repository`

v0.8.0
------
- [COOK-1204] - Make 'add' default action for yum_repository
- [COOK-1351] - option to not make the yum cache (via attribute)
- [COOK-1353] - x86_64 centos path fixes
- [COOK-1414] - recipe for repoforge

v0.6.2
------
- Updated README to remove git diff artifacts.

v0.6.0
------
- Default action for the yum_repository LWRP is now add.
- [COOK-1227] - clear Chefs internal cache after adding new yum repo
- [COOK-1262] - yum::epel should enable existing repo on Amazon Linux
- [COOK-1272], [COOK-1302] - update RPM file for CentOS / RHEL 6
- [COOK-1330] - update cookbook documentation on excludes for yum
- [COOK-1346] - retry remote_file for EPEL in case we get an FTP mirror


v0.5.2
------
- [COOK-825] - epel and ius `remote_file` should notify the `rpm_package` to install

v0.5.0
------
- [COOK-675] - add recipe for handling EPEL repository
- [COOK-722] - add recipe for handling IUS repository

v.0.1.2
------
- Remove yum update in default recipe, that doesn't update caches, it updates packages installed.
