#!/usr/bin/perl -w

my $basedir = `pwd`;
$basedir =~ s/\s+$//; # command output comes with ending newline

print "# Running test from basedir $basedir\n";

# or use File::Find?
#die "find $basedir/www -type f -name '*.test.php'";
my @tests = `find $basedir/www -type f -name '*.test.php'`;

my $count = @tests;

print "1..$count\n";

foreach (@tests) {
	# currently tests are written to run from their own folder (includes are relative)
	/(.*\/)([\w.]+)/; # match agains $_;
	$folder = $1;
	$file = $2;
	print "# test: $folder - $file\n";
	chdir $folder;
	print `php $file`;
	! $? or print "not ok # executionn of $file failed\n";
}

