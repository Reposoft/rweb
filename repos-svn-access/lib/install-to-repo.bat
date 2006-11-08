rem install svnAnt to local repository
rem - download version 1.0 from http://subclipse.tigris.org/svnant.html
set VERSION=1.1.0-RC2
call mvn install:install-file -Dfile=svnant.jar -DgroupId=org.tigris.subclipse -DartifactId=svnant -Dversion=%VERSION% -Dpackaging=jar
call mvn install:install-file -Dfile=svnClientAdapter.jar -DgroupId=org.tigris.subclipse -DartifactId=svnant-svnclientadapter -Dversion=%VERSION% -Dpackaging=jar
rem call mvn install:install-file -Dfile=svnjavahl.jar -DgroupId=org.tigris.subclipse -DartifactId=svnant-svnjavahl -Dversion=%VERSION% -Dpackaging=jar

rem install the jar from the official javahl distribution
set JAVAHL_VERSION=1.4.2
call mvn install:install-file -Dfile=svnjavahl.jar -DgroupId=org.tigris.subversion -DartifactId=javahl -Dversion=%JAVAHL_VERSION% -Dpackaging=jar

echo Installed SvnAnt %VERSION% to local maven repository
pause
