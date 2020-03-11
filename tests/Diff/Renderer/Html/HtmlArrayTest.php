<?php

declare(strict_types=1);

namespace Tests\Diff\Renderer\Html;

use jblond\Diff;
use jblond\Diff\Renderer\Html\HtmlArray;
use PHPUnit\Framework\TestCase;

/**
 * Class HtmlArrayTest
 * @package Tests\Diff\Renderer\Html
 */
class HtmlArrayTest extends TestCase
{

    /**
     * HtmlArrayTest constructor.
     * @param null $name
     * @param array $data
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
        $htmlRenderer = new HtmlArray();
        $htmlRenderer->diff = new Diff(
            ['a'],
            []
        );
        $result = $htmlRenderer->render();
        static::assertEquals([
            [
                [
                    'tag' => 'delete',
                    'base' => [
                        'offset' => 0,
                        'lines' => [
                            'a'
                        ]
                    ],
                    'changed' => [
                        'offset' => 0,
                        'lines' => []
                    ]
                ]
            ]
        ], $result);
    }

    /**
     *
     */
    public function testRenderFixesSpaces()
    {
        $htmlRenderer = new HtmlArray();
        $htmlRenderer->diff = new Diff(
            ['    a'],
            ['a']
        );
        $result = $htmlRenderer->render();
        static::assertEquals([
            [
                [
                    'tag' => 'replace',
                    'base' => [
                        'offset' => 0,
                        'lines' => [
                            "<del>&nbsp;&nbsp;&nbsp;&nbsp;</del>a",
                        ]
                    ],
                    'changed' => [
                        'offset' => 0,
                        'lines' => [
                            '<ins></ins>a'
                        ]
                    ]
                ]
            ]
        ], $result);
    }
}
