apt update
apt install -y git
apt install -y zip
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
mv composer.phar /usr/local/bin/composer
cd /TransferDealer/src
composer update
php -S 0.0.0.0:80 -t /TransferDealer/src/public