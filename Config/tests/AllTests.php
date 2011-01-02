<?php

/**
 * A test class for running all Config_Lite unit tests.
 *
 * PHP version 5
 *
 * @category  Config
 * @package   Config_Lite
 * @author    Patrick C. Engel <info@pc-e.org>
 * @copyright 2010 Patrick C. Engel
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version   Release: @package_version@
 * @link      https://github.com/pce/config_lite
 */

require_once 'PHPUnit/Framework.php';

if (is_file(dirname(__FILE__).'/../Lite.php') === true) {
    // not installed.
    require_once dirname(__FILE__).'/../Lite.php';
} else {
    require_once 'Config/Lite.php';
}


/**
 * Test class for Config_Lite.
 * PHPUnit 3.3.17 by Sebastian Bergmann.
 *
 * Usage: phpunit AllTests.php
 *
 * @category  Config
 * @package   Config_Lite
 * @author    Patrick C. Engel <info@pc-e.org>
 * @copyright 2010 Patrick C. Engel
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version   Release: @package_version@
 * @link      https://github.com/pce/config_lite
 */
class Config_LiteTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    Config_Lite
	 * @access protected
	 */
	protected $object;

	/**
	 * Sets up the fixture,reads the Configuration file `test.cfg'.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		$this->config = new Config_Lite;
		$this->config->read('test.cfg');
	}

	/**
	 * Tears down the fixture, saves the Configuration to file `test.cfg'.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown()
	{
		// $this->config->save();
	}

	public function testWrite()
	{
		$assoc_array = array(
			'general' => array(
				'lang' => "de",
				'appname' => "ConfigTest"),
			'counter' => array(
				'count' => -1)
		);
		$filename = 'test.cfg';
		$this->config->write($filename, $assoc_array);
		$this->config->read($filename);
		$this->assertEquals('ConfigTest', $this->config->get('general', 'appname'));
		$this->assertEquals(-1, $this->config->get('counter', 'count'));
	}

	public function testSave()
	{
		$this->config->save();
		$this->assertEquals('ConfigTest', $this->config->get('general', 'appname'));
	}

	public function testRead()
	{
		$this->config->read('test.cfg');
		$this->assertEquals('ConfigTest', $this->config->get('general', 'appname'));
	}

	public function testGet()
	{
		// fallback to default given value 3
		$counter = $this->config->get('counter', 'nonexisting_counter_option', 3);
		$this->assertEquals(3, $counter);
		// without default
		$this->config->set('counter', 'count', 2);
		$counter = $this->config->get('counter', 'count');
		$this->assertEquals(2, $counter); 
		// exception test
		try {
			// expected to raise an Config_Lite_Exception
			$this->config->get('counter', 'counter');
		}
		catch (Config_Lite_Exception $expected) {
			return;
		}
		catch (Config_Lite_Exception $expected) {
			return;
		}
		
		$this->fail('An expected exception has not been raised.');
	}

	public function testSet()
	{
		$this->config->set('users', 'name', 'John Doe')
					 ->set('users', 'email', 'john@doe.com');
		$this->assertEquals(array('name'=>'John Doe','email'=>'john@doe.com'), $this->config->getSection('users'));
			
		// Invalid Argument. Exception test
		/*
		try {
			// expected to raise an exception
			$this->config->set('counter', array('count'), 1);
		}
		catch (Config_Lite_InvalidArgumentException $expected) {
			return;
		}
		$this->fail('An Config_Lite_Exception expected, due to an invalid Argument. Exception has not been raised.');
		*/
	}

	public function testSetSection()
	{	
		$this->config->setSection('users', array('email'=> 'john@doe.com','name'=> 'John Doe'));
		$this->assertEquals(array('name'=>'John Doe','email'=>'john@doe.com'), $this->config->getSection('users'));
	}

	public function testGetSection()
	{
		$this->config->set('users', 'name', 'John Doe')
					 ->set('users', 'email', 'john@doe.com');
		$this->assertEquals(array('name'=>'John Doe','email'=>'john@doe.com'), $this->config->getSection('users'));
	}
	
	public function testGetBool()
	{
		$this->config->set('general', 'stable', 'No');
		$this->assertEquals(FALSE, $this->config->getBool('general', 'stable'));
		$this->config->set('general', 'stable', 'Off');
		$this->assertEquals(FALSE, $this->config->getBool('general', 'stable'));
		$this->config->set('general', 'stable', FALSE);
		$this->assertEquals(FALSE, $this->config->getBool('general', 'stable'));
		$this->config->set('general', 'stable', 0);
		$this->assertEquals(FALSE, $this->config->getBool('general', 'stable'));
		
		$this->config->set('general', 'stable', 'Yes');
		$this->assertEquals(TRUE, $this->config->getBool('general', 'stable'));
		$this->config->set('general', 'stable', 'On');
		$this->assertEquals(TRUE, $this->config->getBool('general', 'stable'));
		$this->config->set('general', 'stable', TRUE);
		$this->assertEquals(TRUE, $this->config->getBool('general', 'stable'));
		$this->config->set('general', 'stable', 1);
		$this->assertEquals(TRUE, $this->config->getBool('general', 'stable'));
		
	}

	public function testAddComment()
	{
		$this->markTestIncomplete(
          'This test has not been implemented yet. Saving Comments is not implemented.'
        );
	}

	public function testGetComment()
	{
		$this->markTestIncomplete(
          'This test has not been implemented yet. Get Comments is not implemented.'
        );
	}
	
	public function testSetIndexedArrayWithSet()
	{
		$this->config->set('array_test', 'tries', array('12/09', '12/10', '11/07'));
		$this->assertEquals(array('12/09', '12/10', '11/07'), $this->config->get('array_test', 'tries'));
		$this->config->sync();
		$this->assertEquals(array('12/09', '12/10', '11/07'), $this->config->get('array_test', 'tries'));
	}

	public function testSetArrayAsKeyWithSet()
	{
		// exception test
		try {
			// expected to raise an Config_Lite_Exception
			$this->config->set('arraytestsection', array('key1', 'key2'), 'a_option');
		}
		catch (Config_Lite_InvalidArgumentException $expected) {
			return;
		}
		$this->fail('An expected exception has not been raised.');
	}
	
	public function testSingleQuotedEscapedInput()
	{
		$this->config->setString('quoted', 'single', '/(; "-"s[^\\\'"\\\']d//\\m\\\'"\'');
		$this->assertEquals('/(; "-"s[^\\\'"\\\']d//\\m\\\'"\'', $this->config->getString('quoted', 'single'));
	}
	
	public function testDoubleQuotedEscapedInput()
	{
		$this->config->setString('quoted', 'double', "/(; \"-\"s[^\'\"\']d//\\m\'\"'");
		$this->assertEquals("/(; \"-\"s[^\'\"\']d//\\m\'\"'", $this->config->getString('quoted', 'double'));
	}
	
	public function testSetNumericOptionWithSet()
	{
		$this->config->set('counter', 'count', 1);
		$this->assertEquals(1, $this->config->get('counter', 'count'));
	}

	public function testSetStringWithBlankWithSet()
	{
		$this->config->set('counter', 'user', 'John Doe');
		$this->assertEquals('John Doe', $this->config->get('counter', 'user'));
	}

	public function testSetBoolWithSet()
	{
		$this->config->set('counter', 'has_counter', TRUE);
		$this->assertEquals(TRUE, $this->config->get('counter', 'has_counter'));
	}
	
	public function testHasOption()
	{
		$this->config->set('counter', 'count', 1);
		$this->assertEquals(TRUE, $this->config->has('counter', 'count'));
	}


	public function testHasSection()
	{
		$this->assertEquals(TRUE, $this->config->hasSection('general'));
	}
	
	public function testRemove()
	{
		$this->config->set('general', 'stable', 'Yes');
		$this->assertEquals(TRUE, $this->config->getBool('general', 'stable'));
		$this->config->remove('general', 'stable');
		$this->assertEquals(FALSE, $this->config->has('general', 'stable'));
	}

	public function testHas()
	{
		$this->config->set('general', 'a_section', 'a_option_value');
		$this->assertEquals(TRUE, $this->config->has('general', 'a_section'));
		$this->config->remove('general', 'a_section');
		$this->assertEquals(FALSE, $this->config->has('general', 'a_section'));
	}
	
	public function testRemoveSection()
	{
		$this->config->removeSection('counter');
		$this->assertEquals(FALSE, $this->config->hasSection('counter'));
	}
}
