#
# Cookbook Name:: nginx
# Recipe:: http_echo_module
#
# Author:: Danial Pearce (<danial@cushycms.com>)
#
# Copyright 2012, CushyCMS
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

echo_src_filename = "echo-nginx-module-v#{node['nginx']['echo']['version']}.tar.gz"
echo_src_filepath = "#{Chef::Config['file_cache_path']}/#{echo_src_filename}"
echo_extract_path = "#{Chef::Config['file_cache_path']}/nginx_echo_module/#{node['nginx']['echo']['checksum']}"

remote_file echo_src_filepath do
  source   node['nginx']['echo']['url']
  checksum node['nginx']['echo']['checksum']
  owner    'root'
  group    'root'
  mode     00644
end

bash 'extract_http_echo_module' do
  cwd ::File.dirname(echo_src_filepath)
  code <<-EOH
    mkdir -p #{echo_extract_path}
    tar xzf #{echo_src_filename} -C #{echo_extract_path}
    mv #{echo_extract_path}/*/* #{echo_extract_path}/
  EOH

  not_if { ::File.exists?(echo_extract_path) }
end

node.run_state['nginx_configure_flags'] =
  node.run_state['nginx_configure_flags'] | ["--add-module=#{echo_extract_path}"]
