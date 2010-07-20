#!/bin/bash
rm -Rf repos-web
rm -Rf repos-plugins

VERSION="2.4"
wget http://sourceforge.net/projects/reposserver/files/reposstyle/repos-style-with-plugins-$VERSION.zip/download
unzip repos-style-with-plugins-$VERSION.zip

pushd ../../
SVN="$(pwd)/multirepo/svn"
popd
echo "Subversion parent path is at: $SVN"

LOG="repos-web/open/log/index.php"
# can't get sed -i to work on mac so using temp file
sed "s|@@Repository@@|file://$SVN|" < "$LOG" > "$LOG.tmp"
sed 's/isParent = false/isParent = true/' < "$LOG.tmp" > "$LOG"
