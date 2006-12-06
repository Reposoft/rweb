<?php
/**
 * List the available tools for a project.
 * A project in the repository is defined as a folder that contains a "trunk/" folder.
 */

if (!isset($_GET['project'])) trigger_error('The "project" parameter must be set.', E_USER_ERROR);
$project = $_GET['project']; // urlencoded

// tool id => resource to check
$tools = array(
	'news' => 'messages/news.xml',
	'calendar' => 'calendar/'.$project.'.ics'
	);

?>