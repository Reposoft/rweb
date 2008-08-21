#!/usr/bin/perl
# template used for Apache::TestMB

use strict;
use warnings FATAL => 'all';

#why needed?#use lib qw(lib);

# ISSUE: Looks like /usr/lib/perl5/Apache/TestHarnessPHP.pm
# is hard coded to use Test::Harness 2.38, not newer like 3.13

use base 'Apache::TestRunPHP';

# Preparations
use Cwd;
my $cwd = getcwd;

die "Must run from the project basedir" unless -e "$cwd/t/TEST.PL";

my $modfile = "$cwd/t/mods-enabled.httpd_conf_extra";

system('cat /etc/apache2/mods-enabled/*.load '
	.'| grep -v perl '
	.'| grep -v cgi '
	."> $modfile");
system('cat /etc/apache2/mods-enabled/*.conf '
	.'| grep -v perl '
	.'| grep -v cgi '
	.">> $modfile");

# Now run Apache::Test config
my $T = main::->new;
# This project has no Module::Build script so we'll generate TEST from here
Apache::TestRunPHP->generate_script();
# Only do configure, because t/TEST should be used to run
$T->run(@ARGV, '-configure');

# Completed (see also the subs below), clean up
unlink $modfile;
# Delete meaningless startpage
unlink "$cwd/t/htdocs/index.html";
# Add repos loginscript to root
symlink "$cwd/www/_host/html/index.php", "$cwd/t/htdocs/index.php";

# Be a nice guy
print "Now run 't/TEST -start' to start server and 't/TEST -run' to test";


# Apache::TestConfig customizations
sub pre_configure {
	my $self = shift;

	# the additional input conf prepared above
	$self->{conf_opts}->{httpd_conf_extra} = "$modfile";

	$self->SUPER::pre_configure();
}

# could be useful, maybe to add new substitution variables for conf.in -> conf
sub new_test_config {
    my $self = shift;
    return $self->SUPER::new_test_config;
}
