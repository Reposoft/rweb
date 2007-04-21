#!/bin/sh
# phpcoder/eaccelerator hard codes some path in templates/smarty, this is a workaround
ant dev.include.test -Dsource.folder=/var/www/html/repos
