<?php
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

/*
use Config::IniFiles;
use strict;
use CGI;
my (
	$prog, $inifile, $form, $section, $param, $value,
	$name, $length,  $row,  @rows,    $input,
);
*/
$VERSION = "$Revision$";

# Configuration variables
$inifile = "/etc/httpd/auth/svn-access-file";    # Hardcoded for security
$prog    = "iniedit.php";                       # The name of this file

$config = parse_ini_file($inifile, true);

if ( $_REQUEST['change'] == "changeSection" ) {
	ChangePath( $_REQUEST, $inifile );
}

if ( $_REQUEST['change'] == "changeValues" ) {
	ChangeValues( $_REQUEST, $inifile );
}

if ( $_REQUEST['action'] == "editSection" ) {
	DisplaySection($_REQUEST);

}
if ( $_REQUEST['action'] == "addSection" ) {
	AddSection( $_REQUEST, $inifile );

}
if ( $_REQUEST['action'] == "deleteSection" ) {
	DeleteSection( $_REQUEST, $inifile );

}
if ( $_REQUEST['action'] == "editValues" ) {
	DisplayValues( $_REQUEST, $inifile );

}

# This should refresh the display of the config file.
$thbgcolor = "white";
$javascript = "
	function confirmDelete(section){
		return confirm(\"Are you sure you want to delete \" + section + \"?\");
	}
	";
$addSectionForm = "<h3>Add a new subversion path to the access list</h3>

<form name='addSectionForm' action='$prog'>
<input type='hidden' name='action' value='addSection'>
<input type='text' name='section'>
<input type='submit' name='submit' value='Add Repository Path'>
</form>";

start_html( 'Subversion Access List', $javascript );

print $addSectionForm . "\n";
print "<table>";

foreach ($config as $section => $entries) {
	$thbgcolor = "grey";
	$action1 = "";
	# We want to know if we should allow the user to delete this section
	# or not.
	if(!IsProtected($section)){
			$action1 = "<a href=\"$prog?action=deleteSection&section=$section\" onclick=\"return confirmDelete('$section.');\">Delete</a>";
	}
	# We want the groups section to stand out from the rest.
	if ($section == "groups"){
		$thbgcolor="grey";
	}
	$action2 = "<a href=\"$prog?action=editSection&section=$section\">Edit</a>";
	$row = "<tr><th colspan=\"2\" bgcolor=\"$thbgcolor\">" .
		$section . "<br> "
		  . $action1
		  . " "
		  . $action2
		  . "</th></tr>";
	foreach ($section as $param => $value) {
		$tdbgcolor = "lightblue";
		if($section == "groups"){
			$tdbgcolor = "white";
		}
		$row = "<tr><td align=\"right\" bgcolor=\"$tdbgcolor\"" .
			"<a href='" . $prog
			  . "?action=editValues&param="
			  . $param
			  . "&section="
			  . $section . "'>"
			  . $param . "</a>"
			  . "</td>";
		 	  . "<td align=\"left\" bgcolor=\"lightgrey\">"
			  . $value
			  . "</td></tr>";
	}    # End for param
}    #  End for sections

print "</table>";
end_html();
$config = '';

#==========================================================================================
#									End of Main
#==========================================================================================

#==========================================================================================
#									Begin Functions
#==========================================================================================
function start_html($title, $script) {
	print "
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	<html xmlns=\"http://www.w3.org/1999/xhtml\">"
	print "
	<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	<title>$title</title>
	</head>
	<script language=\"javascript\" type=\"text/javascript\">
	$script
	</script>
	<body>"
}
function end_html() {
	print "
	</body>
	</html>"
}
/**
 * write_ini_file function taken from PHP documentation of parse_ini_file
 */
if(!function_exists('write_ini_file')) {
  function write_ini_file($path, $assoc_array) {

   foreach($assoc_array as $key => $item) {
     if(is_array($item)) {
       $content .= "\n[{$key}]\n";
       foreach ($item as $key2 => $item2) {
         if(is_numeric($item2) || is_bool($item2))
           $content .= "{$key2} = {$item2}\n";
         else
           $content .= "{$key2} = \"{$item2}\"\n";
       }       
     } else {
       if(is_numeric($item) || is_bool($item))
         $content .= "{$key} = {$item}\n";
       else
         $content .= "{$key} = \"{$item}\"\n";
     }
   }       

   if(!$handle = fopen($path, 'w')) {
     return false;
   }

   if(!fwrite($handle, $content)) {
     return false;
   }

   fclose($handle);
   return true;

  }

}

function IsProtected($section_name){
	if($section_name == "groups"){
		return 1;
	}
	return 0;
}


function DisplayValues ( $input, $inifile ) {
	$param   = $input('param');
	$section = $input('section');
	$cfg     = parse_ini_file( $inifile, true );
	start_html( "Subversion Access Control::Edit Section" );

	print "<table>";
	print '<th colspan="2">Edit Parameter for ' . $section . '</th>';
	print '<form method="POST" action="' . $prog . '">';
	print '<input type="hidden" name="change" value="changeValues"/>';
	print '<input type="hidden" name="param" value="' . $param '"/>';
	print '<input type="hidden" name="section" value="' . section . '"/>';
	$value  = $cfg[$section][$param];
	$name   = 'value';
	$length = strlen($value) + 5;
	print "<input type='text' name='$name' value='$value' size='$length'/>";
	print "<tr><td align='right'>$param</td><td align='left'>$input</td></tr>";
	print "<tr><td colspan='2'><input type='submit'>Save</input></td></tr>";
	print "</form></table>";
	end_html();
	$cfg = '';
	exit 0;

}

function DisplaySection {
	my $input = shift;
	my $cfg    = Config::IniFiles->new( -file => $inifile );

	my @paramarr = ();
	my $section  = $input->param('section');
	print $input->header;
	print $input->start_html(
		-title => "Subversion Access Control::Edit Section" );

	print "<table>";
	print $input->start_form(
		-method => 'POST',
		-action => $prog
	);
	print '<input type="hidden" name="" value=""/>'
		-name  => 'change',
		-value => 'changeSection'
	);
	print '<input type="hidden" name="" value=""/>'
		-name  => 'section',
		-value => $section
	);

	print $input->h1( { -align => 'center' }, "Edit " . $section );
	print "\n";

	for $param ( $cfg->Parameters($section) ) {
		push @paramarr, $param;
	}    # End for param
	$value = join( ", ", @paramarr );
	$value =~ s/^,//g;
	$name   = 'params';
	$length = length($value) + 5;
	$input  = $input->textfield(
		-name  => $name,
		-value => $value,
		-size  => $length
	);
	$row =
	    $input->td( { -align => 'RIGHT' }, $section )
	  . $input->td( { -align => 'LEFT' },  $input );
	print $input->Tr($row);
	print "\n";
	@paramarr = '';

	$row = $input->td( { -colspan => '2' }, <input type='submit'>'Make changes') );
	print $input->Tr($row);
	print "</form></table>";

	print $input->end_html();
	$cfg = '';
	exit 0;

}

function ChangePath {

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

function AddSection {
	my ( $cgi, $inifile ) = @_;

	#We want to add a section to this sucker.
	my $cfg = Config::IniFiles->new( -file => $inifile )
  || die "Could not open the file: $!";
	
	my $newsection = $cgi->param('section');
	$cfg->AddSection($newsection);
	$cfg->RewriteConfig() || die "Could not rewrite the config file: $!";
}

function DeleteSection {
	my ( $cgi, $inifile ) = @_;
	my %ini;
	tie %ini, 'Config::IniFiles', ( -file => $inifile );
	delete $ini{ $cgi->param('section') };
	tied(%ini)->RewriteConfig() || die "Could not write settings to file: $!";
	untie %ini;

}

function ChangeValues {
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

?>