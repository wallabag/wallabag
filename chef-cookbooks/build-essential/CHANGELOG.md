build-essential Cookbook CHANGELOG
==================================
This file is used to list changes made in each version of the build-essential cookbook.

v1.4.2
------
### Bug
- **[COOK-3318](https://tickets.opscode.com/browse/COOK-3318)** - Use Mixlib::ShellOut instead of Chef::ShellOut

### New Feature
- **[COOK-3093](https://tickets.opscode.com/browse/COOK-3093)** - Add OmniOS support

### Improvement
- **[COOK-3024](https://tickets.opscode.com/browse/COOK-3024)** - Use newer package on SmartOS

v1.4.0
------
This version splits up the default recipe into recipes included based on the node's platform_family.

- [COOK-2505] - backport omnibus builder improvements

v1.3.4
------
- [COOK-2272] - Complete `platform_family` conversion in build-essential

v1.3.2
------
- [COOK-2069] - build-essential will install osx-gcc-installer when XCode is present

v1.3.0
------
- [COOK-1895] - support smartos

v1.2.0
------
- Add test-kitchen support (source repo only)
- [COOK-1677] - build-essential cookbook support for OpenSuse and SLES
- [COOK-1718] - build-essential cookbook metadata should include scientific
- [COOK-1768] - The apt-get update in build-essentials needs to be renamed

v1.1.2
------
- [COOK-1620] - support OS X 10.8

v1.1.0
------
- [COOK-1098] - support amazon linux
- [COOK-1149] - support Mac OS X
- [COOK-1296] - allow for compile-time installation of packages through an attribute (see README)

v1.0.2
------
- [COOK-1098] - Add Amazon Linux platform support
- [COOK-1149] - Add OS X platform support
