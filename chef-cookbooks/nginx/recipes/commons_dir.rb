#
# Cookbook Name:: nginx
# Recipe:: common/dir
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

directory node['nginx']['dir'] do
  owner "root"
  group "root"
  mode 00755
  recursive true
end

directory node['nginx']['log_dir'] do
  mode 00755
  owner node['nginx']['user']
  action :create
  recursive true
end

directory File.dirname(node['nginx']['pid']) do
  owner "root"
  group "root"
  mode  00755
  recursive true
end

%w(sites-available sites-enabled conf.d).each do |leaf|
  directory File.join(node['nginx']['dir'], leaf) do
    owner "root"
    group "root"
    mode 00755
  end
end
