
$script_sqlite = <<SCRIPT
apt-get update
apt-get install -y apache2 php5 php5-sqlite php5-xdebug
apt-get clean -y
echo "ServerName localhost" >> /etc/apache2/apache2.conf
service apache2 restart
rm -f /var/www/html/index.html
date > /etc/vagrant_provisioned_at
SCRIPT

$script_mysql = <<SCRIPT
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y apache2 php5 php5-mysql php5-xdebug mysql-server mysql-client
apt-get clean -y
echo "ServerName localhost" >> /etc/apache2/apache2.conf
service apache2 restart
service mysql restart
echo "create database wallabag;" | mysql -u root
rm -f /var/www/html/index.html
date > /etc/vagrant_provisioned_at
SCRIPT

$script_postgres = <<SCRIPT
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y apache2 php5 php5-pgsql php5-xdebug postgresql postgresql-contrib
apt-get clean -y
echo "ServerName localhost" >> /etc/apache2/apache2.conf
service apache2 restart
service postgresql restart
rm -f /var/www/html/index.html
date > /etc/vagrant_provisioned_at
SCRIPT

Vagrant.configure("2") do |config|

  config.vm.define "sqlite" do |m|
    m.vm.box = "ubuntu/trusty64"
    m.vm.provision "shell", inline: $script_sqlite
    m.vm.synced_folder ".", "/var/www/html", owner: "www-data", group: "www-data"
  end

  config.vm.define "mysql" do |m|
    m.vm.box = "ubuntu/trusty64"
    m.vm.provision "shell", inline: $script_mysql
    m.vm.synced_folder ".", "/var/www/html", owner: "www-data", group: "www-data"
  end

  config.vm.define "postgres" do |m|
    m.vm.box = "ubuntu/trusty64"
    m.vm.provision "shell", inline: $script_postgres
    m.vm.synced_folder ".", "/var/www/html", owner: "www-data", group: "www-data"
  end

  config.vm.define "debian7" do |m|
    m.vm.box = "chef/debian-7.6"
    m.vm.provision "shell", inline: $script_sqlite
    m.vm.synced_folder ".", "/var/www", owner: "www-data", group: "www-data"
  end

  config.vm.define "debian6" do |m|
    m.vm.box = "chef/debian-6.0.10"
    m.vm.provision "shell", inline: $script_sqlite
    m.vm.synced_folder ".", "/var/www", owner: "www-data", group: "www-data"
  end

  config.vm.network :forwarded_port, guest: 80, host: 8003
  #config.vm.network "public_network", :bridge => "en0: Wi-Fi (AirPort)"
end
