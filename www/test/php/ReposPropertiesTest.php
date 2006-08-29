<?php
require_once 'PHPUnit/Framework/TestCase.php';

require '../../conf/repos.properties.php';
 
class ReposPropertiesTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
		unset($_GET);
		unset($_SERVER);
		global $_GET, $_SERVER;
	}

	public function testGetSelfUrl() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '';
		$this->assertEquals('http://my.host', repos_getSelfUrl());
	}

	public function testGetSelfUrlS() {
		$_SERVER['SERVER_PORT'] = 443;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEquals('https://my.host/', repos_getSelfUrl());
	}
	
	public function testGetSelfUrlPort() {
		$_SERVER['SERVER_PORT'] = 123;
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEquals('http://my.host:123/', repos_getSelfUrl());
	}

	public function testGetSelfUrlPortS() {
		$_SERVER['SERVER_PORT'] = 123;
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/';
		$this->assertEquals('https://my.host:123/', repos_getSelfUrl());
	}

	public function testGetSelfUrlFile() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/index.html';
		$this->assertEquals('http://my.host/index.html', repos_getSelfUrl());
	}
	
	public function testGetSelfUrlPath() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/home/';
		$this->assertEquals('http://my.host/home/', repos_getSelfUrl());
	}
	
	public function testGetSelfUrlQ() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/test/?';
		$this->assertEquals('http://my.host/test/', repos_getSelfUrl());
	}

	public function testGetSelfUrlQuery() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/index.html?variable';
		$this->assertEquals('http://my.host/index.html', repos_getSelfUrl());
	}

	public function testGetSelfUrlQuery2() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['SERVER_NAME'] = 'my.host';
		$_SERVER['REQUEST_URI'] = '/test/?variable=value&another';
		$this->assertEquals('http://my.host/test/', repos_getSelfUrl());
	}
	
}
?>