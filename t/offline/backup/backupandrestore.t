#!/usr/bin/perl -w

use File::Temp;
use Data::Dumper;

print "# Running test from basedir ".`pwd`;
#$backup='../../../repos-backup';
# runs only from project basedir
$backup='repos-backup';
$suffix='index.php';
$svn='svn --non-interactive';

print "1..1\n";

$repofolder = File::Temp::tempdir();#CLEANUP => 1);
$backupfolder = File::Temp::tempdir(CLEANUP => 1);

@c=`php $backup/repocreate/$suffix --repo-folder=$repofolder`;
print Dumper(@c);

#makes verify fail:`echo "# test backup" > $backupfolder/repos-backup.md5`;
@s=`$svn import -m "first" $backupfolder/repos-backup.md5 file://$repofolder/test/foo.md5`;
print Dumper(@s);

#should fail, must end with slash: @b=`php $backup/store/$suffix --repo-folder=$repofolder/ --backup-folder=$backupfolder`;
@b=`php $backup/store/$suffix --repo-folder=$repofolder/ --backup-folder=$backupfolder/`;
print Dumper(@b);

@v=`php $backup/verify/$suffix --backup-folder=$backupfolder/`;
print Dumper(@v);

