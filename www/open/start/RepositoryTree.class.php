<?php

/**
 * Represents the starting poins a user has in a repository.
 *
 * Limitations:
 * - Does not support groups that contain other groups
 * - Does not support repository prefix
 * - Does not return a marker for where access is denied from a subdir, for example "svensson = " when svensson has access to parent dir
 */
class RepositoryTree {
	
	// subversion ACL parsed as twodimensional array
	var $_acl = array();
	// username
	var $_user;
	// the user's group memberships
	var $_groups;
	// the entrypoints given to the user by this ACL, directly or through a group
	var $_entries;

	function RepositoryTree($aclFile, $username) {
		$this->_user = $username;
		$this->_acl = $this->_parseToAcl($aclFile, $this->_user);
		if ($this->_acl === false) {
			exit;
		}
		$this->_groups = $this->_getGroupsForUser($this->_acl, $this->_user);
		$this->_entries = $this->_getEntryPointsForUserOrGroup($this->_acl, $this->_user, $this->_groups);
	}
	
	/**
	* Helper for the constructor
	 * @return two-dimensional associative array with ACL[section][entry]
	 */
	function _parseToAcl($aclFile, $username) {
		if (!file_exists($aclFile)) {
			trigger_error("the ACL file does not exist", E_USER_ERROR);
		}
		$acl = parse_ini_file($aclFile, true);
		if (count($acl) < 2) {
			trigger_error("the ACL file must contain at least two sections, groups and a root path");
		}
		return $acl;
	}
	
	/**
	* Helper for the constructor
	 * @return array with the groups that the user is member of
	 */
	function _getGroupsForUser($acl, $username) {
		if (!isset($acl['groups'])) {
			return array();
		}
		$groups = array();
		$all = $acl['groups'];
		foreach ($all as $groupname => $group) {
			$gu = explode(',',$group);
			foreach ($gu as $u) {
				if (trim($u)==$username) $groups[] = $groupname;
			}
		}
		return $groups;
	}
	
	/**
	 * @returns the groups that the user belongs to, in an array
	 */
	function getGroups() {
		return $this->_groups;
	}
	
	/**
	 * an array of RepositoryEntryPoint for the user in the ACL given to the constructor
	 */
	function getEntryPoints() {
		return $this->_entries;
	}
	
	/**
	 * @return an array of RepositoryEntryPoint for the user
	 */
	function _getEntryPointsForUserOrGroup($acl, $username, $groups) {
		$e = array();
		foreach ($acl as $section => $accessrow) {
			if ($section == 'groups') continue;
			foreach ($accessrow as $id => $policy) {
				if (!$this->_isPolicy($policy)) continue;
				if (substr($id, 0, 1) == '@') {
					if (in_array(substr($id, 1), $groups)) {
						$e[] = new RepositoryEntryPoint($section, $policy, true);
					}
				} else if ($id == $username) {
					$e[] = new RepositoryEntryPoint($section, $policy, false);
				}
			}
		}
		return $e;
	}

	/**
	 * @return true if the argument is a valid access policy specification, such as 'r' or 'rw'
	 */
	function _isPolicy($accessString) {
		return ($accessString == 'r' || $accessString=='rw');
	}
}

/**
 * Immutable representation of a path where the user has access
 */
class RepositoryEntryPoint {
	var $path;
	var $readonly;
	var $bygroup;
	
	/**
	 * Constructor
	 * @param path the path starting with '/' but not ending with one
	 * @param accessString 'rw' or 'r' are currently the only accepted
	 * @param byGroupMembership true if the user got access through a group
	 */
	function RepositoryEntryPoint($path, $accessString, $byGroupMembership) { 
		if ($accessString == 'rw') {
			$this->readonly = false;
		} elseif ($accessString = 'r') {
			$this->readonly = true;
		} else {
			trigger_error("Access specified with the string '$accessString' is not valid");
			return false;
		}
		$this->path = $path; 
		$this->bygroup = $byGroupMembership;
	}
	
	/**
	 * @return true if the user can not modify this path
	 * There may still be entry points below rhat are readwrite
	 */
	function isReadOnly() {
		return $this->readonly;
	}
	
	/**
	 * @return true if the user got this access as member of a group that has access
	 */
	function isByGroup() {
		return $this->bygroup;
	}
	
	/**
	 * @return the path starting with '/' but not ending with one
	 */
	function getPath() {
		return $this->path;
	}
	
	/**
	 * @return the name of the top folder in the path (currently; migt be changed with metadata one day)
	 * this is often the project id
	 */
	function getDisplayname() {
		return substr($this->path, strrpos($this->path, '/')+1);
	}
}
?>