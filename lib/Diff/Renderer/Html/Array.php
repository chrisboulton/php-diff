<?php
/**
 * Base renderer for rendering HTML based diffs for PHP DiffLib.
 *
 * PHP version 5
 *
 * Copyright (c) 2009 Chris Boulton <chris.boulton@interspire.com>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the Chris Boulton nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package DiffLib
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.1
 * @link http://github.com/chrisboulton/php-diff
 */

require_once dirname(__FILE__).'/../Abstract.php';

/**
 * Class Diff_Renderer_Html_Array
 */
class Diff_Renderer_Html_Array extends Diff_Renderer_Abstract
{
	/**
	 * @var array Array of the default options that apply to this renderer.
	 */
	protected $defaultOptions = array(
		'tabSize' => 4,
        'title_a' => 'Old Version',
        'title_b' => 'New Version',
	);

	/**
	* From https://gist.github.com/stemar/8287074
	* @param mixed $string The input string.
	* @param mixed $replacement The replacement string.
	* @param mixed $start If start is positive, the replacing will begin at the start'th offset into string.  If start is negative, the replacing will begin at the start'th character from the end of string.
	* @param mixed $length If given and is positive, it represents the length of the portion of string which is to be replaced. If it is negative, it represents the number of characters from the end of string at which to stop replacing. If it is not given, then it will default to strlen( string ); i.e. end the replacing at the end of string. Of course, if length is zero then this function will have the effect of inserting replacement into string at the given start offset.
	* @return string The result string is returned. If string is an array then array is returned.
	*/
	public function mb_substr_replace($string, $replacement, $start, $length=NULL) {
		if (is_array($string)) {
			$num = count($string);
			// $replacement
			$replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
			// $start
			if (is_array($start)) {
				$start = array_slice($start, 0, $num);
				foreach ($start as $key => $value)
					$start[$key] = is_int($value) ? $value : 0;
			}
			else {
				$start = array_pad(array($start), $num, $start);
			}
			// $length
			if (!isset($length)) {
				$length = array_fill(0, $num, 0);
			}
			elseif (is_array($length)) {
				$length = array_slice($length, 0, $num);
				foreach ($length as $key => $value)
					$length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
			}
			else {
				$length = array_pad(array($length), $num, $length);
			}
			// Recursive call
			return array_map(__FUNCTION__, $string, $replacement, $start, $length);
		}
		preg_match_all('/./us', (string)$string, $smatches);
		preg_match_all('/./us', (string)$replacement, $rmatches);
		if ($length === NULL) $length = mb_strlen($string);
		array_splice($smatches['0'], $start, $length, $rmatches[0]);
		return join($smatches['0']);
	}

	/**
	 * Render and return an array structure suitable for generating HTML
	 * based differences. Generally called by subclasses that generate a
	 * HTML based diff and return an array of the changes to show in the diff.
	 *
	 * @return array An array of the generated chances, suitable for presentation in HTML.
	 */
	public function render()
	{
		// As we'll be modifying a & b to include our change markers,
		// we need to get the contents and store them here. That way
		// we're not going to destroy the original data
		$a = $this->diff->getA();
		$b = $this->diff->getB();

		$changes = array();
		$opCodes = $this->diff->getGroupedOpcodes();
		foreach($opCodes as $group) {
			$blocks = array();
			$lastTag = null;
			$lastBlock = 0;
			foreach($group as $code) {
				list($tag, $i1, $i2, $j1, $j2) = $code;

				if($tag == 'replace' && $i2 - $i1 == $j2 - $j1) {
					for($i = 0; $i < ($i2 - $i1); ++$i) {
						$fromLine = $a[$i1 + $i];
						$toLine = $b[$j1 + $i];

						list($start, $end) = $this->getChangeExtent($fromLine, $toLine);
						if($start != 0 || $end != 0) {
							$realEnd = mb_strlen($fromLine) + $end;

							$fromLine = mb_substr($fromLine, 0, $start) . "\0" .
								mb_substr($fromLine, $start, $realEnd - $start) . "\1" . mb_substr($fromLine, $realEnd);

							$realEnd = mb_strlen($toLine) + $end;

							$toLine = mb_substr($toLine, 0, $start) .
								"\0" . mb_substr($toLine, $start, $realEnd - $start) . "\1" .
								mb_substr($toLine, $realEnd);

							$a[$i1 + $i] = $fromLine;
							$b[$j1 + $i] = $toLine;
						}
					}
				}

				if($tag != $lastTag) {
					$blocks[] = array(
						'tag' => $tag,
						'base' => array(
							'offset' => $i1,
							'lines' => array()
						),
						'changed' => array(
							'offset' => $j1,
							'lines' => array()
						)
					);
					$lastBlock = count($blocks)-1;
				}

				$lastTag = $tag;

				if($tag == 'equal') {
					$lines = array_slice($a, $i1, ($i2 - $i1));
					$blocks[$lastBlock]['base']['lines'] += $this->formatLines($lines);
					$lines = array_slice($b, $j1, ($j2 - $j1));
					$blocks[$lastBlock]['changed']['lines'] +=  $this->formatLines($lines);
				}
				else {
					if($tag == 'replace' || $tag == 'delete') {
						$lines = array_slice($a, $i1, ($i2 - $i1));
						$lines = $this->formatLines($lines);
						$lines = str_replace(array("\0", "\1"), array('<del>', '</del>'), $lines);
						$blocks[$lastBlock]['base']['lines'] += $lines;
					}

					if($tag == 'replace' || $tag == 'insert') {
						$lines = array_slice($b, $j1, ($j2 - $j1));
						$lines =  $this->formatLines($lines);
						$lines = str_replace(array("\0", "\1"), array('<ins>', '</ins>'), $lines);
						$blocks[$lastBlock]['changed']['lines'] += $lines;
					}
				}
			}
			$changes[] = $blocks;
		}
		return $changes;
	}

