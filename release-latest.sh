#!/bin/sh
# Helper script to make a release the new 1.3-latest in a single commit

LATEST=$1

if [ -z $LATEST ]
then
 echo "First argument must be a tag name"
 exit 1
fi

echo "Setting $LATEST as new 1.3-latest"
mkdir tempwc
svn co --depth empty https://optime.repos.se/data/repos/tags/ tempwc/
svn update --depth empty tempwc/$LATEST
if [ ! -d tempwc/$LATEST ]
then
 echo "Failed to get tag $LATEST"
 exit 1
fi
svn update --depth empty tempwc/1.3-latest
svn rm tempwc/1.3-latest/
rm -Rf tempwc/1.3-latest/
svn cp tempwc/$LATEST tempwc/1.3-latest
svn commit -m "$LATEST" tempwc/
rm -Rf tempwc/

svn log -v --limit 1 https://optime.repos.se/data/repos/tags/1.3-latest
