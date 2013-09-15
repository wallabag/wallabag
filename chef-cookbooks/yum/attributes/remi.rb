#
# Cookbook Name:: yum
# Attributes:: remi
#
# Copyright 2011, Opscode, Inc.
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

case node['platform']
when "fedora"
  default['yum']['remi']['url'] = "http://rpms.famillecollet.com/fedora/#{node['platform_version'].to_i}/remi/mirror"
else
  default['yum']['remi']['url'] = "http://rpms.famillecollet.com/enterprise/#{node['platform_version'].to_i}/remi/mirror"
end

default['yum']['remi']['key'] = "RPM-GPG-KEY-remi"
default['yum']['remi']['key_url'] = "http://rpms.famillecollet.com/#{node['yum']['remi']['key']}"
default['yum']['remi']['includepkgs'] = nil
default['yum']['remi']['exclude'] = nil
