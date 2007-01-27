@echo off

rem SvnAnt is no longer used
rem install svnAnt to local repository
rem - download version 1.0 from http://subclipse.tigris.org/svnant.html
rem set VERSION=1.1.0-RC2
rem call mvn install:install-file -Dfile=svnant.jar -DgroupId=org.tigris.subclipse -DartifactId=svnant -Dversion=%VERSION% -Dpackaging=jar
rem call mvn install:install-file -Dfile=svnClientAdapter.jar -DgroupId=org.tigris.subclipse -DartifactId=svnant-svnclientadapter -Dversion=%VERSION% -Dpackaging=jar
rem call mvn install:install-file -Dfile=svnjavahl.jar -DgroupId=org.tigris.subclipse -DartifactId=svnant-svnjavahl -Dversion=%VERSION% -Dpackaging=jar

echo Install the jar from the official javahl distribution (download and unzip to this folder)
set JAVAHL_VERSION=1.4.3
call mvn install:install-file -Dfile=svnjavahl.jar -DgroupId=org.tigris.subversion -DartifactId=javahl -Dversion=%JAVAHL_VERSION% -Dpackaging=jar
echo libsvnjavahl-1.dll must be distributed separately

echo Installed Javahl %JAVAHL_VERSION% to local maven repository
pause
