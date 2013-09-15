# Create a virtual machine to run Poche as if it was hosted

## Prerequisites
- Linux kernel supporting LXC
- Vagrant (http://downloads.vagrantup.com/)

Install lxc and hostmanager plugins for vagrant and configure vagrant to use lxc as his default provider
```bash
vagrant plugin install vagrant-lxc
vagrant plugin install vagrant-hostmanager
export VAGRANT_DEFAULT_PROVIDER=lxc
```

Be sure to run the latest versions of each plugins. This installation has been successfully tested with Ubuntu 13.04, vagrant 1.3.1, vagrant-lxc 0.6.0, vagrant-hostmanager 1.2.1.

## Lauching Poche
Launch the VM. The basebox will be fetched from Dropbox, and provisioning will begin right after boot.
```bash
vagrant up
```

This command will add VM IP to your `/etc/hosts` local file.
```bash
vagrant hostmanager
```

Poche should be then available from your host browser on [http://poche.local](http://poche.local)

## Shuting down
You can shutdown the Poche VM using `halt` command if you want to wake it up again later, or using the `destroy` command to flush it completly.
```bash
vagrant halt
vagrant destroy
```
