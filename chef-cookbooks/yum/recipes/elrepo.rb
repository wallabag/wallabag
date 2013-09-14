#
# Cookbook Name:: yum
# Recipe:: elrepo
#
# Copyright:: Copyright (c) 2013 Opscode, Inc.
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

yum_key node['yum']['elrepo']['key'] do
  url  node['yum']['elrepo']['key_url']
  action :add
end

yum_repository "elrepo" do
  description "ELRepo.org Community Enterprise Linux Extras Repository"
  key node['yum']['elrepo']['key']
  mirrorlist node['yum']['elrepo']['url']
  includepkgs node['yum']['elrepo']['includepkgs']
  exclude node['yum']['elrepo']['exclude']
  action :create
end
