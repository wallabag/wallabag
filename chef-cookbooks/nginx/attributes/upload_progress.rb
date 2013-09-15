#
# Cookbook Name:: nginx
# Attributes:: upload_progress
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

default['nginx']['upload_progress']['url']      = "https://github.com/masterzen/nginx-upload-progress-module/tarball/v0.8.4"
default['nginx']['upload_progress']['checksum'] = "7b3f81d30cd3e8af2c343b73d8518d2373b95aeb3d0243790991873a3d91d0c5"
