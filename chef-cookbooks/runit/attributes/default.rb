#
# Cookbook Name:: runit
# Attribute File:: sv_bin
#
# Copyright 2008-2009, Opscode, Inc.
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

case node["platform_family"]
when "debian"

  default["runit"]["sv_bin"] = "/usr/bin/sv"
  default["runit"]["chpst_bin"] = "/usr/bin/chpst"
  default["runit"]["service_dir"] = "/etc/service"
  default["runit"]["sv_dir"] = "/etc/sv"
  default["runit"]["lsb_init_dir"] = "/etc/init.d"
  default["runit"]["executable"] = "/sbin/runit"

  if node["platform"] == "debian"

    default["runit"]["start"] = "runsvdir-start"
    default["runit"]["stop"] = ""
    default["runit"]["reload"] = ""

  elsif node["platform"] == "ubuntu"

    default["runit"]["start"] = "start runsvdir"
    default["runit"]["stop"] = "stop runsvdir"
    default["runit"]["reload"] = "reload runsvdir"

  end

when "rhel"

  default["runit"]["sv_bin"] = "/sbin/sv"
  default["runit"]["chpst_bin"] = "/sbin/chpst"
  default["runit"]["service_dir"] = "/etc/service"
  default["runit"]["sv_dir"] = "/etc/sv"
  default["runit"]["lsb_init_dir"] = "/etc/init.d"
  default["runit"]["executable"] = "/sbin/runit"
  default["runit"]["use_package_from_yum"] = false

  default["runit"]["start"] = "/etc/init.d/runit-start start"
  default["runit"]["stop"] = "/etc/init.d/runit-start stop"
  default["runit"]["reload"] = "/etc/init.d/runit-start reload"

when "gentoo"

  default["runit"]["sv_bin"] = "/usr/bin/sv"
  default["runit"]["chpst_bin"] = "/usr/bin/chpst"
  default["runit"]["service_dir"] = "/var/service"
  default["runit"]["sv_dir"] = "/etc/sv"
  default["runit"]["lsb_init_dir"] = "/etc/init.d"
  default["runit"]["executable"] = "/sbin/runit"
  default["runit"]["start"] = "/etc/init.d/runit-start start"
  default["runit"]["stop"] = "/etc/init.d/runit-start stop"
  default["runit"]["reload"] = "/etc/init.d/runit-start reload"

end
