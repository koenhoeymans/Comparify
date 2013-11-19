<?php

/**
 * @package Comparify
 */

/**
 * Loads the files for the Comparify library.
 */
function Comparify_Autoload($className)
{
	$classNameFile = __DIR__
		. DIRECTORY_SEPARATOR . '..'
		. DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className)
		. '.php';

	if (file_exists($classNameFile))
	{
		require_once $classNameFile;
	}
}

spl_autoload_register('Comparify_Autoload');

if (file_exists(__DIR__
		. DIRECTORY_SEPARATOR . '..'
		. DIRECTORY_SEPARATOR . 'vendor'
		. DIRECTORY_SEPARATOR . 'autoload.php'))
{
	require_once(__DIR__
		. DIRECTORY_SEPARATOR . '..'
		. DIRECTORY_SEPARATOR . 'vendor'
		. DIRECTORY_SEPARATOR . 'autoload.php');
}