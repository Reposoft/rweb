@echo off
echo First manually get the jar from http://ubique.ch/code/inieditor/ and place in this folder.
echo pause
set VERSION=r4
call mvn install:install-file -Dfile=inieditor.jar -DgroupId=ch.ubique -DartifactId=inieditor -Dversion=%VERSION% -Dpackaging=jar

echo Installed IniEditor %VERSION% to local maven repository
pause