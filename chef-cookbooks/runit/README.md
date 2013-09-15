runit Cookbook
==============
Installs runit and provides the `runit_service` service resource for managing processes (services) under runit.

This cookbook does not use runit to replace system init, nor are ther plans to do so.

For more information about runit:

- http://smarden.org/runit/


Requirements
------------
### Platforms
- Debian/Ubuntu
- Gentoo
- RHEL


Attributes
----------
See `attributes/default.rb` for defaults generated per platform.

- `node['runit']['sv_bin']` - Full path to the `sv` binary.
- `node['runit']['chpst_bin']` - Full path to the `chpst` binary.
- `node['runit']['service_dir']` - Full path to the default "services" directory where enabled services are linked.
- `node['runit']['sv_dir']` - Full path to the directory where service lives, which gets linked to `service_dir`.
- `node['runit']['lsb_init_dir']` - Full path to the directory where the LSB-compliant init script interface will be created.
- `node['runit']['start']` - Command to start the runsvdir service
- `node['runit']['stop]` - Command to stop the runsvdir service
- `node['runit']['reload']` - Command to reload the runsvdir service

### Optional Attributes for RHEL systems

- `node['runit']['use_package_from_yum']` - If `true`, attempts to install
  runit without building an RPM first. This is for users who already have
  the package in their own Yum repository.


Recipes
-------
### default
The default recipe installs runit and starts `runsvdir` to supervise the services in runit's service directory (e.g., `/etc/service`).

