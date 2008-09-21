#!/usr/bin/perl -w

die "Should run from basedir, not t/ folder\n" if -e './TEST.PL';

use File::Temp;

print "# Running test from basedir ".`pwd`;
#$backup='../../../repos-backup';
# runs only from project basedir
$backup='repos-backup';
$suffix='index.php';
$svn='svn --non-interactive';

print "1..6\n";

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

# Now imagine a terrible system failure

# Repository screwed up, restore from backup
$newrepofolder = File::Temp::tempdir(CLEANUP => 1);

@d=`php $backup/repocreate/$suffix --repo-folder=$newrepofolder`;
print @d;

@p=`php $backup/load/$suffix --repo-folder=$newrepofolder/ --backup-folder=$backupfolder/`;
#fails, grep for 'contains no files named repos-' in putput
my $prefix = lc($repofolder.'-'); #defaut prefix is lowercase and based on repository path
$prefix =~ s|.*/|repos-|;
@r=`php $backup/load/$suffix --repo-folder=$newrepofolder/ --backup-folder=$backupfolder/ --backup-prefix=$prefix`;
print @r;

@k=`php $backup/svnverify/$suffix --repo-folder=$newrepofolder/`;
print @k; # todo test for revision 1





