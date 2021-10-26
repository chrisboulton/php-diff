<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer;

use jblond\Diff\SequenceMatcher;

/**
 * Base renderer for rendering diffs for PHP DiffLib.
 *
 * PHP version 7.3 or greater
 *
 * @package       jblond\Diff\Renderer\Html
 * @author        Chris Boulton <chris.boulton@interspire.com>
 * @author        Mario Brandt <leet31337@web.de>
 * @author        Ferry Cools <info@DigiLive.nl>
 * @copyright (c) 2009 Chris Boulton
 * @license       New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version       2.4.0
 * @link          https://github.com/JBlond/php-diff
 */
class MainRenderer extends MainRendererAbstract
{
    /**
     * @var int Character count of the line marker with the most characters.
     */
    protected $maxLineMarkerWidth = 0;
    /**
     * @var string The last operation which was recorded in the array which contains the changes, used by the renderer.
     * @see MainRenderer::appendChangesArray()
     */
    private $lastTag;

    /**
     * Generate a string representation of changes between version1 and version2.
     *
     * This method is called by the renderers which extends this class.
     *
     * @param   array   $changes      Contains the op-codes about the differences between version1 and version2.
     * @param   object  $subRenderer  Renderer which is subClass of this class.
     *
     * @return string|false String representation of the differences or false when versions are identical.
     */
    public function renderOutput(array $changes, object $subRenderer)
    {
        if (!$changes) {
            //No changes between version1 and version2
            return false;
        }

        $output = $subRenderer->generateDiffHeader();

        foreach ($changes as $iterator => $blocks) {
            if ($iterator > 0) {
                // If this is a separate block, we're condensing code to indicate a significant portion of the code
                // has been collapsed as it did not change.
                $output .= $subRenderer->generateSkippedLines();
            }

            $this->maxLineMarkerWidth = max(
                strlen($this->options['insertMarkers'][0]),
                strlen($this->options['deleteMarkers'][0]),
                strlen($this->options['equalityMarkers'][0]),
                strlen($this->options['equalityMarkers'][1])
            );

            $deprecationTriggered = false;
            foreach ($blocks as $change) {
                if (
                    $subRenderer instanceof MainRenderer &&
                    !method_exists($subRenderer, 'generateLinesIgnore') &&
                    $change['tag'] == 'ignore'
                ) {
                    if (!$deprecationTriggered) {
                        trigger_error(
                            'The use of a subRenderer without method generateLinesIgnore() is deprecated!',
                            E_USER_DEPRECATED
                        );
                        $deprecationTriggered = true;
                    }
                    $change['tag'] =
                        (count($change['base']['lines']) > count($change['changed']['lines'])) ? 'delete' : 'insert';
                }
                $output .= $subRenderer->generateBlockHeader($change);
                switch ($change['tag']) {
                    case 'equal':
                        $output .= $subRenderer->generateLinesEqual($change);
                        break;
                    case 'insert':
                        $output .= $subRenderer->generateLinesInsert($change);
                        break;
                    case 'delete':
                        $output .= $subRenderer->generateLinesDelete($change);
                        break;
                    case 'replace':
                        $output .= $subRenderer->generateLinesReplace($change);
                        break;
                    case 'ignore':
                        // TODO: Keep backward compatible with renderers?
                        $output .= $subRenderer->generateLinesIgnore($change);
                        break;
                }

                $output .= $subRenderer->generateBlockFooter($change);
            }
        }

        $output .= $subRenderer->generateDiffFooter();

        return $output;
    }

