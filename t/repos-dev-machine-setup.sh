#!/bin/sh
# repos development environment

sudo apt-get install libapache2-mod-php5 libapache2-prefork-dev
sudo apt-get install libapache2-mod-perl2 libapache2-mod-python
sudo apt-get install default-jre tomcat7
sudo apt-get install default-jdk maven

sudo a2enmod proxy_ajp proxy_http rewrite ssl dav_fs headers vhost_alias authz_svn

