#!/usr/bin/perl
#
#===================================================================
#  This script was adapted from iniedit.cgi created by Rich Bowen
#  (http://search.cpan.org/~rbow/).
#===================================================================
#  Adapted by: Bryan Simmons (bryan.simmons@gmail.com)
#  
#  Intended Use: For those who are tired of editing AuthzSVNAccessFiles by
#                hand.

use Config::IniFiles;
use strict;
use CGI;
my (
	$prog, $inifile, $form, $section, $param, $value,
	$name, $length,  $row,  @rows,    $input,
);
my $VERSION = qw($Revision: 0.1 $) [1];

# Configuration variables
$inifile = "/etc/httpd/auth/svn-access-file";    # Hardcoded for security
$prog    = "iniedit.pl";                       # The name of this file
my $config = Config::IniFiles->new( -file => $inifile )
  || die "Could not open the file: $!";
my $cgi = new CGI;

if ( $cgi->param('change') eq "changeSection" ) {
	ChangePath( $cgi, $inifile );
}

if ( $cgi->param('change') eq "changeValues" ) {
	ChangeValues( $cgi, $inifile );
}

if ( $cgi->param('action') eq "editSection" ) {
	DisplaySection($cgi);

}
if ( $cgi->param('action') eq "addSection" ) {
	AddSection( $cgi, $inifile );

}
if ( $cgi->param('action') eq "deleteSection" ) {
	DeleteSection( $cgi, $inifile );

}
if ( $cgi->param('action') eq "editValues" ) {
	DisplayValues( $cgi, $inifile );

}

