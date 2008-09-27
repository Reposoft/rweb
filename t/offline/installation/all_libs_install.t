#!/usr/bin/perl -w

my $basedir = `pwd`;
$basedir =~ s/\s+$//; # command output comes with ending newline

print "# Running test from basedir $basedir\n";

my @installs = `find $basedir/www/lib -mindepth 2 -maxdepth 2 -type f -name 'install.php'`;

my $count = @installs;

print "1..$count\n";

foreach (@installs) {
	# currently installation scripts are written to run from their own folder (includes are relative)
	/(.*\/)([\w.]+)/; # match agains $_;
	$folder = $1;
	$file = $2;
	print "# Install: $folder - $file\n";
	chdir $folder;
	print `php $file`;
	! $? or print "not ok # $file failed\n";
}

