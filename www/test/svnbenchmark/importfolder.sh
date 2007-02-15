REPO=http://test.repos.se/testrepo/test/trunk

mkdir a

svn import --username test --password test --non-interactive a/ $REPO/importfolder/ -m "benchmark"

for F in a b c d e f g h i j k l m n o p q r s t u v x y z
do
	svn import --username test --password test --non-interactive a/ $REPO/importfolder/$F -m "import-$F"
done
rmdir a

svn delete --username test --password test --non-interactive a/ $REPO/importfolder/ -m "benchmark done"

