#!/bin/sh
# In debian's /etc/apache2 structure, Apache::Test 
# parses only the main conf file and forgets the enabled modules.

# or we could probably have a custom config file with desired modules and let TestConfig resolve absolute path

if [ ! -e ./TEST.PL ]
then
	echo "This script should be run from the t/ folder"
	exit 1
fi


# list additional modules to be enabled

cat /etc/apache2/mods-enabled/*.load \
	| grep -v perl \
	| grep -v cgi \
	| grep -v php \
	> ./mods-enabled.httpd_conf_extra

cat /etc/apache2/mods-enabled/*.conf \
	| grep -v perl \
	| grep -v cgi \
	| grep -v php \
	>> ./mods-enabled.httpd_conf_extra

# run with forwarded arguments like -trace=debug to build TEST script (unless -configure)

perl ./TEST.PL -httpd_conf_extra $(pwd)/mods-enabled.httpd_conf_extra $@

# clean up

rm ./mods-enabled.httpd_conf_extra

