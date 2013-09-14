#
# Cookbook Name:: yum_test
# Recipe:: default
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

module Helpers
  module YumTest
    require 'chef/mixin/shell_out'
    include Chef::Mixin::ShellOut
    include MiniTest::Chef::Assertions
    include MiniTest::Chef::Context
    include MiniTest::Chef::Resources

    # This isn't the most efficient thing in the world, but it works
    # reliably as yum will only return the repos that are actually
    # enabled. It would probably be more efficient, since we're at the
    # end of the successful run, to cache the output to a file and
    # inspect its contents.
    def repo_enabled(repo)
      shell_out("yum repolist enabled --verbose | grep Repo-id").stdout.include?(repo)
    end
  end
end
