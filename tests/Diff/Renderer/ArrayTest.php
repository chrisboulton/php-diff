<?php
declare(strict_types=1);
namespace Tests\Diff\Renderer\Html;

require "../../../lib/Autoloader.php";

use jblond\Autoloader;
use jblond\Diff\Renderer\Html\HtmlArray;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayTest
 * @package Tests\Diff\Renderer\Html
 */
class ArrayTest extends TestCase
{

	/**
	 * ArrayTest constructor.
	 * @param null $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = null, array $data = [], $dataName = '')
	{

		parent::__construct($name, $data, $dataName);
		new Autoloader();
	}

	/**
	 *
	 */
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

	/**
	 *
	 */
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
