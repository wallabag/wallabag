name              "yum"
maintainer        "Opscode, Inc."
maintainer_email  "cookbooks@opscode.com"
license           "Apache 2.0"
long_description  IO.read(File.join(File.dirname(__FILE__), 'README.md'))
version           "2.3.2"
recipe            "yum", "Empty recipe."
recipe            "yum::yum", "Manages yum configuration"

%w{ redhat centos scientific amazon }.each do |os|
  supports os, ">= 5.0"
end

attribute "yum/exclude",
  :display_name => "yum.conf exclude",
  :description => "List of packages to exclude from updates or installs. This should be an array.  Shell globs using wildcards (eg. * and ?) are allowed.",
  :required => "optional"

attribute "yum/installonlypkgs",
  :display_name => "yum.conf installonlypkgs",
  :description => "List of packages that should only ever be installed, never updated. Kernels in particular fall into this category. Defaults to kernel, kernel-smp, kernel-bigmem, kernel-enterprise, kernel-debug, kernel-unsupported.",
  :required => "optional"

attribute "yum/proxy",
  :display_name => "yum.conf proxy",
  :description => "Set the http URL for proxy to use in yum.conf",
  :required => "optional"

attribute "yum/proxy_username",
  :display_name => "yum.conf proxy_username",
  :description => "Set the proxy_username to use for yum.conf",
  :required => "optional"

attribute "yum/proxy_password",
  :display_name => "yum.conf proxy_password",
  :description => "Set the proxy_password to use for yum.conf",
  :required => "optional"