	/**
	 * Given two strings, determine where the changes in the two strings
	 * begin, and where the changes in the two strings end.
	 *
	 * @param string $fromLine The first string.
	 * @param string $toLine The second string.
	 * @return array Array containing the starting position (0 by default) and the ending position (-1 by default)
	 */
	private function getChangeExtent($fromLine, $toLine)
	{
		$start = 0;
		$limit = min(mb_strlen($fromLine), mb_strlen($toLine));
		while($start < $limit && mb_substr($fromLine, $start, 1) == mb_substr($toLine, $start, 1)) {
			++$start;
		}
		$end = -1;
		$limit = $limit - $start;
		while(-$end <= $limit && mb_substr($fromLine, $end, 1) == mb_substr($toLine, $end, 1)) {
			--$end;
		}
		return array(
			$start,
			$end + 1
		);
	}

	/**
	 * Format a series of lines suitable for output in a HTML rendered diff.
	 * This involves replacing tab characters with spaces, making the HTML safe
	 * for output, ensuring that double spaces are replaced with &nbsp; etc.
	 *
	 * @param array $lines Array of lines to format.
	 * @return array Array of the formatted lines.
	 */
	protected function formatLines($lines)
	{
		if ($this->options['tabSize'] !== false) {
			$lines = array_map(array($this, 'ExpandTabs'), $lines);
		}
		$lines = array_map(array($this, 'HtmlSafe'), $lines);
		foreach($lines as &$line) {
			$line = preg_replace_callback('# ( +)|^ #', array($this, 'fixSpaces'), $line);
		}
		return $lines;
	}

	/**
	 * Replace a string containing spaces with a HTML representation using &nbsp;.
	 *
	 * @param array $matches The string of spaces.
	 * @return string The HTML representation of the string.
	 */
	protected function fixSpaces($matches)
	{
		$buffer	= '';
		$count = 0;
		foreach($matches as $spaces){
			$count = strlen($spaces);
			if($count == 0) {
				continue;
			}
			$div = floor($count / 2);
			$mod = $count % 2;
			$buffer	.= str_repeat('&nbsp; ', $div).str_repeat('&#xA0;', $mod);
		}

		$div = floor($count / 2);
		$mod = $count % 2;
		return str_repeat('&#xA0; ', $div).str_repeat('&#xA0;', $mod);
	}

	/**
	 * Replace tabs in a single line with a number of spaces as defined by the tabSize option.
	 *
	 * @param string $line The containing tabs to convert.
	 * @return string The line with the tabs converted to spaces.
	 */
	private function expandTabs($line)
	{
		$tabSize	= $this->options['tabSize'];
		while(($pos = strpos($line, "\t")) !== FALSE){
			$left	= substr($line, 0, $pos);
			$right	= substr($line, $pos + 1);
			$length	= $tabSize - ($pos % $tabSize);
			$spaces	= str_repeat(' ', $length);
			$line	= $left.$spaces.$right;
		}
		return $line;
	}

	/**
	 * Make a string containing HTML safe for output on a page.
	 *
	 * @param string $string The string.
	 * @return string The string with the HTML characters replaced by entities.
	 */
	private function htmlSafe($string)
	{
		return htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
	}
}
