#!/usr/bin/perl -w

# derive $h so setup can be called from project root, test root and same folder 
my ($host) = __FILE__ =~ /(.*)setup.pl/;
my $rt = "$host../.."; #repos t root
$rt =~ s|hosts/multirepo/../..||g; #to make output easier to read
my $h = "$rt/hosts/multirepo";

# create repositories
print "Creating svn parent path in $h\n";
print `mkdir -p $h/svn`;

print `svnadmin create $h/svn/one` unless -e "$h/svn/one";
print `svnadmin create $h/svn/two` unless -e "$h/svn/two";

# create cache repositories
my $thumbs = "$h/repos-thumbs";
print `mkdir -p $thumbs`;
print `svnadmin create $thumbs/one` unless -e "$thumbs/one";

# setup hooks, currently for arbortext
use Cwd 'realpath';
my $repo = "$h/svn/abxevent";
my $hookscripts = realpath("$rt/../../cms/hookscripts");
my $logdir = realpath("$rt/logs");
my $shebang = '#!/bin/sh'; # without it you get "Warning: post-commit hook failed (exit code 255) with no output."
my $sh = '/usr/bin/python'; # there is no ENV when svn executes hooks
# indexing
my $hook = $sh.' "'.$hookscripts.'/indexing/index.py" --repository "$1" -r $2';
$hook .= ' --parentpath /svn/ --logfile "'.$logdir.'/reposhooks.log" --loglevel debug --cloglevel debug';
# hook script location
my $postcommit = "$repo/hooks/post-commit";
# http://perldoc.perl.org/functions/-X.html
if (-e $postcommit) {
	print "# Hook script $postcommit already exists\n";
} else {
	print "# Creating hook script $postcommit\n";
	open(F, ">>$postcommit");
	print F "$shebang\n";
	print F "# Repos hooks\n";
	# the actual hook, don't wait for it to complete
	print F "$hook &\n";
	print `chmod a+x $postcommit`;
}
