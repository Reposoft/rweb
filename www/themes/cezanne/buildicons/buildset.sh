#!/bin/bash

# (C) Nick Bargnesi nbargnesi at den-4.com
# This script is distributed under the GPL.  See the file GPL.
# If you make any improvements, feel free to email me any changes. nbargnesi at den-4.com

#Updated By: 	Guppetto (guppetto@msn.com)
#Updated On: 	07/26/2004
#Version:	buildset 1.1
#Changes: 	Mandrake kmenu Icon creation code added, Error Messages were updated,
#		Kmenu icon creation message was updated, index.desktop update comment was added	 

if [ -n "$1" ]
then
CONVERTOPTIONS="$1"
else
CONVERTOPTIONS=""
fi

# PACKAGENAME is the final tarred, compressed, iconset.
PACKAGENAME="Cezanne"
REQUIRED_SIZES="32x32 22x22 16x16"

# DEFINE NEW SIZES HERE
#	These are sizes for everything except the actions folder, which contains
#	menu and toolbar related stuff for KDE.  Add or remove a size here, I
#	would recommend not removing the 32, 22, and 16 sizes.
#
#	Note:
#	If you add additional sizes, you must update your index.desktop file to 
#	access the additional icons. I've added the non-standard 56x56 category 
#	as an example.
 
SIZES="128x128 64x64 48x48 $REQUIRED_SIZES"
DIRS="apps devices filesystems mimetypes" # no actions directory needed, its hardcoded

CONVERT_PATH=
TAR_PATH=
COMPRESSOR=
function checkCompressor() {
	echo -ne "Checking for bzip2... "
	FOUND=`which bzip2`
	if [ "$FOUND" != "" ]; then
		echo -ne "found $FOUND\n"
		COMPRESSOR=$FOUND
		return
	else
		echo -ne " no.\n"
		echo -ne "Checking for gzip... "
		FOUND=`which gzip`
		if [ "$FOUND" != "" ]; then
			echo -ne "found $FOUND\n"
			COMPRESSOR=$FOUND
			return
		else
			echo -ne " no.\n"
			echo -ne "\nNo compressor found (bzip2 | gzip).\n"
			exit 1
		fi
	fi
}
function checkNeeded() {
	echo -ne "Checking for tar... "
	FOUND=`which tar`
	if [ "$FOUND" != "" ]; then
		echo -ne " found $FOUND\n"
		TAR_PATH=$FOUND
		echo -ne "Checking for convert... "
		FOUND=`which convert`
		if [ "$FOUND" != "" ]; then
			echo -ne " found $FOUND\n"
			CONVERT_PATH=$FOUND
			return
		else
			echo -ne " no.\n"
			echo -ne "\nNo convert found in path.\n"
			exit 1
		fi
	else
		echo -ne " no.\n"
		echo -ne "\nNo tar found in path.\n"
		exit 1
	fi
}
function printFound() {
	echo -ne "\nDependencies met - this script is using:\n"
	echo -ne "\t\t$COMPRESSOR as compressor\n"
	echo -ne "\t\t$TAR_PATH as tar path\n"
	echo -ne "\t\t$CONVERT_PATH as convert path\n"
}

echo -ne "This script builds an installable KDE iconset using bash and convert.\n"
echo -ne "Change what you want, add additional sizes, whatever... :)\n"
echo

checkCompressor
checkNeeded
printFound
KMENU_ICON="tux"

# Add your distributions kmenu specific icon name below. 
# The line cp -f 128x128/apps/$KMENU_ICON.png 128x128/apps/menuk-mdk.png
# was added to create the required icon for Mandrake Linux.

if test -f 128x128/apps/$KMENU_ICON.png
	then
		cp -f 128x128/apps/$KMENU_ICON.png 128x128/apps/kmenu.png
		cp -f 128x128/apps/$KMENU_ICON.png 128x128/apps/go.png
		
#	Mandrake Specific Kmenu Icon to create

		cp -f 128x128/apps/$KMENU_ICON.png 128x128/apps/menuk-mdk.png

#	Distribution Specific Kmenu Icon to create

	else
		echo -ne "Invalid selection ($KMENU_ICON), the $KMENU_ICON icon was not found in the .128x128/apps/ directory....exiting...\n"
		exit 1
fi
echo -ne "Ready to go!  Converting all icons! (about 15 seconds on an Athlon 64)\n"
echo

#Loop directory creation according to SIZES specified at startup
for size in $SIZES
do
	mkdir -p $size/apps $size/devices $size/mimetypes $size/filesystems
done

# Required sizes for actions
mkdir -p 32x32/actions 22x22/actions 16x16/actions

# Mmmm... loops...
for dir in $DIRS
do
	cd 128x128/$dir
	for icon in *
	do
		# Loop the specified sizes
		for size in $SIZES
		do
			convert "$icon" -resize $size $CONVERTOPTIONS ../../$size/$dir/"$icon"
		done
	done
	# Move from 128x128/$directory to toplevel
	cd ../../
done

# Move from 128x128/ to 32x32/
cd 32x32/actions
for icon in *
do
	convert "$icon" -resize 32x32 $CONVERTOPTIONS ../../32x32/actions/"$icon"
	convert "$icon" -resize 22x22 $CONVERTOPTIONS ../../22x22/actions/"$icon"
	convert "$icon" -resize 16x16 $CONVERTOPTIONS ../../16x16/actions/"$icon"
done

# Move to top directory
cd ../../

echo -ne "\nAll done. ;)\n"