#
# Cookbook Name:: nginx
# Recipe:: http_geoip_module
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

package 'libtool'

country_dat          = "#{node['nginx']['geoip']['path']}/GeoIP.dat"
country_src_filename = ::File.basename(node['nginx']['geoip']['country_dat_url'])
country_src_filepath = "#{Chef::Config['file_cache_path']}/#{country_src_filename}"
city_dat             = nil
city_src_filename    = ::File.basename(node['nginx']['geoip']['city_dat_url'])
city_src_filepath    = "#{Chef::Config['file_cache_path']}/#{city_src_filename}"
geolib_filename      = ::File.basename(node['nginx']['geoip']['lib_url'])
geolib_filepath      = "#{Chef::Config['file_cache_path']}/#{geolib_filename}"

remote_file geolib_filepath do
  source node['nginx']['geoip']['lib_url']
  checksum node['nginx']['geoip']['lib_checksum']
  owner "root"
  group "root"
  mode 00644
end

bash "extract_geolib" do
  cwd ::File.dirname(geolib_filepath)
  code <<-EOH
    tar xzvf #{geolib_filepath} -C #{::File.dirname(geolib_filepath)}
    cd GeoIP-#{node['nginx']['geoip']['lib_version']}
    which libtoolize && libtoolize -f
    ./configure
    make && make install
  EOH

  creates "/usr/local/lib/libGeoIP.so.#{node['nginx']['geoip']['lib_version']}"
  subscribes :run, "remote_file[#{geolib_filepath}]"
end

directory node['nginx']['geoip']['path'] do
  owner "root"
  group "root"
  mode 00755
  recursive true
end

remote_file country_src_filepath do
  not_if do
    File.exists?(country_src_filepath) &&
    File.mtime(country_src_filepath) > Time.now - 86400
  end
  source node['nginx']['geoip']['country_dat_url']
  checksum node['nginx']['geoip']['country_dat_checksum']
  owner "root"
  group "root"
  mode 00644
end

bash "gunzip_geo_lite_country_dat" do
  code <<-EOH
    gunzip -c #{country_src_filepath} > #{country_dat}
  EOH
  creates country_dat
end

if node['nginx']['geoip']['enable_city']
  city_dat  = "#{node['nginx']['geoip']['path']}/GeoLiteCity.dat"

  remote_file city_src_filepath do
    not_if do
      File.exists?(city_src_filepath) &&
      File.mtime(city_src_filepath) > Time.now - 86400
    end
    source node['nginx']['geoip']['city_dat_url']
    checksum node['nginx']['geoip']['city_dat_checksum']
    owner "root"
    group "root"
    mode 00644
  end

  bash "gunzip_geo_lite_city_dat" do
    code <<-EOH
      gunzip -c #{city_src_filepath} > #{city_dat}
    EOH
    creates city_dat
  end
end

template "#{node['nginx']['dir']}/conf.d/http_geoip.conf" do
  source "modules/http_geoip.conf.erb"
  owner "root"
  group "root"
  mode 00644
  variables(
    :country_dat => country_dat,
    :city_dat => city_dat
  )
end

node.run_state['nginx_configure_flags'] =
  node.run_state['nginx_configure_flags'] | ["--with-http_geoip_module", "--with-ld-opt='-Wl,-R,/usr/local/lib -L /usr/local/lib'"]
