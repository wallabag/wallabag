# -*- mode: ruby -*-
# vi: set ft=ruby :

$first_install = <<SCRIPT
cd /vagrant
curl -s http://getcomposer.org/installer | sudo -u vagrant -H php
sudo -u vagrant -H php composer.phar install
sudo -u vagrant -H cp install/poche.sqlite db/
mv install/ fake_deleted_install
SCRIPT

Vagrant.configure("2") do |config|
  config.vm.box = "wheezy-lxc-chef"
  config.vm.box_url = "https://www.dropbox.com/s/68z52v3d8vj8s09/wheezy-lxc-chef.box"
  config.hostmanager.enabled = true
  config.hostmanager.manage_host = true
  config.hostmanager.ignore_private_ip = false
  config.hostmanager.include_offline = true
  config.vm.define 'poche.local' do |node|
    node.vm.hostname = 'poche.local'
    # node.vm.customize do |vm|
    #   vm.memory_size = 1024
    # end
    node.vm.network :forwarded_port, guest: 80, host: 8080, auto_correct: true
    node.vm.provision :chef_solo do |chef|
      chef.cookbooks_path = "chef-cookbooks"
      # chef.add_recipe("nginx")
      chef.add_recipe("php-fpm")
      chef.add_recipe("poche")

      chef.json = {
        'php-fpm' => {
          'pool' => {
            'www' => {
              'user' => 'vagrant',
              'group' => 'vagrant',
              'listen' => '/var/run/php-fpm-www.sock',
            }
          }
        },
        'nginx' => {
          'webroot' => '/vagrant/',
          'user' => 'vagrant',
        }
      }
    end
    node.vm.provision :shell, inline: $first_install
  end
end