On RHEL family systems, it will build the runit RPM using [Ian Meyer's runit RPM SPEC](https://github.com/imeyer/runit-rpm) unless the attribute `node['runit']['use_package_from_yum']` is set to `true`. In which case it will try and install runit through the normal package installation mechanism.

On Debian family systems, the runit packages are maintained by the runit author, Gerrit Pape, and the recipe will use that for installation.

On Gentoo, the runit ebuild package is installed.


Resource/Provider
-----------------
This cookbook has a resource, `runit_service`, for managing services under runit. This service subclasses the Chef `service` resource.

**This resource replaces the runit_service definition. See the CHANGELOG.md file in this cookbook for breaking change information and any actions you may need to take to update cookbooks using runit_service.**

### Actions
- **enable** - enables the service, creating the required run scripts and symlinks. This is the default action.
- **start** - starts the service with `sv start`
- **stop** - stops the service with `sv stop`
- **disable** - stops the service with `sv down` and removes the service symlink
- **restart** - restarts the service with `sv restart`
- **reload** - reloads the service with `sv force-reload`
- **once** - starts the service with `sv once`.
- **hup** - sends the `HUP` signal to the service with `sv hup`
- **cont** - sends the `CONT` signal to the service
- **term** - sends the `TERM` signal to the service
- **kill** - sends the `KILL` signal to the service
- **up** - starts the service with `sv up`
- **down** - downs the service with `sv down`
- **usr1** - sends the `USR1` signal to the service with `sv 1`
- **usr2** - sends the `USR2` signal to the service with `sv 2`

Service management actions are taken with runit's "`sv`" program.

Read the `sv(8)` [man page](http://smarden.org/runit/sv.8.html) for more information on the `sv` program.

### Parameter Attributes

The first three parameters, `sv_dir`, `service_dir`, and `sv_bin` will attempt to use the corresponding node attributes, and fall back to hardcoded default values that match the settings used on Debian platform systems.

Many of these parameters are only used in the `:enable` action.

- **sv_dir** - The base "service directory" for the services managed by
   the resource. By default, this will attempt to use the
   `node['runit']['sv_dir']` attribute, and falls back to `/etc/sv`.
- **service_dir** - The directory where services are symlinked to be
   supervised by `runsvdir`. By default, this will attempt to use the
   `node['runit']['service_dir']` attribute, and falls back to
   `/etc/service`.
- **lsb_init_dir** - The directory where an LSB-compliant init script
   interface will be created. By default, this will attempt to use the
   `node['runit']['lsb_init_dir']` attribute, and falls back to
   `/etc/init.d`.
- **sv_bin** - The path to the `sv` program binary. This will attempt
    to use the `node['runit']['sv_bin']` attribute, and falls back to
    `/usr/bin/sv`.
- **service_name** - *Name attribute*. The name of the service. This
   will be used in the directory of the managed service in the
   `sv_dir` and `service_dir`.
- **sv_templates** - If true, the `:enable` action will create the
    service directory with the appropriate templates. Default is
    `true`. Set this to `false` if the service has a package that
    provides its own service directory. See __Usage__ examples.
- **options** - Options passed as variables to templates, for
   compatibility with legacy runit service definition. Default is an
   empty hash.
- **env** - A hash of environment variables with their values as content
   used in the service's `env` directory. Default is an empty hash.
- **log** - Whether to start the service's logger with svlogd, requires
   a template `sv-service_name-log-run.erb` to configure the log's run
   script. Default is true.
- **default_logger** - Whether a default `log/run` script should be set
   up. If true, the default content of the run script will use
   `svlogd` to write logs to `/var/log/service_name`. Default is false.
- **log_size** - The maximum size a log file can grow to before it is
  automatically rotated.  See svlogd(8) for the default value.
- **log_num** - The maximum number of log files that will be retained
  after rotation.  See svlogd(8) for the default value.
- **log_min** - The minimum number of log files that will be retained
  after rotation (if svlogd cannot create a new file and the minimum
  has not been reached, it will block).  Default is no minimum.
- **log_timeout** - The maximum age a log file can get to before it is
  automatically rotated, whether it has reached `log_size` or not.
  Default is no timeout.
- **log_processor** - A string containing a path to a program that
  rotated log files will be fed through.  See the **PROCESSOR** section
  of svlogd(8) for details.  Default is no processor.
- **log_socket** - An string containing an IP:port pair identifying a UDP
   socket that log lines will be copied to.  Default is none.
- **log_prefix** - A string that will be prepended to each line as it
  is logged.  Default is no prefix.
- **log_config_append** - A string containing optional additional lines to add
  to the log service configuration.  See svlogd(8) for more details.
- **cookbook** - A cookbook where templates are located instead of
   where the resource is used. Applies for all the templates in the
   `enable` action.
- **finish** - whether the service has a finish script, requires a
   template `sv-service_name-finish.erb`
- **control** - An array of signals to customize control of the service,
   see [runsv man page](http://smarden.org/runit/runsv.8.html) on how
   to use this. This requires that each template be created with the
   name `sv-service_name-signal.erb`.
- **owner** - user that should own the templates created to enable the
   service
- **group** - group that should own the templates created to enable the
   service
- **run_template_name** - alternate filename of the run run script to
   use replacing `service_name`.
- **log_template_name** - alternate filename of the log run script to
   use replacing `service_name`.
- **finish_script_template_name** - alternate filename of the finish
   script to use, replacing `service_name`.
- **control_template_names** - a hash of control signals (see *control*
   above) and their alternate template name(s) replacing
   `service_name`.
- **status_command** - The command used to check the status of the
   service to see if it is enabled/running (if it's running, it's
   enabled). This hardcodes the location of the sv program to
   `/usr/bin/sv` due to the aforementioned cookbook load order.
- **restart_on_update** - Whether the service should be restarted when
    the run script is updated. Defaults to `true`. Set to `false` if
    the service shouldn't be restarted when the run script is updated.

Unlike previous versions of the cookbook using the `runit_service` definition, the `runit_service` resource can be notified. See __Usage__ examples below.


Usage
-----
To get runit installed on supported platforms, use `recipe[runit]`. Once it is installed, use the `runit_service` resource to set up services to be managed by runit.

In order to use the `runit_service` resource in your cookbook(s), each service managed will also need to have `sv-service_name-run.erb` and `sv-service_name-log-run.erb` templates created. If the `log` parameter is false, the log run script isn't created. If the `log` parameter is true, and `default_logger` is also true, the log run
script will be created with the default content:

```bash
#!/bin/sh
exec svlogd -tt /var/log/service_name
```

### Examples
These are example use cases of the `runit_service` resource described above. There are others in the `runit_test` cookbook that is included in the [git repository](https://github.com/opscode-cookbooks/runit).

**Default Example**

This example uses all the defaults in the `:enable` action to set up the service.

We'll set up `chef-client` to run as a service under runit, such as is done in the `chef-client` cookbook. This example will be more simple than in that cookbook. First, create the required run template, `chef-client/templates/default/sv-chef-client-run.erb`.

```bash
#!/bin/sh
exec 2>&1
exec /usr/bin/env chef-client -i 1800 -s 30
```

Then create the required log/run template, `chef-client/templates/default/sv-chef-client-log-run.erb`.

```bash
#!/bin/sh
exec svlogd -tt ./main
```

__Note__ This will cause output of the running process to go to `/etc/sv/chef-client/log/main/current`. Some people may not like this, see the following example. This is preserved for compatibility reasons.

Finally, set up the service in the recipe with:

```ruby
runit_service "chef-client"
```

**Default Logger Example**

To use a default logger with svlogd which will log to `/var/log/chef-client/current`, instead, use the `default_logger` option.

```ruby
runit_service "chef-client" do
  default_logger true
end
```

**No Log Service**

If there isn't an appendant log service, set `log` to false, and the log/run script won't be created.

```ruby
runit_service "no-svlog" do
  log false
end
```

**Finish Script**

To create a service that has a finish script in its service directory, set the `finish` parameter to `true`, and create a `sv-finisher-finish.erb` template.

```ruby
runit_service "finisher" do
  finish true
end
```

This will create `/etc/sv/finisher/finish`.

**Alternate service directory**

If the service directory for the managed service isn't the `sv_dir` (`/etc/sv`), then specify it:

```ruby
runit_service "custom_service" do
  sv_dir "/etc/custom_service/runit"
end
```

**No Service Directory**

If the service to manage has a package that provides its service directory, such as `git-daemon` on Debian systems, set `sv_templates` to false.

```ruby
package "git-daemon-run"

runit_service "git-daemon" do
  sv_templates false
end
```

This will create the service symlink in `/etc/service`, but it will not manage any templates in the service directory.

**User Controlled Services**

To set up services controlled by a non-privileged user, we follow the recommended configuration in the [runit documentation](http://smarden.org/runit/faq.html#user) (Is it possible to allow a user other than root to control a service?).

Suppose the user's name is floyd, and floyd wants to run floyds-app. Assuming that the floyd user and group are already managed with Chef, create a `runsvdir-floyd` runit_service.

```ruby
runit_service "runsvdir-floyd"
```

Create the `sv-runsvdir-floyd-log-run.erb` template, or add `log false`. Also create the `sv-runsvdir-floyd-run.erb` with the following content:

```bash
#!/bin/sh
exec 2>&1
exec chpst -ufloyd runsvdir /home/floyd/service
```

Next, create the `runit_service` resource for floyd's app:

```ruby
runit_service "floyds-app" do
  sv_dir "/home/floyd/sv"
  service_dir "/home/floyd/service"
  owner "floyd"
  group "floyd"
end
```

And now floyd can manage the service with sv:

```text
$ id
uid=1000(floyd) gid=1001(floyd) groups=1001(floyd)
$ sv stop /home/floyd/service/floyds-app/
ok: down: /home/floyd/service/floyds-app/: 0s, normally up
$ sv start /home/floyd/service/floyds-app/
ok: run: /home/floyd/service/floyds-app/: (pid 5287) 0s
$ sv status /home/floyd/service/floyds-app/
run: /home/floyd/service/floyds-app/: (pid 5287) 13s; run: log: (pid 4691) 726s
```

**Options**

Next, let's set up memcached under runit with some additional options using the `options` parameter. First, the `memcached/templates/default/sv-memcached-run.erb` template:

```bash
#!/bin/sh
exec 2>&1
exec chpst -u <%= @options[:user] %> /usr/bin/memcached -v -m <%= @options[:memory] %> -p <%= @options[:port] %>
```

Note that the script uses `chpst` (which comes with runit) to set the user option, then starts memcached on the specified memory and port (see below).

The log/run template, `memcached/templates/default/sv-memcached-log-run.erb`:

```bash
#!/bin/sh
exec svlogd -tt ./main
```

Finally, the `runit_service` in our recipe:

```ruby
runit_service "memcached" do
  options({
    :memory => node[:memcached][:memory],
    :port => node[:memcached][:port],
    :user => node[:memcached][:user]}.merge(params)
  )
end
```

This is where the user, port and memory options used in the run template are used.

**Notifying Runit Services**

In previous versions of this cookbook where the definition was used, it created a `service` resource that could be notified. With the `runit_service` resource, recipes need to use the full resource name.

For example:

```ruby
runit_service "my-service"

template "/etc/my-service.conf" do
  notifies :restart, "runit_service[my-service]"
end
```

Because the resource implements actions for various commands that `sv` can send to the service, any of those actions could be used for notification. For example, `chef-client` supports triggering a Chef run with a USR1 signal.

```ruby
template "/tmp/chef-notifier" do
  notifies :usr1, "runit_service[chef-client]"
end
```

For older implementations of services that used `runit_service` as a definition, but may support alternate service styles, use a conditional, such as based on an attribute:

```ruby
service_to_notify = case node['nginx']['init_style']
                    when "runit"
                      "runit_service[nginx]"
                    else
                      "service[nginx]"
                    end

template "/etc/nginx/nginx.conf" do
  notifies :restart, service_to_notify
end
```

**More Examples**

For more examples, see the `runit_test` cookbook's `service` recipe in the [git repository](https://github.com/opscode-cookbooks/runit).


License & Authors
-----------------
- Author:: Adam Jacob <adam@opscode.com>
- Author:: Joshua Timberman <joshua@opscode.com>

```text
Copyright:: 2008-2013, Opscode, Inc

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
