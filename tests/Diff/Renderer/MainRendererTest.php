<?php

declare(strict_types=1);

namespace Tests\Diff\Renderer;

use jblond\Diff;
use jblond\Diff\Renderer\MainRenderer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * PHPUnit Test for the main renderer of PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package     Tests\Diff\Renderer
 * @author      Mario Brandt <leet31337@web.de>
 * @author      Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2009 Mario Brandt
 * @license     New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version     2.2.1
 * @link        https://github.com/JBlond/php-diff
 */

/**
 * Class MainRendererTest
 * @package Tests\Diff\Renderer\Html
 */
class MainRendererTest extends TestCase
{

    /**
     * @var string[] Defines the main renderer options.
     */
    public $rendererOptions = [
        'format' => 'html',
    ];

    /**
     * MainRendererTest constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        //new \jblond\Autoloader();
        parent::__construct($name, $data, $dataName);
    }

    /**
     *
     */
    public function testRenderSimpleDelete()
    {
        $renderer       = new MainRenderer();
        $renderer->diff = new Diff(
            ['a'],
            []
        );
        $result         = $this->invokeMethod($renderer, 'renderSequences');
        static::assertEquals(
            [
                [
                    [
                        'tag'     => 'delete',
                        'base'    => [
                            'offset' => 0,
                            'lines'  => [
                                'a',
                            ],
                        ],
                        'changed' => [
                            'offset' => 0,
                            'lines'  => [],
                        ],
                    ],
                ],
            ],
            $result
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object     Instantiated object that we will run method on.
     * @param string  $methodName Method name to call
     * @param array   $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws \ReflectionException If the class doesn't exist.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     *
     */
    public function testRenderFixesSpaces()
    {
        $renderer       = new MainRenderer($this->rendererOptions);
        $renderer->diff = new Diff(
            ['    a'],
            ['a']
        );
        $result         = $this->invokeMethod($renderer, 'renderSequences');

        static::assertEquals(
            [
                [
                    [
                        'tag'     => 'replace',
                        'base'    => [
                            'offset' => 0,
                            'lines'  => [
                                "\0&nbsp;&nbsp;&nbsp;&nbsp;\1a",
                            ],
                        ],
                        'changed' => [
                            'offset' => 0,
                            'lines'  => [
                                "\0\1a",
                            ],
                        ],
                    ],
                ],
            ],
            $result
        );
    }
}
