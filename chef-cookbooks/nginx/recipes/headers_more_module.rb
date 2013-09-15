#
# Cookbook Name:: nginx
# Recipe:: headers_more_module
#
# Author:: Lucas Jandrew (<ljandrew@riotgames.com>)
#
# Copyright 2012, Riot Games
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

tar_location = "#{Chef::Config['file_cache_path']}/headers_more.tar.gz"
module_location = "#{Chef::Config['file_cache_path']}/headers_more/#{node['nginx']['headers_more']['source_checksum']}"

remote_file tar_location do
  source node['nginx']['headers_more']['source_url']
  checksum node['nginx']['headers_more']['source_checksum']
  owner 'root'
  group 'root'
  mode 0644
end

directory module_location do
  owner "root"
  group "root"
  mode 0755
  recursive true
  action :create
end

bash "extract_headers_more" do
  cwd ::File.dirname(tar_location)
  user 'root'
  code <<-EOH
    tar -zxf #{tar_location} -C #{module_location}
    mv -f #{module_location}/agentz*/* #{module_location}
    rm -rf #{module_location}/agentz*
  EOH

  not_if { ::File.exists?("#{module_location}/config") }

end

node.run_state['nginx_configure_flags'] =
    node.run_state['nginx_configure_flags'] | ["--add-module=#{module_location}"]
