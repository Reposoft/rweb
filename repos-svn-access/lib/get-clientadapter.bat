@echo off
echo.
echo Status of svnClientAdapter trunk:
svn info --username guest http://subclipse.tigris.org/svn/subclipse/trunk/svnClientAdapter/
echo.
echo Downloading compiled svnClientAdapter from subclipse trunk
svn export --username guest http://subclipse.tigris.org/svn/subclipse/trunk/subclipse/core/lib/svnClientAdapter.jar svnClientAdapter.jar
echo.
set /p REV="What should be the version number of svnClientAdapter in repository? "
echo.
rem org.tigris.subversion is the package name in the svnClientAdapter project
call mvn install:install-file -Dfile=svnClientAdapter.jar -DgroupId=org.tigris.subversion -DartifactId=svnClientAdapter -Dversion=%REV% -Dpackaging=jar
echo Installed org.tigris.subversion svnClientAdapter version %REV% in maven repository.