#
# Cookbook Name:: nginx
# Recipe:: default
# Author:: AJ Christensen <aj@junglist.gen.nz>
#
# Copyright 2008-2012, Opscode, Inc.
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

include_recipe 'nginx::ohai_plugin'

case node['nginx']['install_method']
when 'source'
  include_recipe 'nginx::source'
when 'package'
  case node['platform']
  when 'redhat','centos','scientific','amazon','oracle'
    if node['nginx']['repo_source'] == 'epel'
      include_recipe 'yum::epel'
    elsif node['nginx']['repo_source'] == 'nginx'
      include_recipe 'nginx::repo'
    elsif node['nginx']['repo_source'].nil?
      log "node['nginx']['repo_source'] was not set, no additional yum repositories will be installed." do
        level :debug
      end
    else
      raise ArgumentError, "Unknown value '#{node['nginx']['repo_source']}' was passed to the nginx cookbook."
    end
  end
  package node['nginx']['package_name']
  service 'nginx' do
    supports :status => true, :restart => true, :reload => true
    action :enable
  end
  include_recipe 'nginx::commons'
end

service 'nginx' do
  supports :status => true, :restart => true, :reload => true
  action :start
end
