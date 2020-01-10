<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Html;

use jblond\Diff\Renderer\RendererAbstract;

/**
 * Base renderer for rendering HTML based diffs for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package jblond\Diff\Renderer\Html
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.15
 * @link https://github.com/JBlond/php-diff
 */
class HtmlArray extends RendererAbstract
{
    /**
     * @var array   Associative array containing the default options available for this renderer and their default
     *              value.
     *              - tabSize   The amount of spaces to replace a tab character with.
     *              - title_a   Title of the "old" version of text.
     *              - title_b   Title of the "new" version of text.
     */
    protected $defaultOptions = [
        'tabSize' => 4,
        'title_a' => 'Old Version',
        'title_b' => 'New Version',
    ];

    /**
     * Generate a string representation of changes between the "old and "new" sequences.
     *
     * This method is called by the renderers which extends this class.
     *
     * @param array             $changes        Contains the op-codes about the differences between "old and "new".
     * @param SideBySide|Inline $htmlRenderer   Renderer which extends this class.
     *
     * @return string HTML representation of the differences.
     */
    public function renderHtml(array $changes, object $htmlRenderer): string
    {
        if (empty($changes)) {
            //No changes between "old" and "new"
            return 'No differences found.';
        }

        $html = $htmlRenderer->generateTableHeader();

        foreach ($changes as $i => $blocks) {
            if ($i > 0) {
                // If this is a separate block, we're condensing code so output ...,
                // indicating a significant portion of the code has been collapsed as
                // it is the same.
                //TODO: When is $i > 0 ?
                $html .= '<span class="Skipped"><br>&hellip;<br></span>';
            }

            foreach ($blocks as $change) {
                $html .= '<tbody class="Change' . ucfirst($change['tag']) . '">';
                switch ($change['tag']) {
                    // Equal changes should be shown on both sides of the diff
                    case 'equal':
                        $html .= $htmlRenderer->generateTableRowsEqual($change);
                        break;
                    // Added lines only on the right side
                    case 'insert':
                        $html .= $htmlRenderer->generateTableRowsInsert($change);
                        break;
                    // Show deleted lines only on the left side
                    case 'delete':
                        $html .= $htmlRenderer->generateTableRowsDelete($change);
                        break;
                    // Show modified lines on both sides
                    case 'replace':
                        $html .= $htmlRenderer->generateTableRowsReplace($change);
                        break;
                }

                $html .= '</tbody>';
            }
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * Render and return an array structure suitable for generating HTML based differences.
     *
     * Generally called by classes which extend this class and that generate a HTML based diff by returning an array of
     * the changes to show in the diff.
     *
     * @return array An array of the generated changes, suitable for presentation in HTML.
     */
    public function render()
    {
        // "old" & "new" are copied so change markers can be added without modifying the original sequences.
        $old = $this->diff->getOld();
        $new = $this->diff->getNew();

        $changes = [];
        $opCodes = $this->diff->getGroupedOpcodes();

        foreach ($opCodes as $group) {
            $blocks     = [];
            $lastTag    = null;
            $lastBlock  = 0;
            foreach ($group as $code) {
                list($tag, $i1, $i2, $j1, $j2) = $code;

                if ($tag == 'replace' && $i2 - $i1 == $j2 - $j1) {
                    for ($i = 0; $i < ($i2 - $i1); ++$i) {
                        $fromLine   = $old[$i1 + $i];
                        $toLine     = $new[$j1 + $i];

                        list($start, $end) = $this->getChangeExtent($fromLine, $toLine);
                        if ($start != 0 || $end != 0) {
                            $realEnd        = mb_strlen($fromLine) + $end;
                            $fromLine       = mb_substr($fromLine, 0, $start) . "\0" .
                                              mb_substr($fromLine, $start, $realEnd - $start) . "\1" .
                                              mb_substr($fromLine, $realEnd);

                            $realEnd        = mb_strlen($toLine) + $end;
                            $toLine         = mb_substr($toLine, 0, $start) . "\0" .
                                              mb_substr($toLine, $start, $realEnd - $start) . "\1" .
                                              mb_substr($toLine, $realEnd);

                            $old[$i1 + $i]  = $fromLine;
                            $new[$j1 + $i]  = $toLine;
                        }
                    }
                }

                if ($tag != $lastTag) {
                    $blocks[]   = $this->getDefaultArray($tag, $i1, $j1);
                    $lastBlock  = count($blocks) - 1;
                }

                $lastTag = $tag;

                if ($tag == 'equal') {
                    $lines = array_slice($old, $i1, ($i2 - $i1));
                    $blocks[$lastBlock]['base']['lines'] += $this->formatLines($lines);

                    $lines = array_slice($new, $j1, ($j2 - $j1));
                    $blocks[$lastBlock]['changed']['lines'] +=  $this->formatLines($lines);
                } else {
                    if ($tag == 'replace' || $tag == 'delete') {
                        $lines = array_slice($old, $i1, ($i2 - $i1));
                        $lines = $this->formatLines($lines);
                        $lines = str_replace(array("\0", "\1"), array('<del>', '</del>'), $lines);
                        $blocks[$lastBlock]['base']['lines'] += $lines;
                    }

                    if ($tag == 'replace' || $tag == 'insert') {
                        $lines = array_slice($new, $j1, ($j2 - $j1));
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
     * Determine where changes in two strings begin and where they end.
     *
     * This returns an array.
     * The first value defines the first (starting at 0) character from start of the old string which is different.
     * The second value defines the last character from end of the old string which is different.
     *
     *
     * @param string $oldString The first string to compare.
     * @param string $newString The second string to compare.
     *
     * @return array Array containing the starting position (0 by default) and the ending position (-1 by default)
     */
    private function getChangeExtent(string $oldString, string $newString): array
    {
        $start = 0;
        $limit = min(mb_strlen($oldString), mb_strlen($newString));

        // Find first difference.
        while ($start < $limit && mb_substr($oldString, $start, 1) == mb_substr($newString, $start, 1)) {
            ++$start;
        }

        $end    = -1;
        $limit  = $limit - $start;

        // Find last difference.
        while (-$end <= $limit && mb_substr($oldString, $end, 1) == mb_substr($newString, $end, 1)) {
            --$end;
        }

        return [
            $start,
            $end + 1
        ];
    }

    /**
     * Format a series of strings which are suitable for output in a HTML rendered diff.
     *
     * This involves replacing tab characters with spaces, making the HTML safe for output by ensuring that double
     * spaces are replaced with &nbsp; etc.
     *
     * @param array $lines Array of strings to format.
     *
     * @return array Array of formatted strings.
     */
    protected function formatLines(array $strings): array
    {
        if ($this->options['tabSize'] !== false) {
            // Replace tabs with spaces.
            $strings = array_map(
                function ($item) {
                    return $this->expandTabs($item);
                },
                $strings
            );
        }

        // Convert special characters to HTML entities
        $strings = array_map(
            function ($item) {
                return $this->htmlSafe($item);
            },
            $strings
        );

        // Replace leading spaces of a line with HTML enities.
        foreach ($strings as &$line) {
            $line = preg_replace_callback(
                '/(^[ \0\1]*)/',
                function($matches) {
                    return str_replace(' ', "&nbsp;", $matches[0]);
                },
                $line
            );
        }
        unset($line);

        return $strings;
    }

    /**
     * Replace tabs in a string with an amount of spaces as defined by the tabSize option of this class.
     *
     * @param string $line The string which contains tabs to convert.
     *
     * @return string The line with the tabs converted to spaces.
     */
    private function expandTabs(string $line): string
    {
        return str_replace("\t", str_repeat(' ', $this->options['tabSize']), $line);
    }

    /**
     * Make a string HTML safe for output on a page.
     *
     * @param string $string The string to make safe.
     *
     * @return string The string with the HTML characters replaced by entities.
     */
    private function htmlSafe(string $string): string
    {
        return htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * Helper function that provides an array for the renderer with default values for the changes to render.
     *
     * @param string    $tag        Kind of difference.
     * @param integer   $lineInOld  Start of block in "old".
     * @param integer   $lineInNew  Start of block in "new".
     *
     * @return array
     */
    private function getDefaultArray(string $tag, int $lineInOld, int $lineInNew): array
    {
        return [
            'tag'       => $tag,
            'base'      => [
                'offset'    => $lineInOld,
                'lines'     => []
            ],
            'changed'   => [
                'offset'    => $lineInNew,
                'lines'     => []
            ]
        ];
    }
}
