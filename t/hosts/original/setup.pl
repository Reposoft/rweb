#!/usr/bin/perl -w

# derive $h so setup can be called from project root, test root and same folder 
my ($host) = __FILE__ =~ /(.*)setup.pl/;
my $h = $host.'../../hosts/original'; #no trailing slash
$h =~ s|hosts/original/../../||g; #to make output easier to read

print "Creating host structure in $h\n";
print `mkdir -p $h/repo $h/admin $h/backup`;
print "Creating empty user and access file for apache\n";
print `touch $h/admin/repos-users $h/admin/repos-access`;

print "Running php to reset Repos' testrepo\n";
print `php $h/reset.php`;

# repos-admin hooks are no longer active in testrepo setup because admin is a separate component
print 'Fetching users from repository without hook script';
print `mv $h/admin/repos-users $h/admin/repos-users.old`;
print `$h/repos-auth-reset.sh $h`;
print `cat $h/admin/repos-users.old >> $h/admin/repos-users`;
print `rm $h/admin/repos-users.old`;

