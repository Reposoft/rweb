<?php

/**
 * Represents the starting poins a user has in a repository.
 */
class RepositoryTree {
	
	// subversion ACL parsed as twodimensional array
	var $_acl = array();
	// username
	var $_user;
	// the user's memberships
	var $_groups;

	function RepositoryTree($aclFile, $username) {
		$this->_user = $username;
		$this->_acl = $this->_parseToAcl($aclFile, $this->_user);
		if ($this->_acl === false) {
			exit;
		}
		$this->_groups = $this->_getGroupsForUser($this->_acl, $this->_user);
	}
	
	/**
	* Helper for the constructor
	 * @return two-dimensional associative array with ACL[section][entry]
	 */
	function _parseToAcl($aclFile, $username) {
		if (!file_exists($aclFile)) {
			trigger_error("the ACL file does not exist");
			return false;
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
	 * @return an array of RepositoryEntryPoint for the user
	 */
	function getEntryPoints() {
	
	}

}

class RepositoryEntryPoint {
	var $path;
	var $readonly;
	
	function RepositoryEntryPoint($path) {
		$this->path = $path;
	}
	
	function setReadOnly($flag) {
		$this->readonly = $flag;
	}
	
	function isReadOnly() {
		return $this->readonly;
	}
	
	function getPath() {
		return $this->path;
	}
	
	function getDisplayname() {
		return substr($this->path, strrpos($this->path, '/')+1);
	}
}
?>