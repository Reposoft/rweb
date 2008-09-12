#!perl
# template used for Apache::TestMB

use strict;
use warnings FATAL => 'all';

#why needed?#use lib qw(lib);

# ISSUE: Looks like /usr/lib/perl5/Apache/TestHarnessPHP.pm
# is hard coded to use Test::Harness 2.38, not newer like 3.13
# Also TestRunPHP uses its own hardcoded php.ini which we can't use
# TODO: Try to solve the TestHarnessPHP issue and use our own PHPRC env to 
# choose php.ini per host (see TestRunPHP source), and then use TestRunPHP again.
#use base 'Apache::TestRunPHP';
use base 'Apache::TestRunPerl';

my $T = main::->new;

# This project has no Module::Build script so we'll generate TEST from here
Apache::TestRunPerl->generate_script();

$T->run(@ARGV);

# Apache::TestConfig customizations

sub pre_configure {
	my $self = shift;
	$self->SUPER::pre_configure();
}

# subclass new_test_config to add some config vars which will be
# replaced in generated httpd.conf
sub new_test_config {
    my $self = shift;
    return $self->SUPER::new_test_config;
}