    /**
     * Render the sequences where differences between them are marked.
     *
     * The marked sequences are returned as array which is suitable for rendering the final output.
     *
     * Generally called by classes which extend this class and that generate a diff by returning an array of the changes
     * to show in the diff.
     *
     * @return array An array of marked sequences.
     */
    protected function renderSequences(): array
    {
        // The old and New texts are copied so change markers can be added without modifying the original sequences.
        $oldText = $this->diff->getVersion1();
        $newText = $this->diff->getVersion2();

        $changes = [];

        foreach ($this->diff->getGroupedOpCodes() as $group) {
            $blocks        = [];
            $this->lastTag = null;

            foreach ($group as $code) {
                [$tag, $startOld, $endOld, $startNew, $endNew] = $code;
                /**
                 * $code is an array describing an op-code which includes:
                 * 0 - The type of tag (as described below) for the op code.
                 * 1 - The beginning line in the first sequence.
                 * 2 - The end line in the first sequence.
                 * 3 - The beginning line in the second sequence.
                 * 4 - The end line in the second sequence.
                 *
                 * The different types of tags include:
                 * replace - The string in $oldText from $startOld to $endOld, should be replaced by
                 *           the string in $newText from $startNew to $endNew.
                 * delete  - The string in $oldText from $startOld to $endNew should be deleted.
                 * insert  - The string in $newText from $startNew to $endNew should be inserted at $startOld in
                 *           $oldText.
                 * equal   - The two strings with the specified ranges are equal.
                 * ignore  - The string in $oldText from $startOld to $endOld and
                 *           the string in $newText from $startNew to $endNew are different, but considered to be equal.
                 */

                $blockSizeOld = $endOld - $startOld;
                $blockSizeNew = $endNew - $startNew;

                if (($tag == 'replace') && ($blockSizeOld == $blockSizeNew)) {
                    // Inline differences between old and new block.
                    $this->markInlineChanges($oldText, $newText, $startOld, $endOld, $startNew);
                }

                $lastBlock = $this->appendChangesArray($blocks, $tag, $startOld, $startNew);

                // Extract the block from both the old and new text and format each line.
                $oldBlock = $this->formatLines(array_slice($oldText, $startOld, $blockSizeOld));
                $newBlock = $this->formatLines(array_slice($newText, $startNew, $blockSizeNew));

                if ($tag != 'delete' && $tag != 'insert') {
                    // Old block "equals" New block or is replaced.
                    $blocks[$lastBlock]['base']['lines']    += $oldBlock;
                    $blocks[$lastBlock]['changed']['lines'] += $newBlock;
                    continue;
                }

                if ($tag == 'delete') {
                    // Block of version1 doesn't exist in version2.
                    $blocks[$lastBlock]['base']['lines'] += $oldBlock;
                    continue;
                }

                // Block of version2 doesn't exist in version1.
                $blocks[$lastBlock]['changed']['lines'] += $newBlock;
            }

            $changes[] = $blocks;
        }

        return $changes;
    }

    /**
     * Surround inline changes with markers.
     *
     * @param   array  $oldText   Collection of lines of old text.
     * @param   array  $newText   Collection of lines of new text.
     * @param   int    $startOld  First line of the block in old to replace.
     * @param   int    $endOld    last line of the block in old to replace.
     * @param   int    $startNew  First line of the block in new to replace.
     */
    private function markInlineChanges(
        array &$oldText,
        array &$newText,
        int $startOld,
        int $endOld,
        int $startNew
    ): void {
        if ($this->options['inlineMarking'] < self::CHANGE_LEVEL_LINE) {
            $this->markInnerChange($oldText, $newText, $startOld, $endOld, $startNew);

            return;
        }

        if ($this->options['inlineMarking'] == self::CHANGE_LEVEL_LINE) {
            $this->markOuterChange($oldText, $newText, $startOld, $endOld, $startNew);
        }
    }

    /**
     * Add markers around inline changes between old and new text.
     *
     * Each line of the old and new text is evaluated.
     * When a line of old differs from the same line of new, a marker is inserted into both lines, just before the first
     * different character/word. A second marker is added just before the following character/word which matches again.
     *
     * Setting parameter changeType to self::CHANGE_LEVEL_CHAR will mark differences at character level.
     * Other values will mark differences at word level.
     *
     * E.g. Character level.
     * <pre>
     *         1234567890
     * Old => "aa bbc cdd" Start marker inserted at position 4
     * New => "aa 12c cdd" End marker inserted at position 6
     * </pre>
     * E.g. Word level.
     * <pre>
     *         1234567890
     * Old => "aa bbc cdd" Start marker inserted at position 4
     * New => "aa 12c cdd" End marker inserted at position 7
     * </pre>
     *
     * @param   array  $oldText   Collection of lines of old text.
     * @param   array  $newText   Collection of lines of new text.
     * @param   int    $startOld  First line of the block in old to replace.
     * @param   int    $endOld    last line of the block in old to replace.
     * @param   int    $startNew  First line of the block in new to replace.
     */
    private function markInnerChange(array &$oldText, array &$newText, int $startOld, int $endOld, int $startNew): void
    {
        for ($iterator = 0; $iterator < ($endOld - $startOld); ++$iterator) {
            // ChangeType 0: Character Level.
            // ChangeType 1: Word Level.
            $regex = $this->options['inlineMarking'] ? '/\w+|[^\w\s]|\s/u' : '/.?/u';

            // Deconstruct the lines into arrays, including new empty element to the end in case a marker needs to be
            // placed as last.
            $oldLine   = $this->sequenceToArray($regex, $oldText[$startOld + $iterator]);
            $newLine   = $this->sequenceToArray($regex, $newText[$startNew + $iterator]);
            $oldLine[] = '';
            $newLine[] = '';

            $sequenceMatcher = new SequenceMatcher($oldLine, $newLine);
            $opCodes         = $sequenceMatcher->getGroupedOpCodes();

            foreach ($opCodes as $group) {
                foreach ($group as [$tag, $changeStartOld, $changeEndOld, $changeStartNew, $changeEndNew]) {
                    if ($tag == 'equal') {
                        continue;
                    }
                    if ($tag == 'replace' || $tag == 'delete') {
                        $oldLine[$changeStartOld] = "\0" . $oldLine[$changeStartOld];
                        $oldLine[$changeEndOld]   = "\1" . $oldLine[$changeEndOld];
                    }
                    if ($tag == 'replace' || $tag == 'insert') {
                        $newLine[$changeStartNew] = "\0" . $newLine[$changeStartNew];
                        $newLine[$changeEndNew]   = "\1" . $newLine[$changeEndNew];
                    }
                }
            }

            // Reconstruct the lines and overwrite originals.
            $oldText[$startOld + $iterator] = implode('', $oldLine);
            $newText[$startNew + $iterator] = implode('', $newLine);
        }
    }

