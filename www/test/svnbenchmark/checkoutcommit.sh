REPO=http://test.repos.se/testrepo/test

cd /tmp
mkdir svnbm
echo "benchmark" >> svnbm/a.txt
svn import --username test --password test --non-interactive svnbm $REPO/svnbm/ -m "benchmark"
rm -Rf svnbm/

for F in a b c d e f g h i j k l m n o p q r s t u v x y z
do
 svn checkout --username test --password test --non-interactive $REPO/svnbm/ svnbm
 echo "$F" >> svnbm/a.txt
 svn commit --username test --password test --non-interactive svnbm/ -m "$F"
 rm -Rf svnbm/
done

svn delete --username test --password test --non-interactive a/ $REPO/svnbm/ -m "benchmark done"


