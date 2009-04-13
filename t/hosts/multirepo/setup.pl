#!/usr/bin/perl -w

# derive $h so setup can be called from project root, test root and same folder 
my ($host) = __FILE__ =~ /(.*)setup.pl/;
my $h = $host.'../../hosts/multirepo'; #no trainling slash
$h =~ s|hosts/multirepo/../../||g; #to make output easier to read

print "Creating svn parent path in $h\n";
print `mkdir -p $h/repos`;

print `svnadmin create $h/repos/one`;
print `svnadmin create $h/repos/two`;

