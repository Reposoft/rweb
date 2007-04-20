#!/bin/sh
# phpcoder/eaccelerator hard codes some path in templates/smarty, this is a workaround
ant -Dsource.folder=/var/www/html/repos
