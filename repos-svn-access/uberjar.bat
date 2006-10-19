call mvn clean

@rem pack it
call mvn assembly:assembly -Ddescriptor=src/main/assembly/with-dependencies.xml
IF %ERRORLEVEL% NEQ 0 goto error

@rem run tests
@echo -
@echo running a test case from the new jar with no other depencencies than junit
@echo -
call mvn jar:test-jar
set TESTCASE=se.repos.svn.checkout.simple.SimpleWorkingCopyIntegrationTest
set RELEASEVERSION=1.1-dev
java -cp "C:\Documents and Settings\%USERNAME%\.m2\repository\junit\junit\4.1\junit-4.1.jar";target\repos-svn-access-%RELEASEVERSION%-with-dependencies.jar;target\repos-svn-access-%RELEASEVERSION%-tests.jar junit.textui.TestRunner %TESTCASE%

echo Note that the jar must be redistributed _with_ the licenses, see lib folder.
echo Fat jar built and tested. Press enter to exit.
pause
goto end

rem ---------------------------------
rem --- Old instructions, for use with 'system' scope libs.
rem create the uberjar
mvn assembly:assembly -Ddescriptor=src/main/assembly/with-dependencies.xml
rem now manually unpack the jars from the 'lib' folder into the uberjar, because the assembly plugin just places them in the root of the archive
@echo now manually unpack the jars from the 'lib' folder into the uberjar, because the assembly plugin just places them in the root of the archive
rem make test jar
mvn jar:test-jar
rem ------------------------------------

:error
@echo Jar assembly aborted. Press enter to exit.
pause

:end