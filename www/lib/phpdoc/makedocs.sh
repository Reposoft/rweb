# install PEAR and do: pear upgrade PhpDocumentor
# you might need to upgrad pear first: pear upgrade PEAR

echo Assuming that the working folder is the folder where the script is.
cd ..
cd ..
REPOS_WEB=$(pwd)
if [ ! -d "$REPOS_WEB-docs" ]
then
	mkdir "$REPOS_WEB-docs"
fi
cd lib
cd phpdoc

echo $(date) > $REPOS_WEB-docs/docs-report.txt
phpdoc -t "$REPOS_WEB-docs" -o HTML:frames:default -d "$REPOS_WEB" -i "$REPOS_WEB/lib/*, $REPOS_WEB/test/*, *.test.*" -ti "Repos PHP documentation" >> $REPOS_WEB-docs/docs-report.txt

echo $(date) > $REPOS_WEB-docs/docs-test-report.txt
phpdoc -t "$REPOS_WEB-docs/repos-test-docs" -o HTML:frames:default -f "$REPOS_WEB/test/*, $REPOS_WEB/*.test.*" -i "$REPOS_WEB/lib/*" -ti "Repos PHP test documentation" >> $REPOS_WEB-docs/docs-test-report.txt
