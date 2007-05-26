
# the path in dpkg-scanpackages must match the apt source server
DISTSFOLDER="repos/main/binary-arm"
DISTSREPO="https://update.repos.se/data/$DISTSFOLDER"
DISTS="dists/$DISTSFOLDER"
# eaccelerator build is only updated when there is a new PHP patch from debian updates
EACCELERATORVERSION=0.9.5.1-1

echo "Building packages to folder $DISTSFOLDER"
mkdir -p $DISTS

if [ ! -f $DISTS/.svn ]
then
 echo "Checking out existing distribution"
 svn co --non-interactive "$DISTSREPO" "$DISTS"
fi

# seems like Ant can't chmod folders, so we have to do that manually
chmod 755 repos.arm/DEBIAN
chmod 755 repos-test.arm/DEBIAN
chmod 755 repos-build.arm/DEBIAN
chmod 755 eaccelerator.arm/DEBIAN


dpkg -b repos.arm/ $DISTS/repos_@REPOSVERSION@-@REPOSBUILD@.arm.deb
dpkg -b repos-test.arm/ $DISTS/repos-test_@REPOSVERSION@-@REPOSBUILD@.arm.deb
# currently build setup follows the repos version numbers
dpkg -b repos-build.arm/ $DISTS/repos-build_@REPOSVERSION@-@REPOSBUILD@.arm.deb
# eaccelerator version is the compiled release
dpkg -b eaccelerator.arm/ $DISTS/eaccelerator_$EACCELERATORVERSION.arm.deb

dpkg-scanpackages $DISTS /dev/null | gzip -9c > $DISTS/Packages.gz

echo "Build completed, updated pacakges are in $(pwd)/$DISTS"
svn status $DISTS
