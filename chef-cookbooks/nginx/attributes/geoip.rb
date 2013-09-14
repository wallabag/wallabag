#
# Cookbook Name:: nginx
# Attributes:: geoip
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

default['nginx']['geoip']['path']                 = "/srv/geoip"
default['nginx']['geoip']['enable_city']          = true
default['nginx']['geoip']['country_dat_url']      = "http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz"
default['nginx']['geoip']['country_dat_checksum'] = "40865af8f49b9898957cb9b81548676ecb2efd6073a0a437a7b2afcc594bf8fe"
default['nginx']['geoip']['city_dat_url']         = "http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz"
default['nginx']['geoip']['city_dat_checksum']    = "2db87dd2ed665833b71b5f330476ad850cf3b34001f101d581c8e470dba50a5f"
default['nginx']['geoip']['lib_version']          = "1.4.8"
default['nginx']['geoip']['lib_url']              = "http://geolite.maxmind.com/download/geoip/api/c/GeoIP-#{node['nginx']['geoip']['lib_version']}.tar.gz"
default['nginx']['geoip']['lib_checksum']         = "cf0f6b2bac1153e34d6ef55ee3851479b347d2b5c191fda8ff6a51fab5291ff4"
