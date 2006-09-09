@echo works better as instruction then as batch file

rem create the uberjar
mvn assembly:assembly -Ddescriptor=src/main/assembly/with-dependencies.xml
rem now manually unpack the jars from the 'lib' folder into the uberjar, because the assembly plugin just places them in the root of the archive
@echo now manually unpack the jars from the 'lib' folder into the uberjar, because the assembly plugin just places them in the root of the archive
rem make test jar
mvn jar:test-jar
rem run tests
java -cp "C:\Documents and Settings\solsson\.m2\repository\junit\junit\4.1\junit-4.1.jar";target\repos-svn-access-1.0-SNAPSHOT-with-dependencies.jar;target\repos-svn-access-1.0-SNAPSHOT-tests.jar junit.textui.TestRunner se.repos.svn.checkout.simple.PersonalWorkingCopyIntegrationTest