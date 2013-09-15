#
# Cookbook Name:: yum
# Attributes:: elrepo
#
# Copyright 2013, Opscode, Inc.
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

default['yum']['elrepo']['url'] = "http://elrepo.org/mirrors-elrepo.el#{node['platform_version'].to_i}"
default['yum']['elrepo']['key'] = "RPM-GPG-KEY-elrepo.org"
default['yum']['elrepo']['key_url'] = "http://elrepo.org/#{node['yum']['elrepo']['key']}"
default['yum']['elrepo']['includepkgs'] = nil
default['yum']['elrepo']['exclude'] = nil