# This should refresh the display of the config file.
$config->ReadConfig;
my $thbgcolor = "white";
my $javascript = "
	function confirmDelete(section){
		return confirm(\"Are you sure you want to delete \" + section + \"?\");
	}
	";
my $addSectionForm = "<h3>Add a new subversion path to the access list</h3>

<form name='addSectionForm' action='$prog'>
<input type='hidden' name='action' value='addSection'>
<input type='text' name='section'>
<input type='submit' name='submit' value='Add Repository Path'>
</form>";
print $cgi->header;
print $cgi->start_html( -title => 'Subversion Access List', -script => $javascript );

print $addSectionForm . "\n";
print "<table>";

for $section ( $config->Sections ) {
	$thbgcolor = "grey";
	my $action1 = "";
	# We want to know if we should allow the user to delete this section
	# or not.
	if(!IsProtected($section)){
			$action1 = $cgi->a(
			{ href => $prog . '?action=deleteSection&section=' . $section, -onClick => "return confirmDelete('".$section."');" },
			"Delete" );
	}
	# We want the groups section to stand out from the rest.
	if ($section eq "groups"){
		$thbgcolor="grey";
	}
	my $action2 = $cgi->a(
			{ href => $prog . '?action=editSection&section=' . $section },
			"Edit"
		  );
	$row = $cgi->th(
		{ -colspan => '2' , -bgcolor => $thbgcolor},
		$section . "<br> "
		  . $action1
		  . " "
		  . $action2
	);
	print $cgi->Tr($row);
	for $param ( $config->Parameters($section) ) {
		my $tdbgcolor = "lightblue";
		$value = $config->val( $section, $param );
		if($section eq "groups"){
			$tdbgcolor = "white";
		}
		$row = $cgi->td(
			{ -align => 'RIGHT', -bgcolor => $tdbgcolor},
			"<a href='" . $prog
			  . "?action=editValues&param="
			  . $param
			  . "&section="
			  . $section . "'>"
			  . $param . "</a>"
		  )
		  
		  . $cgi->td( { -align => 'LEFT', -bgcolor => 'lightgrey' }, $value );
		print $cgi->Tr($row);
	}    # End for param
}    #  End for sections


print "</table>";
print $cgi->end_html();
$config = '';

#==========================================================================================
#									End of Main
#==========================================================================================

#==========================================================================================
#									Begin Functions
#==========================================================================================
sub IsProtected{
	my $path = shift;
	if($path eq "groups"){
		return 1;
	}
	return 0;
}


sub DisplayValues {
	my ( $newcgi, $inifile ) = @_;
	my $param   = $newcgi->param('param');
	my $section = $newcgi->param('section');
	my $cfg     = Config::IniFiles->new( -file => $inifile );
	print $newcgi->header;
	print $newcgi->start_html(
		-title => "Subversion Access Control::Edit Section" );

	print "<table>";
	print $newcgi->th( { -colspan => '2' }, "Edit Parameter for " . $section );
	print $newcgi->start_form(
		-method => 'POST',
		-action => $prog
	);
	print $newcgi->hidden(
		-name  => 'change',
		-value => 'changeValues'
	);
	print $newcgi->hidden(
		-name  => 'param',
		-value => $param
	);
	print $newcgi->hidden(
		-name  => 'section',
		-value => $section
	);
	my $value  = $cfg->val( $section, $param );
	my $name   = 'value';
	my $length = length($value) + 5;
	my $input  = $newcgi->textfield(
		-name  => $name,
		-value => $value,
		-size  => $length
	);
	my $row =
	    $newcgi->td( { -align => 'RIGHT' }, $param )
	  . $newcgi->td( { -align => 'LEFT' },  $input );
	print $newcgi->Tr($row);
	print "\n";
	$row = $newcgi->td( { -colspan => '2' }, $newcgi->submit('Save') );
	print $newcgi->Tr($row);
	print "</form></table>";

	print $newcgi->end_html();
	$cfg = '';
	exit 0;

}

sub DisplaySection {
	my $newcgi = shift;
	my $cfg    = Config::IniFiles->new( -file => $inifile );

	my @paramarr = ();
	my $section  = $newcgi->param('section');
	print $newcgi->header;
	print $newcgi->start_html(
		-title => "Subversion Access Control::Edit Section" );

	print "<table>";
	print $newcgi->start_form(
		-method => 'POST',
		-action => $prog
	);
	print $newcgi->hidden(
		-name  => 'change',
		-value => 'changeSection'
	);
	print $newcgi->hidden(
		-name  => 'section',
		-value => $section
	);

	print $newcgi->h1( { -align => 'center' }, "Edit " . $section );
	print "\n";

	for $param ( $cfg->Parameters($section) ) {
		push @paramarr, $param;
	}    # End for param
	$value = join( ", ", @paramarr );
	$value =~ s/^,//g;
	$name   = 'params';
	$length = length($value) + 5;
	$input  = $newcgi->textfield(
		-name  => $name,
		-value => $value,
		-size  => $length
	);
	$row =
	    $newcgi->td( { -align => 'RIGHT' }, $section )
	  . $newcgi->td( { -align => 'LEFT' },  $input );
	print $newcgi->Tr($row);
	print "\n";
	@paramarr = '';

	$row = $newcgi->td( { -colspan => '2' }, $newcgi->submit('Make changes') );
	print $newcgi->Tr($row);
	print "</form></table>";

	print $newcgi->end_html();
	$cfg = '';
	exit 0;

}

sub ChangePath {

	# Use the hash interface of the cfg object to copy the
	# entire parameter lists into the cfg hash.

	my ( $cgi, $inifile ) = @_;

	my ( $key, $section, $param, @fields, $field );

	# Put the ini file in the hash.
	my %ini;
	tie %ini, 'Config::IniFiles', ( -file => $inifile );

	# Now get the cgi params out and put them into the hash.
	# Put the parameters for the section into a temp hash

	$section = $cgi->param('section');

	# Each field is a section.  The value of the field will
	# hold a string of parameters seperated by a comma.

	my @params = split( /,/, $cgi->param("params") );
	for (my $i = 0; $i <= $#params; $i++){
		#get rid of trailing or leading spaces.
		$params[$i] =~ s/\s//g;
	}
	my %parameters = {};
	%parameters = %{ $ini{$section} };

	# Seperate the parameters from the values
	my @keys = keys %parameters;

	# Check to see if any parameters were deleted.
	for $key (@keys) {
		my $keycheck = 0;
		for $param (@params) {

			if ( $key eq $param ) {
				$keycheck = 1;
			}
		}
		if ( $keycheck == 0 ) {

			#This key was removed, delete it.

			delete $parameters{$key};

		}
	}
	for $param (@params) {
		my $paramcheck = 0;
		for $key (@keys) {
			if ( $key eq $param ) {
				$paramcheck = 1;
			}
		}
		if ( $paramcheck == 0 ) {

			#This param was added, put it in.

			$parameters{$param} = "";

		}
	}

	#Now reassign the parameters to the config

	$ini{$section} = {};
	%{ $ini{$section} } = %parameters;

	################################################
	#		print $cgi->h1("FINAL HASH PRINTING");
	#				@fields = keys %ini;
	#		foreach $key (@fields){
	#			my @testparam = keys %{$ini{$key}};
	#			my $subkey;
	#			my $subparam;
	#		print $cgi->p($key.":<br>");
	#			foreach $subkey (@testparam){
	#				$subparam = $ini{$key}{$subkey};
	#				print $cgi->p( $subkey ."=". $subparam."<br>");
	#			}
	#		}
	###############################################

	tied(%ini)->RewriteConfig() || die "Could not write settings to file: $!";

	#tied(%ini)->ReadConfig()    || die "Could not reread the config file!! $!";
	untie(%ini);
}

sub AddSection {
	my ( $cgi, $inifile ) = @_;

	#We want to add a section to this sucker.
	my $cfg = Config::IniFiles->new( -file => $inifile )
  || die "Could not open the file: $!";
	
	my $newsection = $cgi->param('section');
	$cfg->AddSection($newsection);
	$cfg->RewriteConfig() || die "Could not rewrite the config file: $!";
}

sub DeleteSection {
	my ( $cgi, $inifile ) = @_;
	my %ini;
	tie %ini, 'Config::IniFiles', ( -file => $inifile );
	delete $ini{ $cgi->param('section') };
	tied(%ini)->RewriteConfig() || die "Could not write settings to file: $!";
	untie %ini;

}

sub ChangeValues {
	my ( $cgi, $inifile ) = @_;
	my ( $section, $param, $value );
	my $cfg = Config::IniFiles->new( -file => $inifile );
	$section = $cgi->param('section');
	$param   = $cgi->param('param');
	$value   = $cgi->param('value');

	$cfg->setval( $section, $param, $value );

	$cfg->RewriteConfig;
	$cgi->delete_all();
}    #  End sub ChangeValues

=head1 NAME

iniedit.pl - Interface for editing a subversion ini-style AuthzSVNAccessFile
from the web.

=head1 DESCRIPTION

Generates a HTML form containing the sections and values from a
subversion authzsvnaccessfile, and lets you modify them.  The 
script has no knowledge or interaction with any htaccess-style 
group or user files.

=head1 README

Generates a HTML form containing the sections and values from a
subversion authzsvnaccessfile, and lets you modify them.  The 
script has no knowledge or interaction with any htaccess-style 
group or user files.

=head1 PREREQUISITES

	C<Config::IniFiles>, C<CGI.pm>

=pod OSNAMES

Any

=pod SCRIPT CATEGORIES

CGI

=cut

