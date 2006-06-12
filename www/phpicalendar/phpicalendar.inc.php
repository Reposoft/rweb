<?php
// Load this config file after the directives in default PHP icalendar contif.inc.php

// repos.se authentication
define('PARENT_DIR',substr(dirname(__FILE__), 0, strrpos(rtrim(strtr(dirname(__FILE__),'\\','/'),'/'),'/')));
require( PARENT_DIR."/login.inc.php" );
$username=getReposUser();
$password=getReposPass();
$invalid_login=false;

// repos.se custom settings
$language 				= 'Swedish';		// Language support - 'English', 'Polish', 'German', 'French', 'Dutch', 'Danish', 'Italian', 'Japanese', 'Norwegian', 'Spanish', 'Swedish', 'Portuguese', 'Catalan', 'Traditional_Chinese', 'Esperanto', 'Korean'
$week_start_day 		= 'Monday';			// Day of the week your week starts on
$timezone 				= '';				// Set timezone. Read TIMEZONES file for more information
$default_path			= $repos_config['repos_url'] + '/phpicalendar'; // The HTTP URL to the PHP iCalendar directory, ie. http://www.example.com/phpicalendar
$charset				= 'UTF-8';			// Character set your calendar is in, suggested UTF-8, or iso-8859-1 for most languages.

// Yes/No questions --- 'yes' means Yes, anything else means no. 'yes' must be lowercase.
$allow_webcals 			= 'yes';			// Allow http:// and webcal:// prefixed URLs to be used as the $cal for remote viewing of "subscribe-able" calendars. This does not have to be enabled to allow specific ones below.

// Calendar Caching (decreases page load times)
$save_parsed_cals 		= 'no';				// Saves a copy of the cal in /tmp after it's been parsed. Improves performence.
$tmp_dir				= '/tmp';			// The temporary directory on your system (/tmp is fine for UNIXes including Mac OS X). Any php-writable folder works.
$webcal_hours			= '24';				// Number of hours to cache webcals. Setting to '0' will always re-parse webcals.

// The calendar to view
$r_auth = getReposUser() . ':' . getReposPass() . '@';
$cal = $_COOKIE['repos-calendar'];
$list_webcals[0] = substr_replace($cal, "$r_auth", strpos($cal,'://')+3, 0);

?>