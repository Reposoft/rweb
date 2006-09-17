#!/bin/bash
# name the temp dir where the repository will be. This dir will be removed recursively.
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
if [ -d "$TST/repo" ]
then
	rm -Rf "$TST"
else
	if [ -d "$TST" ]
	then
		echo "Is $TST really the right test dir? It already exists but does not contain the testrepository"
		exit 1
	fi
fi
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
echo "demoproject = svensson, test" >> $ACL
echo "" >> $ACL
echo "[/]" >> $ACL
echo "" >> $ACL
echo "[/svensson]" >> $ACL
echo "svensson = rw" >> $ACL
echo "" >> $ACL
echo "[/test]" >> $ACL
echo "test = rw" >> $ACL
echo "" >> $ACL
echo "[/demoproject]" >> $ACL
echo "@demoproject = rw" >> $ACL
echo "" >> $ACL
echo "[/demoproject/trunk/readonly]" >> $ACL
echo "@demoproject = r" >> $ACL
echo "" >> $ACL
echo "[/demoproject/trunk/noaccess]" >> $ACL
echo "@demoproject = " >> $ACL
echo "" >> $ACL

# create apache 2.2 config
CONF="$TST/admin/testrepo.conf"
touch $CONF
echo "DAV svn" >> $CONF
echo "SVNIndexXSLT \"/repos/view/repos.xsl\"" >> $CONF
echo "SVNPath $TST/repo/" >> $CONF
echo "SVNAutoversioning on" >> $CONF
echo "AuthType Basic" >> $CONF
echo "AuthUserFile $USERS" >> $CONF
echo "Require valid-user" >> $CONF
echo "AuthzSVNAccessFile $ACL" >> $CONF

echo "Apache should do \"Include $CONF\" at some <Location >"
echo "Note that apache must be restarted if there are changes in this file."
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
mkdir "$TST/wc/demoproject"
mkdir "$TST/wc/demoproject/trunk"
mkdir "$TST/wc/demoproject/trunk/noaccess"
mkdir "$TST/wc/demoproject/trunk/readonly"
$SVN add "$TST/wc/svensson"
$SVN add "$TST/wc/test"
$SVN add "$TST/wc/demoproject"
$SVN commit -m "Created users svensson and test, and a shared project" "$TST/wc/"

# create a base structure
FOLDERS="a b c d e f g h i j k l m n o p q r s t u v x y z"
TESTFOLDER="$TST/wc/test/trunk"
for dir in $FOLDERS
do
	TESTFOLDER="$TESTFOLDER/f$dir"
	mkdir "$TESTFOLDER"
	echo "$dir" > "$TESTFOLDER/$dir.txt"
done
$SVN add "$TST/wc/test/trunk/fa"
$SVN commit -m "Created a test folder structure for user test" "$TST/wc/"
