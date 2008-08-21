
use strict;
use warnings;

use Apache::Test;
use Apache::TestRequest 'GET_BODY';

plan tests => 1, need_module('mod_status.c');

my $status = GET_BODY '/server-status';

ok $status =~ /uptime/;

1;


