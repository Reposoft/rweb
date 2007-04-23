#!/bin/sh

# phpcoder/eaccelerator hard codes some path in templates/smarty, this is a workaround
#ant dev.include.test -Dsource.folder=/var/www/html/repos
ant dist.debian -Dsource.folder=/var/www/html/repos

# then test the compiled code before distributing
rm -Rf /var/www/html/repos
cp -R target/repos/ /var/www/html/
