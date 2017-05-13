#!/bin/bash
VERSION=1.6.4
rm -Rf target
mkdir target
cp -r repos-plugins target/
cp -r repos-web target/
php target/repos-web/lib/smarty/install.php
php target/repos-web/lib/simpletest/install.php
cd target/repos-plugins/highlight/ && npm install && ./node_modules/.bin/webpack --bail && rm -Rf node_modules && cd ../../../
cd target/repos-web/scripts/ && ./build-defaultexcludes.bat && cd ../../../
cd target && tar cfz ../ReposWeb-$VERSION.tgz repos-web/ repos-plugins/ && cd ..
cd target && zip -qr ../ReposWeb-$VERSION.zip repos-web/ repos-plugins/ && cd ..
