#
# Author:: Eric Edgar (<rocketman110@gmail.com>)
# Cookbook Name:: yum
# Recipe:: repoforge
#
# Copyright:: Copyright (c) 2012-2013 Opscode, Inc.
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

include_recipe "yum::epel"

major = platform?("amazon") ? 6 : node['platform_version'].to_i
arch = (node['kernel']['machine'] == "i686" && major == 5) ? "i386" : node['kernel']['machine']
repoforge = node['yum']['repoforge_release']

remote_file "#{Chef::Config[:file_cache_path]}/rpmforge-release-#{repoforge}.el#{major}.rf.#{arch}.rpm" do
  source "http://pkgs.repoforge.org/rpmforge-release/rpmforge-release-#{repoforge}.el#{major}.rf.#{arch}.rpm"
  not_if "rpm -qa | grep -q '^rpmforge-release-#{repoforge}'"
  notifies :install, "rpm_package[rpmforge-release]", :immediately
end

rpm_package "rpmforge-release" do
  source "#{Chef::Config[:file_cache_path]}/rpmforge-release-#{repoforge}.el#{major}.rf.#{arch}.rpm"
  only_if { ::File.exists?("#{Chef::Config[:file_cache_path]}/rpmforge-release-#{repoforge}.el#{major}.rf.#{arch}.rpm") }
  action :install
end

file "repoforge-release-cleanup" do
  path "#{Chef::Config[:file_cache_path]}/rpmforge-release-#{repoforge}.el#{major}.rf.#{arch}.rpm"
  action :delete
end
