#!/bin/sh

FILE="$1"

if [ -z "$FILE" ]
then
 echo "First argument must be the file to update"
 exit 1
fi

OLDHOSTNAME=$2
if [ -z "$OLDHOSTNAME" ]
then
 OLDHOSTNAME="bubba"
fi

NEWHOSTNAME=$3
if [ -z "$NEWHOSTNAME" ]
then
 NEWHOSTNAME=$(hostname)
fi

if [ "$FILE" = "repos" ]
then
 for F in /var/www/admin/repos.properties /var/www/admin/repos.properties.data /var/www/admin/repos.properties.test
 do
  if [ -f "$F" ]
  then
   /var/www/admin/switch-hostname.sh "$F" "$OLDHOSTNAME" "$NEWHOSTNAME"
  fi
 done
 exit 0
fi

if [ ! -f "$FILE" ]
then
 echo "File '$FILE' does not exist"
 exit 2
fi
   
echo "Changing hostname from $OLDHOSTNAME to $NEWHOSTNAME in $FILE"

if ! grep -q "//$OLDHOSTNAME/" $FILE
then
 echo "File does not contain any urls with the old hostname"
fi
 
sed -i "s/\/\/$OLDHOSTNAME\//\/\/$NEWHOSTNAME\//g" $FILE

if grep -q "//$NEWHOSTNAME/" $FILE
then
 echo "Done"
else
 echo "ERROR: Replaced failed."
 exit 2
fi
