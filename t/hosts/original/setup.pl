#!/usr/bin/perl -w

my $host = __FILE__;
$host =~ s|/[^/]*$||g;
$basedir = $host.'/../../..';
$h = "$basedir/t/hosts/original";

print `mkdir -p $h/repo $h/admin $h/backup`;
print `touch $h/admin/repos-users`;

print `php $h/setup.php`;

print `mv $h/admin/repos-users $h/admin/repos-users.old`;
print `$h/repos-auth-reset.sh $h`;
print `cat $h/admin/repos-users.old >> $h/admin/repos-users`;
print `rm $h/admin/repos-users.old`;

