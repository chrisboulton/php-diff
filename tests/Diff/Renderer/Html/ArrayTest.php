<?php

namespace Tests\Diff\Renderer\Html;

class ArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testRenderSimpleDelete()
    {
        $htmlRenderer = new \Diff_Renderer_Html_Array();
        $htmlRenderer->diff = new \Diff(
            array('a'),
            array()
        );

        $result = $htmlRenderer->render();

        static::assertEquals(array(
            array(
                array(
                    'tag' => 'delete',
                    'base' => array(
                        'offset' => 0,
                        'lines' => array(
                            'a'
                        )
                    ),
                    'changed' => array(
                        'offset' => 0,
                        'lines' => array()
                    )
                )
            )
        ), $result);
    }

    public function testRenderFixesSpaces()
    {
        $htmlRenderer = new \Diff_Renderer_Html_Array();
        $htmlRenderer->diff = new \Diff(
            array('    a'),
            array('a')
        );

        $result = $htmlRenderer->render();

        static::assertEquals(array(
            array(
                array(
                    'tag' => 'replace',
                    'base' => array(
                        'offset' => 0,
                        'lines' => array(
                            '<del>&nbsp; &nbsp;</del>a',
                        )
                    ),
                    'changed' => array(
                        'offset' => 0,
                        'lines' => array(
                            '<ins></ins>a'
                        )
                    )
                )
            )
        ), $result);
    }
}
