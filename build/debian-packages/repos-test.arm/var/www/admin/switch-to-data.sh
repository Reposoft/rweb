#!/bin/sh

echo "Switch repos apache config"
if [ ! -e /etc/apache2/sites-enabled/data-repository.conf ]
then
 ln -sv /var/www/admin/data-repository.conf /etc/apache2/sites-enabled
fi

if [ -e /etc/apache2/sites-enabled/testrepo.conf ]
then
 rm -v /etc/apache2/sites-enabled/testrepo.conf
fi

if [ -f /var/www/admin/repos.properties.data ]
then
 echo "Switch repos settings"
 mv -v /var/www/admin/repos.properties /var/www/admin/repos.properties.test
 mv -v /var/www/admin/repos.properties.data /var/www/admin/repos.properties
fi

if [ -f /var/www/admin/repos-access.data ]
then
 mv -v /var/www/admin/repos-access.data /var/www/admin/repos-access
fi 

/etc/init.d/apache2 reload
