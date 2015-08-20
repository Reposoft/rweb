#!/bin/sh
VERSION=1.5.3.1
rm target -Rf
mkdir target
cp -r repos-plugins/ repos-web/ target/
php target/repos-web/lib/smarty/install.php
php target/repos-web/lib/syntaxhighlighter/install.php
php target/repos-web/lib/simpletest/install.php
cd target/repos-web/scripts/ && ./build-arbortext.bat && cd ../../../
cd target && tar cfz ../ReposWeb-$VERSION.tgz repos-web/ repos-plugins/ && cd ..

