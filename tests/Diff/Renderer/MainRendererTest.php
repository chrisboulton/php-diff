<?php

/** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Tests\Diff\Renderer;

use jblond\Diff;
use jblond\Diff\Renderer\MainRenderer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * PHPUnit Test for the main renderer of PHP DiffLib.
 *
 * PHP version 7.3 or greater
 *
 * @package         Tests\Diff\Renderer
 * @author          Mario Brandt <leet31337@web.de>
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2009 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version        2.4.0
 * @link            https://github.com/JBlond/php-diff
 */

/**
 * Class MainRendererTest
 *
 * @package Tests\Diff\Renderer\Html
 */
class MainRendererTest extends TestCase
{

    /**
     * @var string[] Defines the main renderer options.
     */
    private $rendererOptions = [
        'format' => 'html',
    ];

    /**
     * Test if a sequence of version1 which is removed from version2 is caught by the MainRenderer.
     */
    public function testRenderSimpleDelete()
    {
        $renderer       = new MainRenderer();
        $renderer->diff = new Diff(['a'], []);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            [
                [
                    [
                        'tag'     => 'delete',
                        'base'    => [
                            'offset' => 0,
                            'lines'  => ['a'],
                        ],
                        'changed' => [
                            'offset' => 0,
                            'lines'  => [],
                        ],
                    ],
                ],
            ],
            $this->invokeMethod($renderer, 'renderSequences')
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param   object  $object      Instantiated object that we will run method on.
     * @param   string  $methodName  Method name to call
     * @param   array   $parameters  Array of parameters to pass into method.
     *
     * @return mixed The return value of the invoked method.
     * @throws ReflectionException If the class doesn't exist.
     */
    public function invokeMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Test if leading spaces of a sequence are replaced with html entities.
     */
    public function testRenderFixesSpaces()
    {
        $renderer       = new MainRenderer($this->rendererOptions);
        $renderer->diff = new Diff(
            ['    a'],
            ['a']
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
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
                            'lines'  => ["\0\1a"],
                        ],
                    ],
                ],
            ],
            $this->invokeMethod($renderer, 'renderSequences')
        );
    }

    /**
     * Test inline marking for changes at line level.
     *
     * Everything from the first difference to the last difference should be enclosed by the markers.
     *
     * @throws ReflectionException When invoking the method fails.
     */
    public function testMarkOuterChange()
    {
        $renderer = new MainRenderer();
        $text1    = ['one two three four'];
        $text2    = ['one tWo thrEe four'];
        $this->invokeMethod($renderer, 'markOuterChange', [&$text1, &$text2, 0, 1, 0]);
        $this->assertSame(["one t\0wo thre\1e four"], $text1);
        $this->assertSame(["one t\0Wo thrE\1e four"], $text2);
    }

    /**
     * Test inline marking for changes at character and word level.
     *
     * At character level, everything from a different character to any subsequent different character should be
     * enclosed by the markers.
     *
     * At word level, every word that is different should be enclosed by the markers.
     *
     * @throws ReflectionException When invoking the method fails.
     */
    public function testMarkInnerChange()
    {
        $renderer = new MainRenderer();

        // Character level.
        $renderer->setOptions(['inlineMarking' => $renderer::CHANGE_LEVEL_CHAR]);
        $text1 = ['one two three four'];
        $text2 = ['one tWo thrEe fouR'];
        $this->invokeMethod($renderer, 'markInnerChange', [&$text1, &$text2, 0, 1, 0]);
        $this->assertSame(["one t\0w\1o thr\0e\1e fou\0r\1"], $text1);
        $this->assertSame(["one t\0W\1o thr\0E\1e fou\0R\1"], $text2);

        // Word Level.
        $renderer->setOptions(['inlineMarking' => $renderer::CHANGE_LEVEL_WORD]);
        $text1 = ['one two three four'];
        $text2 = ['one tWo thrEe fouR'];
        $this->invokeMethod($renderer, 'markInnerChange', [&$text1, &$text2, 0, 1, 0]);
        $this->assertSame(["one \0two\1 \0three\1 \0four\1"], $text1);
        $this->assertSame(["one \0tWo\1 \0thrEe\1 \0fouR\1"], $text2);
    }
}
