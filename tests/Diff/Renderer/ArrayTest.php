<?php
namespace Tests\Diff\Renderer\Html;
use jblond\Autoloader;
use jblond\Diff\Renderer\Html\HtmlArray;
use PHPUnit\Framework\TestCase;

require "../../../lib/Autoloader.php";
new Autoloader();

class ArrayTest extends TestCase
{
	public function testRenderSimpleDelete()
	{
		$htmlRenderer = new HtmlArray();
		$htmlRenderer->diff = new \jblond\Diff(
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
		$htmlRenderer = new HtmlArray();
		$htmlRenderer->diff = new \jblond\Diff(
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
							'<del>&#xA0; &#xA0;</del>a',
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