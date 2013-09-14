name              "nginx"
maintainer        "Opscode, Inc."
maintainer_email  "cookbooks@opscode.com"
license           "Apache 2.0"
description       "Installs and configures nginx"
version           "1.8.0"

recipe "nginx", "Installs nginx package and sets up configuration with Debian apache style with sites-enabled/sites-available"
recipe "nginx::source", "Installs nginx from source and sets up configuration with Debian apache style with sites-enabled/sites-available"

%w{ ubuntu debian centos redhat amazon scientific oracle fedora }.each do |os|
  supports os
end

%w{ build-essential yum apt runit }.each do |cb|
  depends cb
end

depends 'ohai', '>= 1.1.4'

%w{ bluepill }.each do |cb|
  suggests cb
end

attribute "nginx/dir",
  :display_name => "Nginx Directory",
  :description => "Location of nginx configuration files",
  :default => "/etc/nginx"

attribute "nginx/log_dir",
  :display_name => "Nginx Log Directory",
  :description => "Location for nginx logs",
  :default => "/var/log/nginx"

attribute "nginx/user",
  :display_name => "Nginx User",
  :description => "User nginx will run as",
  :default => "www-data"

attribute "nginx/binary",
  :display_name => "Nginx Binary",
  :description => "Location of the nginx server binary",
  :default => "/usr/sbin/nginx"

attribute "nginx/gzip",
  :display_name => "Nginx Gzip",
  :description => "Whether gzip is enabled",
  :default => "on"

attribute "nginx/gzip_http_version",
  :display_name => "Nginx Gzip HTTP Version",
  :description => "Version of HTTP Gzip",
  :default => "1.0"

attribute "nginx/gzip_comp_level",
  :display_name => "Nginx Gzip Compression Level",
  :description => "Amount of compression to use",
  :default => "2"

attribute "nginx/gzip_proxied",
  :display_name => "Nginx Gzip Proxied",
  :description => "Whether gzip is proxied",
  :default => "any"

attribute "nginx/gzip_types",
  :display_name => "Nginx Gzip Types",
  :description => "Supported MIME-types for gzip",
  :type => "array",
  :default => [ "text/plain", "text/css", "application/x-javascript", "text/xml", "application/xml", "application/xml+rss", "text/javascript", "application/javascript", "application/json" ]

attribute "nginx/keepalive",
  :display_name => "Nginx Keepalive",
  :description => "Whether to enable keepalive",
  :default => "on"

attribute "nginx/keepalive_timeout",
  :display_name => "Nginx Keepalive Timeout",
  :default => "65"

attribute "nginx/worker_processes",
  :display_name => "Nginx Worker Processes",
  :description => "Number of worker processes",
  :default => "1"

attribute "nginx/worker_connections",
  :display_name => "Nginx Worker Connections",
  :description => "Number of connections per worker",
  :default => "1024"

attribute "nginx/server_names_hash_bucket_size",
  :display_name => "Nginx Server Names Hash Bucket Size",
  :default => "64"

attribute "nginx/types_hash_max_size",
  :display_name => "Nginx Types Hash Max Size",
  :default => "2048"

attribute "nginx/types_hash_bucket_size",
  :display_name => "Nginx Types Hash Bucket Size",
  :default => "64"

attribute "nginx/disable_access_log",
  :display_name => "Disable Access Log",
  :default => "false"

attribute "nginx/default_site_enabled",
  :display_name => "Default site enabled",
  :default => "true"

attribute "nginx/sendfile",
  :display_name => "Nginx sendfile",
  :description => "Whether to enable sendfile",
  :default => "on"
