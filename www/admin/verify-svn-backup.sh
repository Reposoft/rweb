
# Parameters:
#  1: The absolute path to where the dump files are
BACKUPDIR="$1"
#  2: Filenames up to firs revision number, for example "myrepo-" for myrepo-00?-to-0??.svndump.gz
FILEPREFIX="$2"

# Return values:
#  0 = successfull execution, valid backup
#  1 = error in execution, don't know anything about backup
#  2 = invalid backup

if [ -z "$BACKUPDIR" -o -z "$FILEPREFIX" ]
then
	echo "Usage: ./verify-svn-backup '/path/to/files' 'file-prefix-'"
	exit 1
fi

echo "***** Validating $FILEPREFIX* in directory $BACKUPDIR *****"

SECOND="$(date +'%s')"
REPONAME="verify-$SECOND"
REPO="/srv/repos/$REPONAME"

# create temporary repository
mkdir "$REPO"
CREATIONTIME="$(date +'%F')"
echo "[debug] creating temporary repository $REPO on $CREATIONTIME"
/usr/bin/svnadmin create "$REPO"

# go to backupfiles
cd "$BACKUPDIR"
FILECOUNT="$(ls -A1 $FILEPREFIX* | wc -l)"
echo "[debug] there are $FILECOUNT files starting with '$FILEPREFIX' in $(pwd)"

# iterate over the list of files
REVTO="-1"
LASTDATE="$(date +'%Y.%V')"
for FILENAME in $(ls -A1 $FILEPREFIX*);
do 
	# check revision numbers
	REVFROM=$(expr match $FILENAME "$FILEPREFIX[0]*\([0-9][0-9]*\)-.*")
	if [ ! $REVFROM = $(expr $REVTO + 1) ]
	then
		echo "ERROR: revision number gap at $FILENAME starting at rev $REVFROM, last rev was $REVTO"
		exit 2
	fi
	REVTO=$(expr match $FILENAME "$FILEPREFIX[0-9]*-to-[0]*\([0-9][0-9]*\).*")
	# insert into repository
	echo "[debug] loading $FILENAME revision $REVFROM to $REVTO"
	/usr/bin/gunzip -c "$FILENAME" | /usr/bin/svnadmin load "$REPO"
	RETURNVALUE=$?
	if [ $RETURNVALUE -ne 0 ]
	then
  		echo "ERROR: loading svn-dump $FILENAME returned $RETURNVALUE. Backup is invalid."
		exit 2
	fi
	# save date of this backup file
	LASTDATE="$(date -r "$FILENAME" +'%Y.%V')"
done
/usr/bin/svnadmin verify $REPO
RETURNVALUE=$?
if [ $RETURNVALUE -ne 0 ]
then
	echo "ERROR: verifying repository $REPO returned $RETURNVALUE. Backup is invalid."
	exit 2
fi

# check date of last backup
if [ ! "$LASTDATE" = "$(date +'%Y.%V')" ]
then
	echo "WARNING: last backup file is created $LASTDATE: not within the current time period $(date +'%Y.%V')"
fi

# delete temporary repository if created today (as security measure)
if [ "$CREATIONTIME" = "$(date -r "$REPO" +'%F')" ]
then
	rm -Rf "$REPO"
	echo "[debug] done, removed temporary repository $REPO"
else
	echo "[error] repo temp folder not created today. not deleting $REPO"
fi
