<?php

declare(strict_types=1);

namespace Tests\Diff\Renderer\Text;

use jblond\Diff;
use jblond\Diff\Renderer\Text\Context;
use jblond\Diff\Renderer\Text\Unified;
use PHPUnit\Framework\TestCase;

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

    /**
     * Test context
     * @covers \jblond\Diff\Renderer\Text\Context
     */
    public function testContext()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer   = new Context();
        $result     = $diff->render($renderer);
        if ($this->genOutputFiles) {
            file_put_contents('textContext.txt', $result);
        }

        $this->assertStringEqualsFile('tests/resources/textContext.txt', $result);
    }

    /**
     * Test Unified
     * @covers \jblond\Diff\Renderer\Text\Unified
     */
    public function testUnified()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer   = new Unified();
        $result     = $diff->render($renderer);
        if ($this->genOutputFiles) {
            file_put_contents('textUnified.txt', $result);
        }

        $this->assertStringEqualsFile('tests/resources/textUnified.txt', $result);
    }

    /**
     * Test Unified Cli
     * @covers \jblond\Diff\Renderer\Text\UnifiedCli
     */
    public function testUnifiedCli()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer = new Diff\Renderer\Text\UnifiedCli();
        $result   = $diff->render($renderer);
        if ($this->genOutputFiles) {
            file_put_contents('textUnifiedCli.txt', $result);
        }
        $this->assertStringEqualsFile('tests/resources/ab.diff', $result);
    }
}

