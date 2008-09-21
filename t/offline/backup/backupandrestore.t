#!/usr/bin/perl -w

use File::Temp;

print "# Running test from basedir ".`pwd`;
#$backup='../../../repos-backup';
# runs only from project basedir
$backup='repos-backup';
$suffix='index.php';
$svn='svn --non-interactive';

print "1..1\n";

$repofolder = File::Temp::tempdir(CLEANUP => 1);
$backupfolder = File::Temp::tempdir(CLEANUP => 1);

@c=`php $backup/repocreate/$suffix --repo-folder=$repofolder`;
print @c;

#import any kind of file to a new path
@s=`$svn import -m "first" $repofolder/hooks/post-commit.tmpl file://$repofolder/test/foo.md5`;
print @s;

#should fail, must end with slash: @b=`php $backup/store/$suffix --repo-folder=$repofolder/ --backup-folder=$backupfolder`;
@b=`php $backup/store/$suffix --repo-folder=$repofolder/ --backup-folder=$backupfolder/`;
print @b;

@v=`php $backup/verify/$suffix --backup-folder=$backupfolder/`;
print @v;

