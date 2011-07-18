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

if (is_file(dirname(__FILE__).'/../Config/Lite.php') === true) {
    // not installed.
    require_once dirname(__FILE__).'/../Config/Lite.php';
} else {
    require_once 'Config/Lite.php';
}


/**
 * Test class for Config_Lite.
 * 
 * PHPUnit 3.3.17 by Sebastian Bergmann.
 * The first Tests relies on test.cfg,
 * followd by tests on a temporary file.
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
	 * temporary filename
	 * 
	 * @access protected
	 */
	protected $filename;

	/**
	 * Sets up the fixture,reads the Configuration file `test.cfg'.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		$this->config = new Config_Lite;
		$this->config->read(dirname(__FILE__).'/test.cfg');
		$this->filename = tempnam(sys_get_temp_dir(), __CLASS__);
		if (!is_writable($this->filename)) {
			printf('Warning: temporary file not writeable: %s.'."\n", 
				$this->filename
			);	
		}
	}

	/**
	 * Tears down the fixture, saves the Configuration to file `test.cfg'.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown()
	{
		file_exists($this->filename) && unlink($this->filename);
	}


	public function testRead()
	{
		$this->config->read(dirname(__FILE__).'/test.cfg');
		$this->assertEquals('ConfigTest', $this->config->get('general', 'app.name'));
	}

	public function testGet()
	{
		$this->config->read(dirname(__FILE__).'/test.cfg');
		$counter = $this->config->get('counter', 'count');
		$this->assertEquals(2, $counter);
		// fallback to default given value 3
		$counter = $this->config->get('counter', 'nonexisting_counter_option', 3);
		$this->assertEquals(3, $counter);
	}

	public function testGetDefaultByNonExistingSection()
	{
		$this->config->read(dirname(__FILE__).'/test.cfg');
		// fallback to default given value 3
		$counter = $this->config->get('foo', 'nonexisting_counter_option', 3);
		$this->assertEquals(3, $counter);
	}
		
	public function testGetGlobalOption()
	{
		$this->config->read(dirname(__FILE__).'/test.cfg');
		$this->assertEquals('test.cfg', $this->config['filename']);
		// global values with null
		$this->assertEquals('test.cfg', $this->config->get(null, 'filename'));
	}
	
	public function testGetBoolGlobalOption()
	{
		$this->config->read(dirname(__FILE__).'/test.cfg');
		$this->assertEquals(TRUE, $this->config->getBool(null, 'debug', false));
	}
	
	public function testWrite()
	{
		$assoc_array = array(
			'general' => array(
				'lang' => "de",
				'app.name' => "ConfigTest",
				'app.version' => '0.1.0'),
			'counter' => array(
				'count' => -1)
		);
		// write to temporary file
		$this->config->write($this->filename, $assoc_array);
		$this->config->setFilename($this->filename);
		$this->config->read();
		$this->assertEquals('ConfigTest', $this->config->get('general', 'app.name'));
		$this->assertEquals(-1, $this->config->get('counter', 'count'));
	}

	public function testSave()
	{
		$this->config->setFilename($this->filename);
		$this->config->set('counter', 'count', 2);
		$this->config->save();
		$this->config->read($this->filename);
		$this->assertEquals(2, $this->config->get('counter', 'count'));
	}
	
	public function testSetIndexedArrayWithSet()
	{
		$this->config->setFilename($this->filename);
		$this->config->read();
		$this->config->set('test array', 'tries', array('12/09', '12/10', '11/07'));
		$this->assertEquals(array('12/09', '12/10', '11/07'), $this->config->get('test array', 'tries'));
		$this->config->sync();
		$this->assertEquals(array('12/09', '12/10', '11/07'), $this->config->get('test array', 'tries'));
	}
	
	public function testArrayAccess()
	{	
		$this->config->setFilename($this->filename);
		$this->config->read();
		$this->config['server'] = array('basepath' => '/var/www');
		$this->assertEquals('/var/www', $this->config['server']['basepath']);
		// global values with null
		$this->config['filename'] = 'test.cfg';
		$this->assertEquals('test.cfg', $this->config['filename']);
		$this->config->sync();
		// global values with null
		$this->assertEquals('test.cfg', $this->config['filename']);
	}

	public function testSet()
	{
		$this->config->set('users', 'name', 'John Doe')
					 ->set('users', 'email', 'john@doe.com');
		$this->assertEquals(array('name'=>'John Doe','email'=>'john@doe.com'), $this->config->getSection('users'));
		// expected to raise an Invalid Argument exception, 
		// if Section is Array
		try {
			$this->config->set(array('counter' => 'count'), 1);
		}
		catch (Config_Lite_Exception_InvalidArgument $expected) {
			return;
		}
		$this->fail('An Config_Lite_Exception expected, due to an invalid Argument. Exception has not been raised.');
		// if Key is Array		
		try {
			$this->config->set('section', array('count' => 1));
		}
		catch (Config_Lite_Exception_InvalidArgument $expected) {
			return;
		}
		$this->fail('An Config_Lite_Exception expected, due to an invalid Argument. Exception has not been raised.');
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
		// test human readable representation
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

	public function testSetArrayAsKeyWithSet()
	{
		// exception test
		try {
			// expected to raise an Config_Lite_Exception
			$this->config->set('arraytestsection', array('key1', 'key2'), 'a_option');
		}
		catch (Config_Lite_Exception_InvalidArgument $expected) {
			return;
		}
		$this->fail('An expected exception has not been raised.');
	}
	
	public function testSingleQuotedEscapedInput()
	{
		$this->config->setFilename($this->filename);
		$this->config->setString('quoted', 'single', '/(; "-"s[^\\\'"\\\']d//m\\\'"\'');
		$this->config->sync();
		$this->assertEquals('/(; "-"s[^\\\'"\\\']d//m\\\'"\'', 
			$this->config->getString('quoted', 'single')
		);
	}
	
	public function testDoubleQuotedEscapedInput()
	{
		$this->config->setFilename($this->filename);
		$this->config->setString('quoted', 'double', "/(; \"-\"s[^\'\"\']d//m\'\"'");
		$this->config->sync();
		$this->assertEquals("/(; \"-\"s[^\'\"\']d//m\'\"'", 
			$this->config->getString('quoted', 'double')
		);
		$this->config->removeSection('quoted');
		$this->config->sync();
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
		$this->config->setFilename($this->filename);
		$this->config->set('counter', 'has_counter', TRUE);
		$this->config->sync();
		$this->assertEquals(1, $this->config->get('counter', 'has_counter'));
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

	/**
	 * to test protected methods 
	 */
	protected static function getMethod($name) 
	{
		$class = new ReflectionClass('Config_Lite');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	public function testNormalizeValue()
	{
		$m = self::getMethod('normalizeValue');
		$obj = new Config_Lite();
		
		$b = $m->invokeArgs($obj, array(true));
		$this->assertEquals('yes', $b);

		$d = $m->invokeArgs($obj, array(1234));
		$this->assertEquals(1234, $d);

		$s = $m->invokeArgs($obj, array('String'));
		$this->assertEquals('"String"', $s);
	}

    public function testCountSections()
    {
        $count = count($this->config);
        $this->assertEquals($count, 6);
    }

    public function testCountGlobal()
    {
        $this->config->setProcessSections(false)->read(dirname(__FILE__).'/test.cfg');
        $count = count($this->config);
        $this->assertEquals($count, 9);
    }

    public function testDoNotProcessSectionsGet()
    {
        $this->config->setProcessSections(false)->read(dirname(__FILE__).'/test.cfg');
        $counter = $this->config->get(null, 'count');
        $this->assertEquals(2, $counter);
        // fallback to default given value 3
        $counter = $this->config->get(null, 'nonexisting_counter_option', 3);
        $this->assertEquals(3, $counter);
    }
    
    public function testDoNotDoubleQuoteWrite()
    {
        $this->config->setQuoteStrings(false)->read($this->filename);
        $this->config->setFilename($this->filename);
		$this->config->setString('notquoted', 'nodouble', 'String');
		$this->config->sync();
		$this->assertEquals('String',
            $this->config->getString('notquoted', 'nodouble')
        );
		// back to default
		$this->config->setQuoteStrings(false);
    }

    public function testGetFilename()
    {
        $filename = dirname(__FILE__).'/test.cfg';
        $this->config->setFilename($filename);
        $this->assertEquals($filename, $this->config->getFilename());
    }

    public function testToStringMatchesWrite()
    {
        $this->config->setFilename($this->filename);
        $this->config->save();
        $this->assertEquals($this->config->__toString(), file_get_contents($this->filename));
    }
}
