#
# Cookbook Name:: nginx
# Attributes:: naxsi
#
# Author:: Artiom Lunev (<artiom.lunev@gmail.com>)
#
# Copyright 2012, Artiom Lunev
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

default['nginx']['naxsi']['version']  = "0.49"
default['nginx']['naxsi']['url']      = "http://naxsi.googlecode.com/files/naxsi-core-#{node['nginx']['naxsi']['version']}.tgz"
default['nginx']['naxsi']['checksum'] = "bb0b63713d5a9446401d5acd1ef0882b9c2a0fc506502bbd88bc4663d273e9c7"
