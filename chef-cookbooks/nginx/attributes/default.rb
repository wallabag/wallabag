#
# Cookbook Name:: nginx
# Attributes:: default
#
# Author:: Adam Jacob (<adam@opscode.com>)
# Author:: Joshua Timberman (<joshua@opscode.com>)
#
# Copyright 2009-2011, Opscode, Inc.
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

# In order to update the version, the checksum attribute should be
# changed too. It is in the source.rb file, though we recommend
# overriding attributes by modifying a role, or the node itself.
# default['nginx']['source']['checksum']
default['nginx']['version'] = "1.2.9"
default['nginx']['package_name'] = "nginx"
default['nginx']['dir'] = "/etc/nginx"
default['nginx']['log_dir'] = "/var/log/nginx"
default['nginx']['binary'] = "/usr/sbin/nginx"

case node['platform']
when "debian","ubuntu"
  default['nginx']['user']       = "www-data"
  default['nginx']['init_style'] = "runit"
when "redhat","centos","scientific","amazon","oracle","fedora"
  default['nginx']['user']       = "nginx"
  default['nginx']['init_style'] = "init"
  default['nginx']['repo_source'] = "epel"
when "gentoo"
  default['nginx']['user']       = "nginx"
  default['nginx']['init_style'] = "init"
else
  default['nginx']['user']       = "www-data"
  default['nginx']['init_style'] = "init"
end

default['nginx']['upstart']['runlevels'] = '2345'
default['nginx']['upstart']['respawn_limit'] = nil
default['nginx']['upstart']['foreground'] = true

default['nginx']['group'] = node['nginx']['user']

default['nginx']['pid'] = "/var/run/nginx.pid"

default['nginx']['gzip']              = "on"
default['nginx']['gzip_http_version'] = "1.0"
default['nginx']['gzip_comp_level']   = "2"
default['nginx']['gzip_proxied']      = "any"
default['nginx']['gzip_vary']         = "off"
default['nginx']['gzip_buffers']      = nil
default['nginx']['gzip_types']        = [
  "text/plain",
  "text/css",
  "application/x-javascript",
  "text/xml",
  "application/xml",
  "application/rss+xml",
  "application/atom+xml",
  "text/javascript",
  "application/javascript",
  "application/json",
  "text/mathml"
]

default['nginx']['keepalive']          = "on"
default['nginx']['keepalive_timeout']  = 65
default['nginx']['worker_processes']   = node['cpu'] && node['cpu']['total'] ? node['cpu']['total'] : 1
default['nginx']['worker_connections'] = 1024
default['nginx']['worker_rlimit_nofile'] = nil
default['nginx']['multi_accept']       = false
default['nginx']['event']              = nil
default['nginx']['server_tokens']      = nil
default['nginx']['server_names_hash_bucket_size'] = 64
default['nginx']['sendfile'] = 'on'

default['nginx']['disable_access_log'] = false
default['nginx']['install_method'] = 'package'
default['nginx']['default_site_enabled'] = true
default['nginx']['types_hash_max_size'] = 2048
default['nginx']['types_hash_bucket_size'] = 64

default['nginx']['proxy_read_timeout'] = nil
default['nginx']['client_body_buffer_size'] = nil
default['nginx']['client_max_body_size'] = nil
