#!/bin/sh

echo "Switch repos apache config"
if [ -e /etc/apache2/sites-enabled/data-repository.conf ]
then
 rm -v /etc/apache2/sites-enabled/data-repository.conf
fi

if [ ! -e /etc/apache2/sites-enabled/testrepo.conf ]
then
 ln -sv /var/www/admin/testrepo.conf /etc/apache2/sites-enabled
fi

if [ -f /var/www/admin/repos.properties.test ]
then
 echo "Switch repos settings"
 mv -v /var/www/admin/repos.properties /var/www/admin/repos.properties.data
 mv -v /var/www/admin/repos.properties.test /var/www/admin/repos.properties
 cp -v /var/www/admin/repos-access /var/www/admin/repos-access.data
fi

if [ -f /var/www/admin/repos-users ]
then
 mv -v /var/www/admin/repos-users /var/www/admin/repos-users.data
fi

echo "Webserver user must be able to write to repos-users and repos-access"
chmod a+w /var/www/admin/repos-users
chmod a+w /var/www/admin/repos-access

echo "Create folder for testrepository"
if [ ! -d /var/www/testrepo/ ]
then
 mkdir -v /var/www/testrepo/
 svnadmin create /var/www/testrepo/
fi

chown -R www-data /var/www/testrepo/

echo "Create empty test backup folder"
if [ -d /var/www/testbackup/ ]
then
 rm -Rf /var/www/testbackup/
fi
mkdir -v /var/www/testbackup/
chmod a+w /var/www/testbackup/ 

sudo /etc/init.d/apache2 reload
