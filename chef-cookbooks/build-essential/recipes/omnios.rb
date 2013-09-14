#
# Cookbook Name:: build-essential
# Recipe:: omnios
#
# Copyright 2013, Opscode, Inc.
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

%w{
  developer/gcc47
  developer/object-file
  developer/linker
  developer/library/lint
  developer/build/gnu-make
  system/header
  system/library/math/header-math
}.each do |pkg|

  r = package pkg do
    action( node['build_essential']['compiletime'] ? :nothing : :install )
  end
  r.run_action(:install) if node['build_essential']['compiletime']

  # Per OmniOS documentation, the gcc bin dir isn't in the default
  # $PATH, so add it to the running process environment
  # http://omnios.omniti.com/wiki.php/DevEnv
  ENV['PATH'] = "#{ENV['PATH']}:/opt/gcc-4.7.2/bin"
end
