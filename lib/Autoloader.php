<?php

namespace jblond;

/**
 * Autoloader Class
 *
 * Library to automatically include required files when a script calls a class.
 *
 * PHP version 7.2 or greater
 *
 * @package jblond
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.15
 * @link https://github.com/JBlond/php-diff
 */
class Autoloader
{

    /**
     * Constructor.
     *
     * A function is registered as an __autoload() implementation to include and evaluate the class file when this class
     * is called.
     */
    public function __construct()
    {
        spl_autoload_register(function ($class) {
            $class = str_replace('\\', '/', $class); // revert path for old PHP on Linux
            $dir = str_replace('\\', '/', __DIR__);
            if (file_exists($dir . '/' . $class . '.php')) {
                /** @noinspection PhpIncludeInspection */
                require_once $dir . '/' . $class . '.php';
            }
        });
    }
}
