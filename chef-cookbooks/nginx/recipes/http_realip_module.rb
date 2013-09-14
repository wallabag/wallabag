#
# Cookbook Name:: nginx
# Recipe:: http_realip_module
#
# Author:: Jamie Winsor (<jamie@vialstudios.com>)
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
#

# Documentation: http://wiki.nginx.org/HttpRealIpModule

# Currently only accepts X-Forwarded-For or X-Real-IP
node.default['nginx']['realip']['header']    = "X-Forwarded-For"
node.default['nginx']['realip']['addresses'] = ["127.0.0.1"]
node.default['nginx']['realip']['real_ip_recursive'] = "off"

template "#{node['nginx']['dir']}/conf.d/http_realip.conf" do
  source "modules/http_realip.conf.erb"
  owner "root"
  group "root"
  mode 00644
  variables(
    :addresses => node['nginx']['realip']['addresses'],
    :header => node['nginx']['realip']['header'],
    :real_ip_recursive => node['nginx']['realip']['real_ip_recursive']
  )

  notifies :reload, "service[nginx]"
end

node.run_state['nginx_configure_flags'] =
  node.run_state['nginx_configure_flags'] | ["--with-http_realip_module"]
