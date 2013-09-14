#
# Cookbook Name:: nginx
# Attributes:: echo
#
# Author:: Danial Pearce (<github@tigris.id.au>)
#

default['nginx']['echo']['version']        = '0.40'
default['nginx']['echo']['url']            = "https://github.com/agentzh/echo-nginx-module/tarball/v#{node['nginx']['echo']['version']}"
default['nginx']['echo']['checksum']       = '26ae7f7381d52d6aa5021dfc39a1862fd081d580166343f671d0920ed239ab41'
