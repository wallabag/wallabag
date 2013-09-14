if node.platform_family == "rhel"
  user = "apache"
  group = "apache"
  conf_dir = "/etc/php-fpm.d"
  conf_file = "/etc/php-fpm.conf"
  error_log = "/var/log/php-fpm/error.log"
  pid = "/var/run/php-fpm/php-fpm.pid"
else
  user = "www-data"
  group = "www-data"
  conf_dir = "/etc/php5/fpm"
  conf_file = "/etc/php5/fpm/php-fpm.conf"
  error_log = "/var/log/php5-fpm.log"
  pid ="/var/run/php5-fpm.pid"
end

default['php-fpm']['conf_dir'] = conf_dir
default['php-fpm']['conf_file'] = conf_file
default['php-fpm']['pid'] = pid
default['php-fpm']['error_log'] =  error_log
default['php-fpm']['log_level'] = "notice"

default['php-fpm']['pools'] = ["www","testpool"]

default['php-fpm']['pool']['www']['listen'] = "/var/run/php-fpm-www.sock"
default['php-fpm']['pool']['www']['allowed_clients'] = ["127.0.0.1"]
default['php-fpm']['pool']['www']['user'] = user
default['php-fpm']['pool']['www']['group'] = group
default['php-fpm']['pool']['www']['process_manager'] = "dynamic"
default['php-fpm']['pool']['www']['max_children'] = 50
default['php-fpm']['pool']['www']['start_servers'] = 5
default['php-fpm']['pool']['www']['min_spare_servers'] = 5
default['php-fpm']['pool']['www']['max_spare_servers'] = 35
default['php-fpm']['pool']['www']['max_requests'] = 500

default['php-fpm']['pool']['testpool']['listen'] = "127.0.0.1:9001"
default['php-fpm']['pool']['testpool']['allowed_clients'] = ["127.0.0.1"]
default['php-fpm']['pool']['testpool']['user'] = user
default['php-fpm']['pool']['testpool']['group'] = group
default['php-fpm']['pool']['testpool']['process_manager'] = "dynamic"
default['php-fpm']['pool']['testpool']['max_children'] = 50
default['php-fpm']['pool']['testpool']['start_servers'] = 5
default['php-fpm']['pool']['testpool']['min_spare_servers'] = 5
default['php-fpm']['pool']['testpool']['max_spare_servers'] = 35
default['php-fpm']['pool']['testpool']['max_requests'] = 500
