#!/bin/bash
# Update public web from subversion
# UTF-8 needed for svn update (this is the user shell setting)
export LANG=en_US.UTF-8

# Update
echo "-- Updating WWW contents --"
env
#/usr/bin/svn co http://localhost/repos/olsson/www /srv/www/htdocs
/usr/bin/svn export --force file:///srv/repos/olsson/www /srv/www/htdocs
echo "-- Done updating --"
#/usr/bin/svn info /srv/www/htdocs
