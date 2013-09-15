#
# Cookbook Name:: yum
# Attributes:: default
#
# Copyright 2011, Eric G. Wolfe
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

# Example: override.yum.exclude = "kernel* compat-glibc*"
default['yum']['exclude'] = Array.new
default['yum']['installonlypkgs'] = Array.new
default['yum']['ius_release'] = '1.0-11'
default['yum']['repoforge_release'] = '0.5.2-2'
default['yum']['proxy'] = ''
default['yum']['proxy_username'] = ''
default['yum']['proxy_password'] = ''
default['yum']['cachedir'] = '/var/cache/yum'
default['yum']['keepcache'] = 0
