#!/usr/bin/env bash

sudo apt-get autoremove --purge -y
sudo apt-get autoclean
sudo apt-get update

# link the uploaded nginx config to enable
echo -e "\e[0m--"
rm -rf /etc/nginx/sites-enabled/*
for name in dist pma readis; do
    # link
    ln -sf "/vagrant/env/nginx/$name.conf" "/etc/nginx/sites-enabled/020-$name"
    # check link
    test -L "/etc/nginx/sites-enabled/020-$name" && echo -e "\e[0mLinking nginx $name config: \e[1;32mOK\e[0m" || echo -e "Linking nginx $name config: \e[1;31mFAILED\e[0m";
done

sudo rm -f /etc/php/7.1/mods-available/xdebug.ini
sudo cp /vagrant/env/php/7.1/xdebug.ini /etc/php/7.1/mods-available/xdebug.ini

# set correct permissions for private key
chmod 0700 /home/vagrant/.ssh
chmod 0600 /home/vagrant/.ssh/id_rsa
chmod 0600 /home/vagrant/.ssh/config
sudo chmod -R 0777 /tmp

sudo service nginx restart
sudo service php7.1-fpm restart

sed -i '1 a export CFP_ENV=development' /home/vagrant/.bashrc
echo 'CREATE DATABASE IF NOT EXISTS `cfp-phpdd-org` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' | mysql -uroot -proot
