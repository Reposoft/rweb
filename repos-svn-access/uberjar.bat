rem now this should do it:
call mvn assembly:assembly -Ddescriptor=src/main/assembly/with-dependencies.xml

rem run tests
@echo -
@echo running a test case from the new jar with no other depencencies than junit
@echo -
set TESTCASE=se.repos.svn.checkout.simple.SimpleWorkingCopyIntegrationTest
java -cp "C:\Documents and Settings\%USERNAME%\.m2\repository\junit\junit\4.1\junit-4.1.jar";target\repos-svn-access-1.0-SNAPSHOT-with-dependencies.jar;target\repos-svn-access-1.0-SNAPSHOT-tests.jar junit.textui.TestRunner %TESTCASE%

echo fat jar built and tested, press enter to exit
pause
goto end

rem --- Old instructions, for use with 'system' scope libs.
rem create the uberjar
mvn assembly:assembly -Ddescriptor=src/main/assembly/with-dependencies.xml
rem now manually unpack the jars from the 'lib' folder into the uberjar, because the assembly plugin just places them in the root of the archive
@echo now manually unpack the jars from the 'lib' folder into the uberjar, because the assembly plugin just places them in the root of the archive
rem make test jar
mvn jar:test-jar

:end