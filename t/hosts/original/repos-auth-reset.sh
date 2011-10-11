#!/bin/sh

# read repos user accounts and access control from repository
# made to run after backup load

# currently repos lacks ability to run hooks for loaded backup revs

HOST=$1
REPO=$HOST/repo/
USERFILE=$HOST/admin/repos-users
ACCSFILE=$HOST/admin/repos-access

if [ -z "$HOST" ]
then
    echo "First argument must be a host path"
        exit 1
fi

if [ ! -d "$REPO" ]
then
    echo "The repository $REPO does not exist"
        exit 1
fi

svnlook tree --full-paths $REPO \
    | grep "^[^/]*\/administration\/repos\.user" \
    | xargs -I '{}' svnlook cat $REPO '{}' \
    > $USERFILE.tmp
echo "Overwriting current $(cat $USERFILE | wc -l) users with $(cat $USERFILE.tmp | wc -l) users from repository" 
cp $USERFILE.tmp $USERFILE
rm $USERFILE.tmp

# read access control
svnlook cat $REPO administration/repos.accs > $ACCSFILE.tmp
echo "Overwriting current ACL, $(cat $ACCSFILE | wc -l) lines, with administration/repos.accs, $(cat $ACCSFILE.tmp | wc -l) lines"
cp $ACCSFILE.tmp $ACCSFILE
rm $ACCSFILE.tmp

# at the same time homepage should be exported, if we can get a url for svn client
echo "Getting the file URL to use the testrepo from local svn client"
URL="file://$(realpath $HOST/repo)" # found no better way than using the realpath command
rm -Rf $HOST/html/home
svn export $URL/administration/homepage/ $HOST/html/home
chmod -R g+w $HOST/html/home
echo "Exported $(ls $HOST/html/home | wc -l) homepage files to $HOST/html/home"

