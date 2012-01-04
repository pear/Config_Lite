#!/usr/bin/env php
<?php

/**
 * simple buildscript, see ./build -h
 *
 * declare a target is easy as prefixing a function with "target_"  
 * 
 * PHP version 5.2.6+
 *
 * @file      build
 * @author    Patrick C. Engel <info@pc-e.org>
 * @copyright 2011 info@pc-e.org
 * @license   MIT http://www.opensource.org/licenses/mit-license.html
 * @link      https://github.com/pce/config_lite
 */


function target_phpcs() 
{
	$cs = 'phpcs';
	// pear install phpDocumentor
	$target = 'Config/Lite.php';
	printf("== %s\n", 'Coding Standard');
	system($cs .' -v '.$target);
	printf("\n");
}

function target_test()
{
	$phpunit = 'phpunit';
	$test_target = 'tests/Config_LiteTest.php';
	printf("== %s\n", 'Unit Tests');
	system($phpunit . ' ' . $test_target); 
	printf("\n");
}

function target_doc() 
{
	printf("== %s\n", __FUNCTION__);
	$target = 'Config/Lite.php';
	system('phpdoc -o HTML:Smarty:PHP -f '.$target.' -t phpdocs');
	printf("\n");
}

function target_clean() 
{
	printf("== %s\n", __FUNCTION__);
	$quiet = ' 2> /dev/null';
	system('rm -r ./phpdocs'.$quiet);
	system('rm *.tgz'.$quiet);
	printf("\n");
}

function target_beautify() 
{
	printf("== %s\n", __FUNCTION__);
	// system('php_beautifier -f Config/Lite.php -o Config/Lite_.php');
	printf("\n");
}

function target_md()
{
	printf("== %s\n", __FUNCTION__);
	/* uses phpmd from http://phpmd.org/ */
	system('phpmd Config text codesize,unusedcode,naming,design');
        printf("\n");
}


function target_package()
{
	printf("== %s\n", __FUNCTION__);
	/* 
	# edited /usr/bin/pear
	# since i compiled env php without zlib:
	PHP_PEAR_PHP_BIN="/usr/bin/php"
	*/
	$pear = "/usr/bin/pear";
	system($pear.' package ./package.xml');
	printf("\n");
}

function target_pirum()
{
	printf("== %s\n", __FUNCTION__);
	$pear = '/usr/bin/pear';
	system($pear.' package ./pirum-package.xml');
	printf("\n");
}

function target_pirum_add()
{
	printf("== %s\n", __FUNCTION__);
	$package = isset($argv[2]) ? basename($argv[2]) : 'Config_Lite-0.1.0.tgz'; 
	system('pirum add pear '.$package);
	printf("\n");
}

function target_validate() 
{
	$pear = "/usr/bin/pear";
	printf("== %s\n", 'validate pear package');
	system('find ./ -name \'*.tgz\' -exec '
		. $pear .' package-validate "{}" \;');
	printf("\n");
}

function target_default()
{
	target_phpcs();
	target_test();
}

// ---------------------------------------------------------------------
// build script
// ---------------------------------------------------------------------

function print_usage($prg, $err=false)
{
	fprintf($err?STDERR:STDOUT, "Usage: %s [<target>]\n\n", $prg);
	$err && fprintf(STDERR, "    run with -h too see all targets\n\n");
	fprintf($err?STDERR:STDOUT, "    without any target, %s invokes the `task_default'.\n", $prg);
	fprintf($err?STDERR:STDOUT, "    Example: %s default\n\n", $prg);
	$err && exit(1);
}

function main($argc, $argv) 
{

	if ($argc == 1 ) {
		if (function_exists('target_default')) {
			target_default();
		} else {
			print_usage($argv[0], true);	
		}
	}

	if ($argc > 1) {
		$target = 'target_'.$argv[1];
		if (function_exists($target)) {
			$target();
		} elseif ($argv[1] == '-h' || $argv[1] == '--help') {
			print_usage($argv[0]);
			$avail_targets = array();
			$funcs = get_defined_functions();
			foreach ($funcs['user'] as $func) {
				if ('target_' === substr($func, 0, 7)) {
					$avail_targets[] = str_replace('target_', '', $func);
				}
			}
			printf("\n  %s\n\n", 'available targets:');
			foreach ($avail_targets as $option) {
				printf("    %s\n", $option);
			}
			printf("\n");
		} else {
			print_usage($argv[0], true);	
		}
	}
}

main($argc, $argv);
