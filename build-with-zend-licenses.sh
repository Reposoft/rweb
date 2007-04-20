#!/bin/sh

SVN_TAGS_URL="https://optime.repos.se/data/repos/tags/repos-"
VERSION="1.1.3"
SWITCH_URL="$SVN_TAGS_URL-$VERSION"

echo "Version number is $VERSION. Switch working copy to that tag."
svn switch "$SWITCH_URL" .

TARGET=dist

# Optime server (Repos hosted) license
ant $TARGET -Dzend.guard.product="Repos web" \
	-Dapp.version=$VERSION \
	-Dapp.version.build="Optime" \
	-Dtarget.folder="dist-Optime"

# Trial server
ant $TARGET -Dzend.guard.product="Repos web trial" \
	-Dapp.version=$VERSION \
	-Dapp.version.build="Trial" \
	-Dtarget.folder="dist-Trial"
	
# Inceptive
ant $TARGET -Dzend.guard.product="Repos for Inceptive" \
	-Dapp.version=$VERSION \
	-Dapp.version.build="Inceptive" \
	-Dtarget.folder="dist-Inceptive"
	