    /**
     * Split a sequence of characters into an array.
     *
     * Each element of the returned array contains a full pattern match of the regex pattern.
     *
     * @param   string  $pattern   Regex pattern to split by.
     * @param   string  $sequence  The sequence to split.
     *
     * @return array  The split sequence.
     */
    public function sequenceToArray(string $pattern, string $sequence): array
    {
        preg_match_all($pattern, $sequence, $matches);

        return $matches[0];
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
     * Old => "abcdefg" Start marker inserted at position 3
     * New => "ab123fg"   End marker inserted at position 6
     * </pre>
     *
     * @param   array  $oldText   Collection of lines of old text.
     * @param   array  $newText   Collection of lines of new text.
     * @param   int    $startOld  First line of the block in old to replace.
     * @param   int    $endOld    last line of the block in old to replace.
     * @param   int    $startNew  First line of the block in new to replace.
     */
    private function markOuterChange(array &$oldText, array &$newText, int $startOld, int $endOld, int $startNew): void
    {
        for ($iterator = 0; $iterator < ($endOld - $startOld); ++$iterator) {
            // Check each line in the block for differences.
            $oldString = $oldText[$startOld + $iterator];
            $newString = $newText[$startNew + $iterator];

            // Determine the start and end position of the line difference.
            [$start, $end] = $this->getOuterChange($oldString, $newString);
            // Changes between the lines exist.
            // Add markers around the changed character sequence in the old string.
            $sequenceEnd = mb_strlen($oldString) + $end;
            $oldString
                         = mb_substr($oldString, 0, $start) . "\0" .
                mb_substr($oldString, $start, $sequenceEnd - $start) . "\1" .
                mb_substr($oldString, $sequenceEnd);

            // Add markers around the changed character sequence in the new string.
            $sequenceEnd = mb_strlen($newString) + $end;
            $newString
                         = mb_substr($newString, 0, $start) . "\0" .
                mb_substr($newString, $start, $sequenceEnd - $start) . "\1" .
                mb_substr($newString, $sequenceEnd);

            // Overwrite the strings in the old and new text so the changed lines include the markers.
            $oldText[$startOld + $iterator] = $oldString;
            $newText[$startNew + $iterator] = $newString;
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
     * @param   string  $oldString  The first string to compare.
     * @param   string  $newString  The second string to compare.
     *
     * @return array Array containing the starting position (0 by default) and the ending position (-1 by default)
     */
    private function getOuterChange(string $oldString, string $newString): array
    {
        $start = 0;
        $limit = min(mb_strlen($oldString), mb_strlen($newString));

        // Find the position of the first character which is different between old and new.
        // Starts at the beginning of the strings.
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
            $end + 1,
        ];
    }

    /**
     * Helper function that will fill the changes-array for the renderer with default values.
     * Every time an operation changes (specified by $tag) , a new element will be appended to this array.
     *
     * The index of the last element of the array is always returned.
     *
     * @param   array   $blocks     The array which keeps the changes for the HTML renderer.
     * @param   string  $tag        Kind of difference.
     * @param   int     $lineInOld  Start of block in "old".
     * @param   int     $lineInNew  Start of block in "new".
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
                'lines'  => [],
            ],
            'changed' => [
                'offset' => $lineInNew,
                'lines'  => [],
            ],
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
     * @param   array  $strings  Array of strings to format.
     *
     * @return array Array of formatted strings.
     */
    protected function formatLines(array $strings): array
    {
        if ($this->options['tabSize'] !== false) {
            // Replace tabs with spaces.
            $strings = array_map(
                function ($line) {
                    return str_replace("\t", str_repeat(' ', $this->options['tabSize']), $line);
                },
                $strings
            );
        }

        if (strtolower($this->options['format']) == 'html') {
            // Convert special characters to HTML entities
            $strings = array_map(
                function ($line) {
                    return htmlspecialchars($line, ENT_NOQUOTES);
                },
                $strings
            );

            // Replace leading spaces of a line with HTML entities.
            foreach ($strings as &$line) {
                $line = preg_replace_callback(
                    '/(^[ \0\1]*)/',
                    function ($matches) {
                        return str_replace(' ', '&nbsp;', $matches[0]);
                    },
                    $line
                );
            }
            unset($line);
        }

        return $strings;
    }
}
