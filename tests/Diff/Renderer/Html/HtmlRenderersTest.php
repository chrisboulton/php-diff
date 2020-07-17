<?php

declare(strict_types=1);

namespace Tests\Diff\Renderer\Html;

use jblond\Diff;
use jblond\Diff\Renderer\Html\Inline;
use jblond\Diff\Renderer\Html\SideBySide;
use jblond\Diff\Renderer\Html\Unified;
use PHPUnit\Framework\TestCase;

/**
 * Class HtmlRendererTest
 *
 * PHPUnit tests to verify that the output of the HTML renderers did not change due to code changes.
 *
 * @package         Tests\Diff\Renderer\Html
 * @author          Mario Brandt <leet31337@web.de>
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2020 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.0.0
 * @link            https://github.com/JBlond/php-diff
 */
class HtmlRenderersTest extends TestCase
{
    /**
     * @var bool Store the renderer's output in a file, when set to true.
     */
    private $genOutputFiles = false;

    /**
     * Constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        //$this->genOutputFiles = true;
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Test the output of the HTML Side by Side renderer.
     * @covers \jblond\Diff\Renderer\Html\SideBySide
     */
    public function testSideBySide()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer = new SideBySide();
        $result   = $diff->render($renderer);
        if ($this->genOutputFiles) {
            file_put_contents('htmlSideBySide.txt', $result);
        }

        $this->assertStringEqualsFile('tests/resources/htmlSideBySide.txt', $result);
    }

    /**
     * Test the output of the HTML Inline renderer.
     * @covers \jblond\Diff\Renderer\Html\Inline
     */
    public function testInline()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer = new Inline(
            [
                'format'        => 'html',
                'insertMarkers' => ['<ins>', '</ins>'],
                'deleteMarkers' => ['<del>', '</del>'],
            ]
        );
        $result   = $diff->render($renderer);
        if ($this->genOutputFiles) {
            file_put_contents('htmlInline.txt', $result);
        }

        $this->assertStringEqualsFile('tests/resources/htmlInline.txt', $result);
    }

    /**
     * Test the output of the HTML Unified renderer.
     * @covers \jblond\Diff\Renderer\Html\Unified
     */
    public function testUnified()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer = new Unified();
        $result   = $diff->render($renderer);
        if ($this->genOutputFiles) {
            file_put_contents('htmlUnified.txt', $result);
        }

        $this->assertStringEqualsFile('tests/resources/htmlUnified.txt', $result);
    }
}
