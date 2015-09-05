# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "ubuntu/trusty64"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  config.vm.network :forwarded_port, guest: 80, host: 8080

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.88.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "../data", "/vagrant_data"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  config.vm.provider "virtualbox" do |vcpu|
    vcpu.memory = 1024
    vcpu.cpus = 2
  end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL

     sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password password'
     sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password password'
     sudo apt-get update
     sudo apt-get install -y apache2 tor 2> /dev/null
     sudo apt-get install -y mysql-server libapache2-mod-auth-mysql 2> /dev/null
     sudo apt-get install -y php5-cgi php5-cli php5-gd php5-curl php5-mysql php5-intl php5-xdebug php5-mcrypt libapache2-mod-php5 2> /dev/null
     sudo apt-get install -y git 2> /dev/null

     sudo echo "
       zend_extension=xdebug.so
       xdebug.remote_autostart = on
       xdebug.remote_enable = on
       xdebug.remote_host = 127.0.0.1
       xdebug.remote_connect_back = on
       xdebug.idekey = vagrant
     " > /etc/php5/apache2/conf.d/20-xdebug.ini

     sudo a2enmod rewrite
     sudo a2enmod alias
     sudo php5enmod mcrypt
     sudo echo "
       <VirtualHost *:80>
         DocumentRoot /var/www/ZendTestProject/public
         <Directory /var/www/ZendTestProject/public>
           DirectoryIndex index.php
           AllowOverride All
           Order allow,deny
           Allow from all
          </Directory>
       </VirtualHost>
     " > /etc/apache2/sites-available/ZendTestProject.conf

     sudo a2dissite 000-default
     sudo a2ensite ZendTestProject
     sudo service apache2 restart

     cd /var/www/ZendTestProject

     sudo wget -q http://getcomposer.org/composer.phar -O composer.phar > /dev/null 2>&1
     sudo php ./composer.phar install --no-progress
     mysql -uroot -ppassword -e "CREATE DATABASE zend_test;"
     mysql -uroot -ppassword -e "GRANT ALL privileges on zend_test.* TO zend_test@'localhost' IDENTIFIED BY 'password';"
     mysql -uroot -ppassword zend_test < /var/www/ZendTestProject/data/zend_test.sql

     sudo sed -i -e 's/bind-address/#bind-address/g' /etc/mysql/my.cnf
     sudo service mysql restart

  SHELL

  config.vm.synced_folder '.', '/var/www/ZendTestProject', owner: "www-data", group: "www-data"
end
