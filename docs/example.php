<?php

// PEAR relies on the include path
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__.'/../');

// ; ---- Comment not compatible with hack ?

file_put_contents("test.cfg",  "
[general]
lang = 'en'

[db]
user = 'dionysis'
password = 'c2oiVnY!f8sf'

");


require_once __DIR__ . '/../Config/Lite.php';

$config = new Config_Lite('test.cfg');

echo $config->get('db', 'user');

// read with ArrayAccess
echo $config['db']['password'];

echo $config;

echo "---------------------\n";

$config = new Config_Lite();
$config->read('test.cfg');
$config->set('db', 'user', 'JohnDoe')
	->set('db', 'password', 'd0g1tcVs$HgIn1');

// set with ArrayAccess
$config['general'] = array('lang' => 'fr');

echo $config;

$config->save();

