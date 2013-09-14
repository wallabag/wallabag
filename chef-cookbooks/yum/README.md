yum Cookbook
============
Configures various YUM components on Red Hat-like systems.  Includes LWRP for managing repositories and their GPG keys.

Based on the work done by Eric Wolfe and Charles Duffy on the [yumrepo](https://github.com/atomic-penguin/cookbook-yumrepo) cookbook.


Requirements
------------
Red Hat Enterprise Linux 5, and 6 distributions within this platform family.


Attributes
----------
* `yum['exclude']`
    - An array containing a list of packages to exclude from updates or installs.  Wildcards and shell globs are supported.
    - Defaults to an empty exclude list.

* `yum['installonlypkgs']`
    - An array containing a list of packages which should only be
      installed, never updated.
    - Defaults to an empty install-only list.

* `yum['ius_release']`
    - Set the IUS release to install.
    - Defaults to the current release of the IUS repo.

* `yum['repoforge_release']`
    - Set the RepoForge release to install.
    - Defaults to the current release of the repoforge repo.

EPEL attributes used in the `yum::epel` recipe, see `attributes/epel.rb` for default values:

* `yum['epel']['key']`
    - Name of the GPG key used for the repo.

* `yum['epel']['baseurl']`
    - Base URL to an EPEL mirror.

* `yum['epel']['url']`
    - URL to the EPEL mirrorlist.

* `yum['epel']['key_url']`
    - URL to the GPG key for the repo.

* `yum['epel']['includepkgs']`
    - list of packages you want to use for the repo.

* `yum['epel']['exclude']`
    - list of packages you do NOT want to use for the repo.

The `node['yum']['epel_release']` attribute is removed, see the __epel__ recipe information below.

remi attributes used in the `yum::remi` recipe, see `attributes/remi.rb` for default values:

* `yum['remi']['key']`
    - Name of the GPG key used for the repo.

* `yum['remi']['url']`
    - URL to the remi mirrorlist.

* `yum['remi']['key_url']`
    - URL to the GPG key for the repo.

* `yum['remi']['includepkgs']`
    - list of packages you want to use for the repo.

* `yum['remi']['exclude']`
    - list of packages you do NOT want to use for the repo.

Proxy settings used in yum.conf on RHEL family 5 and 6:

* `yum['proxy']`
    - Set the URL for an HTTP proxy
    - None of the proxy settings are used if this is an empty string
      (default)

* `yum['proxy_username']`
    - Set the username for the proxy
    - not used if `yum['proxy']` above is an empty string

* `yum['proxy_password']`
    - Set the password for the proxy
    - not used if `yum['proxy']` above is an empty string


Recipes
-------
### default
The default recipe does nothing.

### yum
Manages the configuration of the `/etc/yum.conf` via attributes.  See the aforementioned Array attributes `yum['exclude']` and `yum['installonlypkgs']`.

### epel
Uses the `yum_key` and `yum_repository` resources from this cookbook are used to manage the main EPEL repository. If you need other EPEL repositories (source, debug-info), use the `yum_repository` LWRP in your own cookbook where those packages are required. The recipe will use the `yum['epel']` attributes (see above) to configure the key, url and download the GPG key for the repo. The defaults are detected by platform and version and should just work without modification in most use cases.

On all platforms except Amazon, the action is to add the repository. On Amazon, the action is add and update.

Amazon Linux has the EPEL repositories already added in the AMI. In previous versions of this cookbook, they were enabled with `yum-config-manager`, however in the current version, we manage the repository using the LWRP. The main difference is that the source and debuginfo repositories are not available, but if they're needed, add them using the `yum_repository` LWRP in your own cookbook(s).

### ius
Installs the [IUS Community repositories](http://iuscommunity.org/Repos) via RPM. Uses the `node['yum']['ius_release']` attribute to select the right version of the package to install.

The IUS repository requires EPEL, and includes `yum::epel` as a dependency.

### repoforge
Installs the [RepoForge repositories](http://repoforge.org/) via RPM. Uses the `node['yum']['repoforge_release']` attribute to select the right version of the package to install.

The RepoForge repository requires EPEL, and includes `yum::epel` as a dependency.

### remi
Install the [Les RPM de Remi - Repository](http://rpms.famillecollet.com/) with the `yum_key` and `yum_repository` resources from this cookbook are used to manage the remi repository.  Use the `yum['remi']` attributes (see above) to configure the key, url and download the GPG key for the repo. The defaults are detected by platform and should just work without modification in most use cases.


Resources/Providers
-------------------
### yum_key
This LWRP handles importing GPG keys for YUM repositories. Keys can be imported by the `url` parameter or placed in `/etc/pki/rpm-gpg/` by a recipe and then installed with the LWRP without passing the URL.

#### Actions
- :add: installs the GPG key into `/etc/pki/rpm-gpg/`
- :remove: removes the GPG key from `/etc/pki/rpm-gpg/`

#### Attribute Parameters
- key: name attribute. The name of the GPG key to install.
- url: if the key needs to be downloaded, the URL providing the download.

#### Example

``` ruby
# add the Zenoss GPG key
yum_key "RPM-GPG-KEY-zenoss" do
  url "http://dev.zenoss.com/yum/RPM-GPG-KEY-zenoss"
  action :add
end

# remove Zenoss GPG key
yum_key "RPM-GPG-KEY-zenoss" do
  action :remove
end
```

### yum_repository
This LWRP provides an easy way to manage additional YUM repositories. GPG keys can be managed with the `yum_key` LWRP.  The LWRP automatically updates the package management cache upon the first run, when a new repo is added.

#### Actions
- :create: creates a repository file and builds the repository listing
- :add: runs create action if repository file is missing (default)
- :remove: removes the repository file
- :update: updates the repository

#### Attribute Parameters
- repo_name: name attribute. The name of the channel to discover
- description. The description of the repository
- url: The URL providing the packages, used for baseurl in the config
- mirrorlist: Set this as a string containing the URI to the
  mirrorlist, start with "http://", "ftp://", "file://"; use "file://"
  if the mirrorlist is a text file on the system.
- key: Optional, the name of the GPG key file installed by the `key`
  LWRP.
- enabled: Default is `1`, set to `0` if the repository is disabled.
- type: Optional, alternate type of repository
- failovermethod: Optional, failovermethod
- bootstrapurl: Optional, bootstrapurl
- make_cache: Optional, Default is `true`, if `false` then `yum -q
  makecache` will not be ran
- metadata_expire: Optional, Default is nil (or not applied)
- type: Optional, Default is nil (or not applied)

*Note*: When using both url (to set baseurl) and mirrorlist, it is probably a good idea to also install the fastestmirror plugin, and use failovermethod "priority".

#### Example
``` ruby
# add the Zenoss repository
yum_repository "zenoss" do
  repo_name "zenoss"
  description "Zenoss Stable repo"
  url "http://dev.zenoss.com/yum/stable/"
  key "RPM-GPG-KEY-zenoss"
  action :add
end

# remove Zenoss repo
yum_repository "zenoss" do
  action :remove
end
```


Usage
-----
Put `recipe[yum::yum]` in the run list to ensure yum is configured correctly for your environment within your Chef run.

Use the `yum::epel` recipe to enable EPEL, or the `yum::ius` recipe to enable IUS, or the `yum::repoforge` recipe to enable RepoForge, or the `yum::remi` recipe to enable remi per __Recipes__ section above.

You can manage GPG keys either with cookbook_file in a recipe if you want to package it with a cookbook or use the `url` parameter of the `key` LWRP.


License & Authors
-----------------
- Author:: Eric G. Wolfe
- Author:: Matt Ray (<matt@opscode.com>)
- Author:: Joshua Timberman (<joshua@opscode.com>)

```text
Copyright:: 2010 Tippr Inc.
Copyright:: 2011 Eric G. Wolfe
Copyright:: 2011-2012 Opscode, Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```
