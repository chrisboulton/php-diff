<?php

declare(strict_types=1);

namespace jblond\Diff;

use InvalidArgumentException;

/**
 * Sequence matcher for Diff
 *
 * PHP version 7.3 or greater
 *
 * @package         jblond\Diff
 * @author          Chris Boulton <chris.boulton@interspire.com>
 * @author          Mario Brandt <leet31337@web.de>
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2020 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class SequenceMatcher implements ConstantsInterface
{
    /**
     * @var array The first sequence to compare against.
     */
    protected $old;
    /**
     * @var array The second sequence.
     */
    protected $new;
    /**
     * @var array   Associative array containing the options that will be applied for generating the diff.
     *              The key-value pairs are set at the constructor of this class.
     *
     * @see SequenceMatcher::setOptions()
     */
    protected $options = [];
    /**
     * @var string|array Either a string or an array containing a callback function to determine
     * if a line is "junk" or not.
     */
    private $junkCallback;
    /**
     * @var array Array of characters that are considered junk from the second sequence. Characters are the array key.
     */
    private $junkDict = [];
    /**
     * @var array Array of indices that do not contain junk elements.
     */
    private $b2j = [];
    /**
     * @var array A list of all the op-codes for the differences between the compared strings.
     */
    private $opCodes;

    /**
     * @var array A nested set of arrays for all the matching sub-sequences the compared strings.
     */
    private $matchingBlocks;

    /**
     * @var array Associative array containing the default options available for the diff class and their default value.
     *
     *            - context           The amount of lines to include around blocks that differ.
     *            - trimEqual         Strip blocks of equal lines from the start and end of the text.
     *            - ignoreWhitespace  True to ignore differences in tabs and spaces.
     *            - ignoreCase        True to ignore differences in character casing.
     *            - ignoreLines       0: None.
     *                                1: Ignore empty lines.
     *                                2: Ignore blank lines.
     */
    private $defaultOptions = [
        'context'          => 3,
        'trimEqual'        => true,
        'ignoreWhitespace' => false,
        'ignoreCase'       => false,
        'ignoreLines'      => self::DIFF_IGNORE_LINE_NONE,
    ];

    /**
     * The constructor. With the sequences being passed, they'll be set for the
     * sequence matcher, and it will perform a basic cleanup & calculate junk
     * elements.
     *
     * @param   string|array       $old           A string or array containing the lines to compare against.
     * @param   string|array       $new           A string or array containing the lines to compare.
     * @param   array              $options
     * @param   string|array|null  $junkCallback  Either an array or string that references a callback function
     *                                            (if there is one) to determine 'junk' characters.
     */
    public function __construct($old, $new, array $options = [], $junkCallback = null)
    {
        $this->old          = [];
        $this->new          = [];
        $this->junkCallback = $junkCallback;
        $this->setOptions($options);
        $this->setSequences($old, $new);
    }

    /**
     * @param   array  $options
     */
    public function setOptions(array $options): void
    {
        if (isset($options['context']) && $options['context'] < 0) {
            throw new InvalidArgumentException('The context option cannot be a negative value!');
        }
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Set the first and second sequence to use with the sequence matcher.
     *
     * @param   string|array  $version1  A string or array containing the lines to compare against.
     * @param   string|array  $version2  A string or array containing the lines to compare.
     *
     * @return void
     */
    public function setSequences($version1, $version2): void
    {
        $this->setSeq1($version1);
        $this->setSeq2($version2);
    }

    /**
     * Set the first sequence.
     *
     * Also resets internal caches to indicate that, when calling the calculation methods, we need to recalculate them.
     *
     * @param   string|array|void  $version1  The sequence to set as the first sequence.
     *
     * @return void
     */
    public function setSeq1($version1): void
    {
        if (!is_array($version1)) {
            $version1 = str_split($version1);
        }
        if ($version1 == $this->old) {
            return;
        }

        $this->old            = $version1;
        $this->matchingBlocks = null;
        $this->opCodes        = null;
    }

    /**
     * Set the second sequence.
     *
     * Also resets internal caches to indicate that, when calling the calculation methods, we need to recalculate them.
     *
     * @param   string|array  $version2  The sequence to set as the second sequence.
     *
     * @return void
     */
    public function setSeq2($version2): void
    {
        if (!is_array($version2)) {
            $version2 = str_split($version2);
        }
        if ($version2 == $this->new) {
            return;
        }

        $this->new            = $version2;
        $this->matchingBlocks = null;
        $this->opCodes        = null;
        $this->chainB();
    }

    /**
     * Generate the internal arrays containing the list of junk and non-junk
     * characters for the second ($b) sequence.
     */
    private function chainB(): void
    {
        $length      = count($this->new);
        $this->b2j   = [];
        $popularDict = [];

        foreach ($this->new as $i => $iValue) {
            $char = $iValue;
            if (isset($this->b2j[$char])) {
                if ($length >= 200 && count($this->b2j[$char]) * 100 > $length) {
                    $popularDict[$char] = 1;
                    unset($this->b2j[$char]);
                    continue;
                }

                $this->b2j[$char][] = $i;
                continue;
            }

            $this->b2j[$char] = [$i];
        }

        // Remove leftovers
        foreach (array_keys($popularDict) as $char) {
            unset($this->b2j[$char]);
        }

        $this->junkDict = [];
        if (is_callable($this->junkCallback)) {
            foreach (array_keys($popularDict) as $char) {
                if (call_user_func($this->junkCallback, $char)) {
                    $this->junkDict[$char] = 1;
                    unset($popularDict[$char]);
                }
            }

            foreach (array_keys($this->b2j) as $char) {
                if (call_user_func($this->junkCallback, $char)) {
                    $this->junkDict[$char] = 1;
                    unset($this->b2j[$char]);
                }
            }
        }
    }

    /**
     * Return a series of nested arrays containing different groups of generated op codes for the differences between
     * the strings with up to $this->options['context'] lines of surrounding content.
     *
     * Any large equal block of strings is separated into smaller subsets which are "Within- or Out Of Context".
     *
     * @return array Nested array of all the grouped op codes.
     */
    public function getGroupedOpCodes(): array
    {
        $opCodes = $this->getOpCodes();
        $opCodes = $opCodes ?: [['equal', 0, 1, 0, 1,],];

        if ($this->options['trimEqual']) {
            if ($opCodes[0][0] == 'equal') {
                // Remove equal sequences at the start of the text, but keep the context lines.
                $opCodes[0] = [
                    $opCodes[0][0],
                    max($opCodes[0][1], $opCodes[0][2] - $this->options['context']),
                    $opCodes[0][2],
                    max($opCodes[0][3], $opCodes[0][4] - $this->options['context']),
                    $opCodes[0][4],
                ];
            }

            $lastItem = array_key_last($opCodes);
            if ($opCodes[$lastItem][0] == 'equal') {
                // Remove equal sequences at the end of the text, but keep the context lines.
                [$tag, $item1, $item2, $item3, $item4] = $opCodes[$lastItem];
                $opCodes[$lastItem] = [
                    $tag,
                    $item1,
                    min($item2, $item1 + $this->options['context']),
                    $item3,
                    min($item4, $item3 + $this->options['context']),
                ];
            }
        }

        $maxRange = $this->options['context'] * 2;
        $groups   = [];
        $newGroup = [];

        foreach ($opCodes as [$tag, $item1, $item2, $item3, $item4]) {
            if ($tag == 'equal' && $item2 - $item1 > $maxRange) {
                // Count of equal lines is greater than defined maximum context.
                // Define lines before "Out of Context".
                $newGroup[] = [
                    $tag,
                    $item1,
                    min($item2, $item1 + $this->options['context']),
                    $item3,
                    min($item4, $item3 + $this->options['context']),
                ];

                $groups[] = $newGroup;

                // Define lines which are "Out Of Context".
                $newGroup   = [];
                $newGroup[] = [
                    'outOfContext',
                    min($item2, $item1 + $this->options['context']),
                    max($item1, $item2 - $this->options['context']),
                    min($item4, $item3 + $this->options['context']),
                    max($item3, $item4 - $this->options['context']),
                ];
                $groups[]   = $newGroup;

                // Define start of lines after "Out Of Context".
                $newGroup = [];
                $item1    = max($item1, $item2 - $this->options['context']);
                $item3    = max($item3, $item4 - $this->options['context']);
            }

            // Define lines "Within Context".
            $newGroup[] = [$tag, $item1, $item2, $item3, $item4,];
        }

        if (
            !$this->options['trimEqual'] ||
            (!empty($newGroup) && !(count($newGroup) == 1 && $newGroup[0][0] == 'equal'))
        ) {
            // Add the last sequences when !trimEqual || When there are no differences between both versions.
            $groups[] = $newGroup;
        }

        return $groups;
    }

    /**
     * Return a list of all the op codes for the differences between the
     * two strings.
     *
     * The nested array returned contains an array describing the op code which includes:
     * 0 - The type of tag (as described below) for the op code.
     * 1 - The beginning line in the first sequence.
     * 2 - The end line in the first sequence.
     * 3 - The beginning line in the second sequence.
     * 4 - The end line in the second sequence.
     *
     * The different types of tags include:
     * replace - The string from $i1 to $i2 in $a should be replaced by
     *           the string in $b from $j1 to $j2.
     * delete -  The string in $a from $i1 to $j2 should be deleted.
     * insert -  The string in $b from $j1 to $j2 should be inserted at
     *           $i1 in $a.
     * equal  -  The two strings with the specified ranges are equal.
     *
     * @return array Array of the opcodes describing the differences between the strings.
     */
    public function getOpCodes(): array
    {
        if (!empty($this->opCodes)) {
            //Return the cached results.
            return $this->opCodes;
        }

        $i             = 0;
        $j             = 0;
        $this->opCodes = [];

        $blocks = $this->getMatchingBlocks();
        foreach ($blocks as [$ai, $bj, $size]) {
            $tag = '';
            if ($i < $ai && $j < $bj) {
                $tag = 'replace';
            } elseif ($i < $ai) {
                $tag = 'delete';
            } elseif ($j < $bj) {
                $tag = 'insert';
            }

            if ($this->options['ignoreLines']) {
                $slice1 = array_slice($this->old, $i, $ai - $i);
                $slice2 = array_slice($this->new, $j, $bj - $j);

                if ($this->options['ignoreLines'] == 2) {
                    array_walk(
                        $slice1,
                        static function (&$line) {
                            $line = trim($line);
                        }
                    );
                    array_walk(
                        $slice2,
                        static function (&$line) {
                            $line = trim($line);
                        }
                    );
                }

                if (
                    ($tag == 'delete' && implode('', $slice1) == '') ||
                    ($tag == 'insert' && implode('', $slice2) == '')
                ) {
                    $tag = 'ignore';
                }
            }

            if ($tag) {
                $this->opCodes[] = [
                    $tag,
                    $i,
                    $ai,
                    $j,
                    $bj,
                ];
            }

            $i = $ai + $size;
            $j = $bj + $size;

            if ($size) {
                $this->opCodes[] = [
                    'equal',
                    $ai,
                    $i,
                    $bj,
                    $j,
                ];
            }
        }

        return $this->opCodes;
    }

    /**
     * Return a nested set of arrays for all the matching sub-sequences
     * in the strings $a and $b.
     *
     * Each block contains the lower constraint of the block in $a, the lower constraint of the block in $b and finally
     * the number of lines that the block continues for.
     *
     * @return array Nested array of the matching blocks, as described by the function.
     */
    public function getMatchingBlocks(): array
    {
        if (!empty($this->matchingBlocks)) {
            return $this->matchingBlocks;
        }

        $aLength = count($this->old);
        $bLength = count($this->new);

        $queue = [
            [
                0,
                $aLength,
                0,
                $bLength,
            ],
        ];

        $matchingBlocks = [];
        while (!empty($queue)) {
            [$aLower, $aUpper, $bLower, $bUpper] = array_pop($queue);
            $longestMatch = $this->findLongestMatch($aLower, $aUpper, $bLower, $bUpper);
            [$list1, $list2, $list3] = $longestMatch;
            if ($list3) {
                $matchingBlocks[] = $longestMatch;
                if ($aLower < $list1 && $bLower < $list2) {
                    $queue[] = [
                        $aLower,
                        $list1,
                        $bLower,
                        $list2,
                    ];
                }

                if ($list1 + $list3 < $aUpper && $list2 + $list3 < $bUpper) {
                    $queue[] = [
                        $list1 + $list3,
                        $aUpper,
                        $list2 + $list3,
                        $bUpper,
                    ];
                }
            }
        }

        sort($matchingBlocks);

        $i1          = 0;
        $j1          = 0;
        $k1          = 0;
        $nonAdjacent = [];
        foreach ($matchingBlocks as [$list4, $list5, $list6]) {
            if ($i1 + $k1 == $list4 && $j1 + $k1 == $list5) {
                $k1 += $list6;
                continue;
            }
            if ($k1) {
                $nonAdjacent[] = [
                    $i1,
                    $j1,
                    $k1,
                ];
            }

            $i1 = $list4;
            $j1 = $list5;
            $k1 = $list6;
        }


        if ($k1) {
            $nonAdjacent[] = [
                $i1,
                $j1,
                $k1,
            ];
        }

        $nonAdjacent[] = [
            $aLength,
            $bLength,
            0,
        ];

        $this->matchingBlocks = $nonAdjacent;

        return $this->matchingBlocks;
    }

    /**
     * Find the longest matching block in the two sequences, as defined by the
     * lower and upper constraints for each sequence. (for the first sequence,
     * $alo - $ahi and for the second sequence, $blo - $bhi)
     *
     * Essentially, of all the maximal matching blocks, return the one that
     * starts earliest in $a, and all of those maximal matching blocks that
     * start earliest in $a, return the one that starts earliest in $b.
     *
     * If the junk callback is defined, do the above but with the restriction
     * that the junk element appears in the block. Extend it as far as possible
     * by matching only junk elements in both $a and $b.
     *
     * @param   int  $aLower  The lower constraint for the first sequence.
     * @param   int  $aUpper  The upper constraint for the first sequence.
     * @param   int  $bLower  The lower constraint for the second sequence.
     * @param   int  $bUpper  The upper constraint for the second sequence.
     *
     * @return array Array containing the longest match that includes the starting position in $a,
     * start in $b and the length/size.
     */
    public function findLongestMatch(int $aLower, int $aUpper, int $bLower, int $bUpper): array
    {
        $old = $this->old;
        $new = $this->new;

        $bestI    = $aLower;
        $bestJ    = $bLower;
        $bestSize = 0;

        $j2Len   = [];
        $nothing = [];

        for ($i = $aLower; $i < $aUpper; ++$i) {
            $newJ2Len = [];
            $jDict    = $this->b2j[$old[$i]] ?? $nothing;
            foreach ($jDict as $j) {
                if ($j < $bLower) {
                    continue;
                } elseif ($j >= $bUpper) {
                    break;
                }

                $k            = ($j2Len[$j - 1] ?? 0) + 1;
                $newJ2Len[$j] = $k;
                if ($k > $bestSize) {
                    $bestI    = $i - $k + 1;
                    $bestJ    = $j - $k + 1;
                    $bestSize = $k;
                }
            }

            $j2Len = $newJ2Len;
        }

        while (
            $bestI > $aLower &&
            $bestJ > $bLower &&
            !$this->isBJunk($new[$bestJ - 1]) &&
            !$this->linesAreDifferent($bestI - 1, $bestJ - 1)
        ) {
            --$bestI;
            --$bestJ;
            ++$bestSize;
        }

        while (
            $bestI + $bestSize < $aUpper &&
            ($bestJ + $bestSize) < $bUpper &&
            !$this->isBJunk($new[$bestJ + $bestSize]) &&
            !$this->linesAreDifferent($bestI + $bestSize, $bestJ + $bestSize)
        ) {
            ++$bestSize;
        }

        while (
            $bestI > $aLower &&
            $bestJ > $bLower &&
            $this->isBJunk($new[$bestJ - 1]) &&
            !$this->linesAreDifferent($bestI - 1, $bestJ - 1)
        ) {
            --$bestI;
            --$bestJ;
            ++$bestSize;
        }

        while (
            $bestI + $bestSize < $aUpper &&
            $bestJ + $bestSize < $bUpper &&
            $this->isBJunk($new[$bestJ + $bestSize]) &&
            !$this->linesAreDifferent($bestI + $bestSize, $bestJ + $bestSize)
        ) {
            ++$bestSize;
        }

        return [
            $bestI,
            $bestJ,
            $bestSize,
        ];
    }

    /**
     * Checks if a particular character is in the junk dictionary
     * for the list of junk characters.
     *
     * @param   string  $bString
     *
     * @return bool True if the character is considered junk. False if not.
     */
    private function isBJunk(string $bString): bool
    {
        return isset($this->junkDict[$bString]);
    }

    /**
     * Check if the two lines at the given indexes are different or not.
     *
     * @param   int  $aIndex  Number of line to check against in A.
     * @param   int  $bIndex  Number of line to check against in B.
     *
     * @return bool True if the lines are different and false if not.
     */
    public function linesAreDifferent(int $aIndex, int $bIndex): bool
    {
        $lineA = $this->old[$aIndex];
        $lineB = $this->new[$bIndex];

        if ($this->options['ignoreWhitespace']) {
            $replace = ["\t", ' '];
            $lineA   = str_replace($replace, '', $lineA);
            $lineB   = str_replace($replace, '', $lineB);
        }

        if ($this->options['ignoreCase']) {
            $lineA = strtolower($lineA);
            $lineB = strtolower($lineB);
        }

        return $lineA != $lineB;
    }
}
