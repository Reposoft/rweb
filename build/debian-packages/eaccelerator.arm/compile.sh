# download eAccelerator src and do
phpize
./configure --enable-eaccelerator=shared --with-php-config=/usr/bin/php-config --with-eaccelerator-userid=www-data
make
make install

