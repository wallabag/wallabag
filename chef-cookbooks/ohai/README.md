ohai Cookbook
=============
Creates a configured plugin path for distributing custom Ohai plugins, and reloads them via Ohai within the context of a Chef Client run during the compile phase (if needed).


Attributes
----------
- `node['ohai']['plugin_path']` - location to drop off plugins directory, default is `/etc/chef/ohai_plugins`. This is not FHS-compliant, an FHS location would be something like `/var/lib/ohai/plugins`, or `/var/lib/chef/ohai_plugins` or similar.

    Neither an FHS location or the default value of this attribute are in the default Ohai plugin path. Set the Ohai plugin path with the config setting "`Ohai::Config[:plugin_path]`" in the Chef config file (the `chef-client::config` recipe does this automatically for you!). The attribute is not set to the default plugin path that Ohai ships with because we don't want to risk destroying existing essential plugins for Ohai.

- `node['ohai']['plugins']` - sources of plugins, defaults to the `files/default/plugins` directory of this cookbook. You can add additional cookbooks by adding the name of the cookbook as a key and the path of the files directory as the value. You have to make sure that you don't have any file conflicts between multiple cookbooks. The last one to write wins.


Usage
-----
Put the recipe `ohai` at the start of the node's run list to make sure that custom plugins are loaded early on in the Chef run and data is available for later recipes.

The execution of the custom plugins occurs within the recipe during the compile phase, so you can write new plugins and use the data they return in your Chef recipes.

For information on how to write custom plugins for Ohai, please see the Chef wiki pages.

http://wiki.opscode.com/display/chef/Writing+Ohai+Plugins

*PLEASE NOTE* - This recipe reloads the Ohai plugins a 2nd time during the Chef run if:

* The "`Ohai::Config[:plugin_path]`" config setting has *NOT* been properly set in the Chef config file
- The "`Ohai::Config[:plugin_path]`" config setting has been properly set in the Chef config file and there are updated plugins dropped off at "`node['ohai']['plugin_path']`".


Example
-------
For an example implementation, inspect the ohai_plugin.rb recipe in the nginx community cookbook.


License & Authors
-----------------
- Author:: Joshua Timberman (<joshua@opscode.com>)
- Author:: Seth Chisamore (<schisamo@opscode.com>)

```text
Copyright:: 2010-2011, Opscode, Inc

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
