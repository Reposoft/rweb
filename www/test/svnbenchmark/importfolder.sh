
#REPO=http://test.repos.se/testrepo/test/trunk

if [ -z "$1" ]
then
 REPOF=/tmp/svntest
 mkdir $REPOF
 svnadmin create $REPOF
 REPO="file:///$REPOF"
else
 REPO="$1"
fi
echo "Running benchmark with repo $REPO, start time $(date)"

mkdir a

svn import --username test --password test --non-interactive a/ $REPO/importfolder/ -m "benchmark"

#for F in a b c d e f g h i j k l m n o p q r s t u v x y z
for F in a b c d e f g h i j
do
	svn import --username test --password test --non-interactive a/ $REPO/importfolder/$F -m "import-$F"
done
rmdir a

svn delete --username test --password test --non-interactive $REPO/importfolder/ -m "benchmark done"

echo "Benchmark done, end time $(date)"

if [ ! -z "$REPOF" ]
then
 rm -Rf "$REPOF"
fi

