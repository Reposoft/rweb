
mkdir ./dist

# seems like Ant can't chmod folders, so we have to do that manually
chmod 755 repos.arm/DEBIAN
chmod 755 repos-test.arm/DEBIAN
chmod 755 repos-build.arm/DEBIAN
chmod 755 eaccelerator.arm/DEBIAN

# remember to update version numbers to match current control files

dpkg -b repos.arm/ ./dist/repos_@REPOSVERSION@-@REPOSBUILD@.arm.deb
dpkg -b repos-test.arm/ ./dist/repos-test_@REPOSVERSION@-@REPOSBUILD@.arm.deb
# currently build setup follows the repos version numbers
dpkg -b repos-build.arm/ ./dist/repos-build_@REPOSVERSION@-@REPOSBUILD@.arm.deb
# eaccelerator version is the compiled release
EACCELERATORVERSION=0.95-2
dpkg -b eaccelerator.arm/ ./dist/eaccelerator_$EACCELERATORVERSION.arm.deb

cd ./dist/
dpkg-scanpackages ./ /dev/null | gzip -9c > Packages.gz
cd ..

# and to use the package site in /etc/apt/sources.list add: 'http://host/package-path/ ./'
