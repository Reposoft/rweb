@echo off
set ADMINPATH=C:\srv\www\htdocs\repos\admin\
set TESTREPO=C:\WINDOWS\Temp\test.repos.se\repo

echo ** Examples on how to use Repos administration commands without a web server **
echo *
echo Verify that PHP is in the shell path:
echo *
php --version
echo *
echo For scheduled scripts, it is recommended to use the absolute path to the php command
echo *
echo *
echo Verify that repos admin command line interface is available:
php %ADMINPATH%commands.php
echo *
echo The following is executed from the host folder:
echo *
echo Sample usage:
echo *
echo php %ADMINPATH%commands.php dump
echo *
echo php %ADMINPATH%commands.php dump %TESTREPO% C:\srv\backup
echo *
echo php html/repos-backup/verify/index.php --backup-folder=backup/
echo *
echo php %ADMINPATH%commands.php verifyMD5 C:\srv\backup
echo *
echo mkdir \srv\repository-mirror
echo svnadmin create \srv\repository-mirror
echo php %ADMINPATH%commands.php load \srv\repository-mirror C:\srv\backup svnrepo-WINDOWS-Temp-test.repos.se-repo-
echo svnlook tree \srv\repository-mirror
