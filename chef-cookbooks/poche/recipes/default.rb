# begin with installing nginx
include_recipe 'nginx'

package "php5-cli" do
  action :install
end

package "php5-sqlite" do
  action :install
end

package "php5-tidy" do
  action :install
end

package "php5-curl" do
  action :install
end

# create nginx server block file
template "#{node['nginx']['dir']}/sites-available/poche.local" do
  source "poche.local.erb"
  owner "root"
  group "root"
  mode 00755
end

# disable the default site
nginx_site 'default' do
  enable false
  notifies :reload, 'service[nginx]'
end
# enable the server block we just created
nginx_site 'poche.local' do
  enable true
  notifies :reload, 'service[nginx]'
end

