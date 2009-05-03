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
	> ./mods-enabled.httpd_conf_extra

cat /etc/apache2/mods-enabled/*.conf \
	| grep -v perl \
	| grep -v cgi \
	>> ./mods-enabled.httpd_conf_extra

# TODO use module path relative to modules dir (and avoid "APXS (/usr/bin/apxs2) query for PREFIX failed")
# It might be useful that conf->_apxs->LIBEXECDIR contains the folder

# run with forwarded arguments like -trace=debug to build TEST script
# -configure parameter is added because running tests from this script is not prefered

# -maxclients X is a good parameter, particularly since operations do subrequets

# Sadly it is not possible to run this script's logic from inside TEST.PL because Apache::TestRun does 'exit'
# (tried in revision 4051)

perl ./TEST.PL -httpd_conf_extra $(pwd)/mods-enabled.httpd_conf_extra -configure $@

echo "Now run './TEST -start' to start server and './TEST -run' to test"

# clean up

rm ./mods-enabled.httpd_conf_extra

# An extra t folder is created inside t. Tried to avoid it in revision 4051 but that gave other problems.
rm -Rf ./t/

# remove autogenerated meaningless index.html and use repos root script instead

rm htdocs/index.html
if [ ! -L htdocs/index.php ]
then
	ln -s $(pwd)/../www/_host/html/* htdocs/
fi

echo "Automatic host setup is not implemented, so after server start"
echo "you need to manually cd hosts/original/ and execute setup.pl"

