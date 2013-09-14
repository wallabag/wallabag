#
# Cookbook Name:: yum
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

require File.expand_path('../support/helpers', __FILE__)

describe "yum::test" do
  # helpers includes the repo_enabled method used to test that repos
  # are in fact enabled.
  include Helpers::YumTest

  describe "elrepo" do
    it "enables the elrepo repository" do
      assert(repo_enabled("elrepo"))
    end
  end

  describe "epel" do
    it "enables the epel repository" do
      assert(repo_enabled("epel"))
    end
  end

  describe "ius" do
    it "enables the ius repository" do
      assert(repo_enabled("ius"))
    end
  end

  describe "remi" do
    it "enables the remi repository" do
      assert(repo_enabled("remi"))
    end
  end

  describe "repoforge" do
    it "enables the repoforge repository" do
      assert(repo_enabled("rpmforge"))
    end
	end

  describe "cook-2121" do

    it 'doesnt update the zenos-add.repo file if it exists' do
      assert File.zero?('/etc/yum.repos.d/zenoss-add.repo')
    end

    it 'updates the zenoss-create file' do
      file('/etc/yum.repos.d/zenoss-create.repo').must_match %r[baseurl=http://dev.zenoss.com/yum/stable/]
    end
  end
end
