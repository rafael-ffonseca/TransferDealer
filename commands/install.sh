apt-get update
apt-get install -y git
apt-get install -y zip
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
mv composer.phar /usr/local/bin/composer
cd /TransferDealer/src
composer update
php -S 0.0.0.0:80 -t /TransferDealer/src/public