EMAIL="staffan@optime.se"

/srv/scripts/verify-svn-backup.sh  /home/solsson/www.optime.se/backup/ svnrepo-srv-repos-system-
VERIFY1=$?
/srv/scripts/verify-svn-backup.sh  /home/solsson/www.optime.se/backup/ svnrepo-srv-repos-staffan-
VERIFY2=$?
/srv/scripts/verify-svn-backup.sh  /home/solsson/www.optime.se/backup/ svnrepo-srv-repos-com-onweb-
VERIFY3=$?

if [ $VERIFY1 -ne 0 -o $VERIFY2 -ne 0 -o $VERIFY3 -ne 0 ]
then
	echo "Check local cron results at Castor" | mailx -s "Castor: Optime.se backup validation failed" "$EMAIL"
fi
