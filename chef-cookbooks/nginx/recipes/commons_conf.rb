#
# Cookbook Name:: nginx
# Recipe:: common/conf
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

template "nginx.conf" do
  path "#{node['nginx']['dir']}/nginx.conf"
  source "nginx.conf.erb"
  owner "root"
  group "root"
  mode 00644
  notifies :reload, 'service[nginx]'
end

template "#{node['nginx']['dir']}/sites-available/default" do
  source "default-site.erb"
  owner "root"
  group "root"
  mode 00644
  notifies :reload, 'service[nginx]'
end

nginx_site 'default' do
  enable node['nginx']['default_site_enabled']
end
