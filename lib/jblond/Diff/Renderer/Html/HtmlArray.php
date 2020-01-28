<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Html;

use jblond\Diff\Renderer\RendererAbstract;

/**
 * Base renderer for rendering HTML based diffs for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package       jblond\Diff\Renderer\Html
 * @author        Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license       New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version       1.15
 * @link          https://github.com/JBlond/php-diff
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
     * @var string The last operation which was recorded in the array which contains the changes, used by the renderer.
     * @see HtmlArray::appendChangesArray()
     */
    private $lastTag;

    /**
     * Generate a string representation of changes between the "old and "new" sequences.
     *
     * This method is called by the renderers which extends this class.
     *
     * @param array  $changes      Contains the op-codes about the differences between "old and "new".
     * @param object $htmlRenderer Renderer which extends this class.
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
                // If this is a separate block, we're condensing code to output …,
                // indicating a significant portion of the code has been collapsed as it did not change.
                $html .= $htmlRenderer->generateTableRowsSkipped();
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
        // The old and New texts are copied so change markers can be added without modifying the original sequences.
        $oldText = $this->diff->getOld();
        $newText = $this->diff->getNew();

        $changes = [];

        foreach ($this->diff->getGroupedOpCodes() as $group) {
            $blocks        = [];
            $this->lastTag = null;

            foreach ($group as $code) {
                list($tag, $startOld, $endOld, $startNew, $endNew) = $code;
                /**
                 * $code is an array describing a op-code which includes:
                 * 0 - The type of tag (as described below) for the op code.
                 * 1 - The beginning line in the first sequence.
                 * 2 - The end line in the first sequence.
                 * 3 - The beginning line in the second sequence.
                 * 4 - The end line in the second sequence.
                 *
                 * The different types of tags include:
                 * replace - The string from $startOld to $endOld in $oldText should be replaced by
                 *           the string in $newText from $startNew to $endNew.
                 * delete  - The string in $oldText from $startOld to $endNew should be deleted.
                 * insert  - The string in $newText from $startNew to $endNew should be inserted at $startOld in
                 *           $oldText.
                 * equal   - The two strings with the specified ranges are equal.
                 */

                $blockSizeOld = $endOld - $startOld;
                $blockSizeNew = $endNew - $startNew;

                if (($tag == 'replace') && ($blockSizeOld == $blockSizeNew)) {
                    // Inline differences between old and new block.
                    $this->markInlineChange($oldText, $newText, $startOld, $endOld, $startNew);
                }

                $lastBlock = $this->appendChangesArray($blocks, $tag, $startOld, $startNew);

                // Extract the block from both the old and new text and format each line.
                $oldBlock = $this->formatLines(array_slice($oldText, $startOld, $blockSizeOld));
                $newBlock = $this->formatLines(array_slice($newText, $startNew, $blockSizeNew));

                if ($tag == 'equal') {
                    // Old block equals New block
                    $blocks[$lastBlock]['base']['lines']    += $oldBlock;
                    $blocks[$lastBlock]['changed']['lines'] += $newBlock;
                    continue;
                }

                if ($tag == 'replace' || $tag == 'delete') {
                    // Inline differences or old block doesn't exist in the new text.
                    // Replace the markers, which where added above, by HTML delete tags.
                    $oldBlock                            = str_replace(["\0", "\1"], ['<del>', '</del>'], $oldBlock);
                    $blocks[$lastBlock]['base']['lines'] += $oldBlock;
                }

                if ($tag == 'replace' || $tag == 'insert') {
                    // Inline differences or the new block doesn't exist in the old text.
                    // Replace the markers, which where added above, by HTML insert tags.
                    $newBlock                               = str_replace(["\0", "\1"], ['<ins>', '</ins>'], $newBlock);
                    $blocks[$lastBlock]['changed']['lines'] += $newBlock;
                }
            }
            $changes[] = $blocks;
        }

        return $changes;
    }

    /**
     * Add markers around inline changes between old and new text.
     *
     * Each line of the old and new text is evaluated.
     * When a line of old differs from the same line of new, a marker is inserted into both lines, just before the first
     * different character. A second marker is added just behind the last character which differs from each other.
     *
     * E.g.
     * <pre>
     *         1234567
     * OLd => "abcdefg" Start marker inserted at position 3
     * New => "ab123fg"   End marker inserted at position 6
     * </pre>
     *
     * @param array $oldText  Collection of lines of old text.
     * @param array $newText  Collection of lines of new text.
     * @param int   $startOld First line of the block in old to replace.
     * @param int   $endOld   last line of the block in old to replace.
     * @param int   $startNew First line of the block in new to replace.
     */
    private function markInlineChange(array &$oldText, array &$newText, $startOld, $endOld, $startNew)
    {
        for ($i = 0; $i < ($endOld - $startOld); ++$i) {
            // Check each line in the block for differences.
            $oldString = $oldText[$startOld + $i];
            $newString = $newText[$startNew + $i];

            // Determine the start and end position of the line difference.
            list($start, $end) = $this->getInlineChange($oldString, $newString);
            if ($start != 0 || $end != 0) {
                // Changes between the lines exist.
                // Add markers around the changed character sequence in the old string.
                $sequenceEnd = mb_strlen($oldString) + $end;
                $oldString   =
                    mb_substr($oldString, 0, $start) . "\0" .
                    mb_substr($oldString, $start, $sequenceEnd - $start) . "\1" .
                    mb_substr($oldString, $sequenceEnd);

                // Add markers around the changed character sequence in the new string.
                $sequenceEnd = mb_strlen($newString) + $end;
                $newString   =
                    mb_substr($newString, 0, $start) . "\0" .
                    mb_substr($newString, $start, $sequenceEnd - $start) . "\1" .
                    mb_substr($newString, $sequenceEnd);

                // Overwrite the strings in the old and new text so the changed lines include the markers.
                $oldText[$startOld + $i] = $oldString;
                $newText[$startNew + $i] = $newString;
            }
        }
    }

    /**
     * Determine where changes between two strings begin and where they end.
     *
     * This returns a two elements array.
     * The first element defines the first (starting at 0) character from the start of the old string which is
     * different.
     * The second element defines the last (starting at -0) character from the end of the old string which is different.
     *
     *
     * @param string $oldString The first string to compare.
     * @param string $newString The second string to compare.
     *
     * @return array Array containing the starting position (0 by default) and the ending position (-1 by default)
     */
    private function getInlineChange(string $oldString, string $newString): array
    {
        $start = 0;
        $limit = min(mb_strlen($oldString), mb_strlen($newString));

        // Find the position of the first character which is different between old and new.
        // Starts at the begin of the strings.
        // Stops at the end of the shortest string.
        while ($start < $limit && mb_substr($oldString, $start, 1) == mb_substr($newString, $start, 1)) {
            ++$start;
        }

        $end   = -1;
        $limit = $limit - $start;

        // Find the position of the last character which is different between old and new.
        // Starts at the end of the shortest string.
        // Stops just before the last different character.
        while (-$end <= $limit && mb_substr($oldString, $end, 1) == mb_substr($newString, $end, 1)) {
            --$end;
        }

        return [
            $start,
            $end + 1
        ];
    }

    /**
     * Helper function that will fill the changes-array for the renderer with default values.
     * Every time a operation changes (specified by $tag) , a new element will be appended to this array.
     *
     * The index of the last element of the array is always returned.
     *
     * @param array   $blocks    The array which keeps the changes for the HTML renderer.
     * @param string  $tag       Kind of difference.
     * @param integer $lineInOld Start of block in "old".
     * @param integer $lineInNew Start of block in "new".
     *
     * @return int The index of the last element.
     */
    private function appendChangesArray(array &$blocks, string $tag, int $lineInOld, int $lineInNew): int
    {
        if ($tag == $this->lastTag) {
            return count($blocks) - 1;
        }

        $blocks[] = [
            'tag'     => $tag,
            'base'    => [
                'offset' => $lineInOld,
                'lines'  => []
            ],
            'changed' => [
                'offset' => $lineInNew,
                'lines'  => []
            ]
        ];

        $this->lastTag = $tag;
        return count($blocks) - 1;
    }

    /**
     * Format a series of strings which are suitable for output in a HTML rendered diff.
     *
     * This involves replacing tab characters with spaces, making the HTML safe for output by ensuring that double
     * spaces are replaced with &nbsp; etc.
     *
     * @param array $strings Array of strings to format.
     *
     * @return array Array of formatted strings.
     */
    protected function formatLines(array $strings): array
    {
        if ($this->options['tabSize'] !== false) {
            // Replace tabs with spaces.
            $strings = array_map(
                function ($item) {
                    return str_replace("\t", str_repeat(' ', $this->options['tabSize']), $item);
                },
                $strings
            );
        }

        // Convert special characters to HTML entities
        $strings = array_map(
            function ($item) {
                return htmlspecialchars($item, ENT_NOQUOTES, 'UTF-8');
            },
            $strings
        );

        // Replace leading spaces of a line with HTML entities.
        foreach ($strings as &$line) {
            $line = preg_replace_callback(
                '/(^[ \0\1]*)/',
                function ($matches) {
                    return str_replace(' ', "&nbsp;", $matches[0]);
                },
                $line
            );
        }
        unset($line);

        return $strings;
    }
}
