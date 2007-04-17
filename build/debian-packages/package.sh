
mkdir ./dist

# remember to update version numbers to match current control files

dpkg -b repos.arm/ ./dist/repos_1.1.2-1.arm.deb
dpkg -b repos-build.arm/ ./dist/repos-build_1.1-1.arm.deb
dpkg -b eaccelerator.arm/ ./dist/eaccelerator_0.9.5-2.arm.deb

cd ./dist/
pkg-scanpackages ./ /dev/null | gzip -9c > Packages.gz
cd ..

# and to use the package site in /etc/apt/sources.list add: 'http://host/package-path/ ./'
