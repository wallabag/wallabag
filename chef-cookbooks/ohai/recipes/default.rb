#
# Cookbook Name:: ohai
# Recipe:: default
#
# Copyright 2011, Opscode, Inc
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#

reload_ohai = false
# Add plugin_path from node attributes if missing, and ensure a reload of
# ohai in that case
unless Ohai::Config[:plugin_path].include?(node['ohai']['plugin_path'])
  Ohai::Config[:plugin_path] = [node['ohai']['plugin_path'], Ohai::Config[:plugin_path]].flatten.compact
  reload_ohai ||= true
end
Chef::Log.info("ohai plugins will be at: #{node['ohai']['plugin_path']}")

# This is done during the compile phase so new plugins can be used in
# resources later in the run.
node['ohai']['plugins'].each_pair do |source_cookbook, path|

  rd = remote_directory node['ohai']['plugin_path'] do
    cookbook source_cookbook
    source path
    mode '0755' unless platform_family?('windows')
    recursive true
    purge false
    action :nothing
  end

  rd.run_action(:create)
  reload_ohai ||= rd.updated?
end

resource = ohai 'custom_plugins' do
  action :nothing
end

# Reload ohai if the client's plugin_path did not contain
# node['ohai']['plugin_path'], or new plugins were loaded
if reload_ohai
  resource.run_action(:reload)
end
