<?php

declare(strict_types=1);

namespace Tests\Diff\Renderer\Text;

use PHPUnit\Framework\TestCase;
use jblond\Diff;
use jblond\Diff\Renderer\Text\Context;
use jblond\Diff\Renderer\Text\Unified;

/**
 * Class TextRendererTest
 *
 * PHPUnit tests to verify the output of the text renderers hasn't change by code changes.
 *
 * @package Tests\Diff\Renderer\Text
 */
class TextRendererTest extends TestCase
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

    public function testContext()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer   = new Context();
        $result     = $diff->render($renderer);
        //file_put_contents('out.txt', $result);

        $this->assertStringEqualsFile('tests/resources/textContext.txt', $result);
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

        $this->assertStringEqualsFile('tests/resources/textUnified.txt', $result);
    }
}
