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

echo "Switch repos settings"
mv -v /var/www/admin/repos.properties /var/www/admin/repos.properties.data
mv -v /var/www/admin/repos.properties.test /var/www/admin/repos.properties
cp -v /var/www/admin/repos-access /var/www/admin/repos-access.data
if [ -f /var/www/admin/repos-users ]
then
 mv -v /var/www/admin/repos-users /var/www/admin/repos-users.data
fi

echo "Create folder for testrepository"
if [ ! -d /tmp/repos-test/ ]
then
 mkdir -v /tmp/repos-test/
fi

if [ ! -d /tmp/repos-test/repo/ ]
then
 mkdir -v /tmp/repos-test/repo/
 svnadmin create /tmp/repos-test/repo/
fi

chown -R www-data /tmp/repos-test/

echo "Create empty test backup folder"
if [ -d /tmp/repos-test/backup/ ]
then
 rm -Rf /tmp/repos-test/backup/
fi
mkdir -v /tmp/repos-test/backup/

sudo /etc/init.d/apache2 reload
