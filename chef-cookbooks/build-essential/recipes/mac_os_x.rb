#
# Cookbook Name:: build-essential
# Recipe:: mac_os_x
#
# Copyright 2008-2013, Opscode, Inc.
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


result = Mixlib::ShellOut.new("pkgutil --pkgs").run_command
osx_gcc_installer_installed = result.stdout.split("\n").include?("com.apple.pkg.gcc4.2Leo")
developer_tools_cli_installed = result.stdout.split("\n").include?("com.apple.pkg.DeveloperToolsCLI")
pkg_filename = ::File.basename(node['build_essential']['osx']['gcc_installer_url'])
pkg_path = "#{Chef::Config[:file_cache_path]}/#{pkg_filename}"

r = remote_file pkg_path do
  source node['build_essential']['osx']['gcc_installer_url']
  checksum node['build_essential']['osx']['gcc_installer_checksum']
  action( node['build_essential']['compiletime'] ? :nothing : :create )
  not_if { osx_gcc_installer_installed or developer_tools_cli_installed  }
end
r.run_action(:create) if node['build_essential']['compiletime']

r = execute "sudo installer -pkg \"#{pkg_path}\" -target /" do
  action( node['build_essential']['compiletime'] ? :nothing : :run )
  not_if { osx_gcc_installer_installed or developer_tools_cli_installed  }
end
r.run_action(:run) if node['build_essential']['compiletime']
