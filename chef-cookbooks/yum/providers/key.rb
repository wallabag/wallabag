#
# Cookbook Name:: yum
# Provider:: key
#
# Copyright 2010, Tippr Inc.
# Copyright 2011, Opscode, Inc.
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

def whyrun_supported?
  true
end

action :add do
  unless ::File.exists?("/etc/pki/rpm-gpg/#{new_resource.key}")
    Chef::Log.info "Adding #{new_resource.key} GPG key to /etc/pki/rpm-gpg/"

    if node['platform_version'].to_i <= 5
      package "gnupg"
    elsif node['platform_version'].to_i >= 6
      package "gnupg2"
    end

    execute "import-rpm-gpg-key-#{new_resource.key}" do
      command "rpm --import /etc/pki/rpm-gpg/#{new_resource.key}"
      action :nothing
      not_if <<-EOH
    function packagenames_for_keyfile() {
      local filename="$1"
      gpg \
        --with-fingerprint \
        --with-colons \
        --fixed-list-mode \
        "$filename" \
      | gawk -F: '/^pub/ { print tolower(sprintf("gpg-pubkey-%s-%x\\n", substr($5, length($5)-8+1), $6)) }'
    }

    for pkgname in $(packagenames_for_keyfile "/etc/pki/rpm-gpg/#{new_resource.key}"); do
      if [[ $pkgname ]] && ! rpm -q $pkgname ; then
        exit 1;
      fi;
    done

    exit 0
    EOH
    end

    #download the file if necessary
    unless new_resource.url.nil?
      remote_file "/etc/pki/rpm-gpg/#{new_resource.key}" do
        source new_resource.url
        mode "0644"
        notifies :run, "execute[import-rpm-gpg-key-#{new_resource.key}]", :immediately
      end
    end

  end
end

action :remove do
  if ::File.exists?("/etc/pki/rpm-gpg/#{new_resource.key}")
    Chef::Log.info "Removing #{new_resource.key} key from /etc/pki/rpm-gpg/"
    file "/etc/pki/rpm-gpg/#{new_resource.key}" do
      action :delete
    end
    new_resource.updated_by_last_action(true)
  end
end
