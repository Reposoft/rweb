@echo off
set ADMINPATH=C:\srv\www\htdocs\repos\admin\

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
echo *
echo Sample usage:
echo *
echo php %ADMINPATH%commands.php dump
echo *
echo php %ADMINPATH%commands.php dump C:\WINDOWS\Temp\test.repos.se\repo C:\srv\backup