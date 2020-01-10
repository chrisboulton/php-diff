<?php

declare(strict_types=1);

namespace Tests\Diff\Renderer\Html;

use PHPUnit\Framework\TestCase;
use jblond\Diff;
use jblond\Diff\Renderer\Html\Inline;
use jblond\Diff\Renderer\Html\SideBySide;
use jblond\Diff\Renderer\Html\Unified;

/**
 * Class HtmlRendererTest
 *
 * PHPUnit tests to verify the output of the HTML renderers hasn't change by code changes.
 *
 * @package Tests\Diff\Renderer\Html
 */
class HtmlRendererTest extends TestCase
{

    /**
     * Constructor.
     *
     * @param null      $name
     * @param array     $data
     * @param string    $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public function testSideBySide()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer   = new SideBySide();
        $result     = $diff->render($renderer);
        //file_put_contents('out.txt', $result);

        $this->assertStringEqualsFile('tests/resources/htmlSideBySide.txt', $result);
    }

    public function testInline()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer   = new Inline();
        $result     = $diff->render($renderer);
        //file_put_contents('out.txt', $result);

        $this->assertStringEqualsFile('tests/resources/htmlInline.txt', $result);
    }

    public function testUnified()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer   = new Unified();
        $result     = $diff->render($renderer);
        //file_put_contents('out.txt', $result);

        $this->assertStringEqualsFile('tests/resources/htmlUnified.txt', $result);
    }
}
