#!/usr/bin/perl -w

my $host = __FILE__;
$host =~ s|/[^/]*$||g;
$basedir = $host.'/../../..';

print `php $basedir/t/hosts/original/setup.php`;

print `mv $basedir/t/hosts/original/admin/repos-users $basedir/t/hosts/original/admin/repos-users.old`;
print `$basedir/t/hosts/original/repos-auth-reset.sh $basedir/t/hosts/original`;
print `cat $basedir/t/hosts/original/admin/repos-users.old >> $basedir/t/hosts/original/admin/repos-users`;
print `rm $basedir/t/hosts/original/admin/repos-users.old`;

