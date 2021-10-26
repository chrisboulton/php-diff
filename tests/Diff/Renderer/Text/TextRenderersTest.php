<?php

declare(strict_types=1);

namespace Tests\Diff\Renderer\Text;

use jblond\Diff;
use jblond\Diff\Renderer\Text\Context;
use jblond\Diff\Renderer\Text\Unified;
use PHPUnit\Framework\TestCase;

/**
 * Class TextRenderersTest
 *
 * PHPUnit tests to verify that the output of the text renderers did not change due to code changes.
 *
 * @package         Tests\Diff\Renderer\Text
 * @author          Mario Brandt <leet31337@web.de>
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2019 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version        2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class TextRenderersTest extends TestCase
{
    /**
     * @var bool Store the renderer's output in a file, when set to true.
     */
    private $genOutputFiles = false;

    /**
     * TextRenderersTest constructor.
     *
     * @param   null    $name
     * @param   array   $data
     * @param   string  $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        //$this->genOutputFiles = true;
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Test the output of the text-context renderer.
     *
     * @covers \jblond\Diff\Renderer\Text\Context
     */
    public function testContext()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer = new Context();
        $result   = $diff->render($renderer);
        if ($this->genOutputFiles) {
            file_put_contents('textContext.txt', $result);
        }

        $this->assertStringEqualsFile('tests/resources/textContext.txt', $result);
    }

    /**
     * Test the output of the text-unified renderer.
     *
     * @covers \jblond\Diff\Renderer\Text\Unified
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
            file_put_contents('textUnified.txt', $result);
        }

        $this->assertStringEqualsFile('tests/resources/textUnified.txt', $result);
    }

    /**
     * Test the output of the CLI text-context renderer.
     *
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
        $this->assertStringEqualsFile('tests/resources/textUnifiedCli.txt', $result);
    }

    /**
     * Test the output of the CLI text-inline renderer.
     *
     * @covers \jblond\Diff\Renderer\Text\InlineCli
     */
    public function testInlineCli()
    {
        $diff = new Diff(
            file_get_contents('tests/resources/a.txt'),
            file_get_contents('tests/resources/b.txt')
        );

        $renderer = new Diff\Renderer\Text\InlineCli(
            [
                'cliColor'        => true,
                'deleteMarkers'   => ['-', '-'],
                'insertMarkers'   => ['+', '+'],
                'equalityMarkers' => ['=', 'x'],
            ]
        );
        $result   = $diff->render($renderer);
        if ($this->genOutputFiles) {
            file_put_contents('textInlineCli.txt', $result);
        }
        $this->assertStringEqualsFile('tests/resources/textInlineCli.txt', $result);
    }
}
