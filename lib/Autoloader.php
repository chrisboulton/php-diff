<?php
namespace jblond;

/**
 * Class Autoloader
 * @package jblond
 */
class Autoloader
{

	/**
	 * Autoloader constructor.
	 */
	public function __construct()
	{
		spl_autoload_register(array($this, '__autoload'));
	}

	/**
	 * @param string $class
	 */
	private function __autoload($class)
	{
		$class = str_replace('\\', '/', $class); // revert path for old PHP on Linux
		$dir = str_replace('\\', '/', __DIR__);
		if (file_exists($dir . '/' . $class . '.php')) {
			 /** @noinspection PhpIncludeInspection */
			require $dir . '/' . $class . '.php';
		}
	}
}
