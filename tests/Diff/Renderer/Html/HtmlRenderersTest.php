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
 * PHPUnit tests to verify the output of the HTML renderers hasn't change by code changes.
 *
 * @package Tests\Diff\Renderer\Html
 */
class HtmlRendererTest extends TestCase
{
    /**
     * @var bool Store the renderer's output in a file, when set to true.
     */
    private $genOutputFiles = false;

    /**
     * Constructor.
     *
     * @param null      $name
     * @param array     $data
     * @param string    $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        //$this->genOutputFiles = true;
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
        if ($this->genOutputFiles) {
            file_put_contents('htmlSideBySide.txt', $result);
        }

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
        if ($this->genOutputFiles) {
            file_put_contents('htmlInline.txt', $result);
        }

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
        if ($this->genOutputFiles) {
            file_put_contents('htmlUnified.txt', $result);
        }

        $this->assertStringEqualsFile('tests/resources/htmlUnified.txt', $result);
    }
}
