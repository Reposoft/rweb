#!/bin/bash
TST="/tmp/test.repos.se"
HERE=$(pwd)

echo "Restoring the repos.se test repository to its baseline"
echo ""

# environment setup
export LANG="en_US.UTF-8"
export LC_ALL="en_US.UTF-8"
# svn command alias
SVN="/usr/bin/svn --config-dir $HERE/test-svn-config-dir"

# create test repository
rm -Rf "$TST"
mkdir "$TST"
mkdir "$TST/repo/"
mkdir "$TST/admin/"
#svnadmin create "$TST/repo/"
cd "$TST/repo/"
tar xzf "$HERE/newrepo.tgz"
cd "$HERE"

# create user database, base64 encoded by htpasswd2
USERS="$TST/admin/repos-users"
touch $USERS
# svensson:medel
echo "svensson:rrE3/9iLvCoFU" >> $USERS
# test:test
echo "test:n8F28qRYJJ4Q6" >> $USERS

# create ACL
ACL="$TST/admin/repos-access";
touch $ACL
echo "[groups]" >> $ACL
echo "" >> $ACL
echo "[/]" >> $ACL
echo "" >> $ACL
echo "[/svensson]" >> $ACL
echo "svensson = rw" >> $ACL
echo "" >> $ACL
echo "[/test]" >> $ACL
echo "test = rw" >> $ACL

# create apache 2.2 config
CONF="$TST/admin/testrepo.conf"
touch $CONF
echo "DAV svn" >> $CONF
echo "SVNIndexXSLT \"/repos/view/index.xsl\"" >> $CONF
echo "SVNPath $TST/repo/" >> $CONF
echo "SVNAutoversioning on" >> $CONF
echo "AuthType Basic" >> $CONF
echo "AuthUserFile $USERS" >> $CONF
echo "Require valid-user" >> $CONF
echo "AuthzSVNAccessFile $ACL" >> $CONF

echo "Apache should do \"Include $CONF\" at some <Location >"
echo ""

# check out working copy and create base structure
mkdir "$TST/wc/"
$SVN co file:///tmp/test.repos.se/repo "$TST/wc/"
mkdir "$TST/wc/svensson"
mkdir "$TST/wc/svensson/trunk"
mkdir "$TST/wc/svensson/calendar"
mkdir "$TST/wc/test"
mkdir "$TST/wc/test/trunk"
mkdir "$TST/wc/test/calendar"
$SVN add "$TST/wc/svensson"
$SVN add "$TST/wc/test"
$SVN commit -m "Created users svensson and test" "$TST/wc/"

