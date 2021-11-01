<?php

declare(strict_types=1);

namespace jblond\Diff;

/**
 * Similarity ratio calculations for the Sequence matcher.
 *
 * @see             similar_text()
 *
 * PHP version 7.3 or greater
 *
 * @package         jblond\Diff
 * @author          Chris Boulton <chris.boulton@interspire.com>
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2020 Ferry Cools
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class Similarity extends SequenceMatcher
{
    /**
     * Default method for calculation similarity ratio.
     */
    public const CALC_DEFAULT = 0;
    /**
     * Fast method for calculation similarity ratio.
     */
    public const CALC_FAST = 1;
    /**
     * Fastest method for calculation similarity ratio.
     */
    public const CALC_FASTEST = 2;
    /**
     * @var array Count of each unique sequence at version 2.
     */
    private $uniqueCount2;
    /**
     * @var array Contains the indexes of lines which are stripped from the sequences by Similarity::stripLines().
     * @see Similarity::stripLines()
     */
    private $stripped = ['old' => [], 'new' => []];


    /**
     * @inheritDoc
     */
    public function setSeq2($version2): void
    {
        $this->uniqueCount2 = null;
        parent::setSeq2($version2);
    }

    /**
     * Return a measure of similarity between the two sequences.
     *
     * This will be a float value between 0 and 1.
     *
     * Tree calculation methods are available:
     * self::CALC_DEFAULT:  Default method.
     * self::CALC_FAST:     Faster calculation. Less cpu load & less accurate than above.
     * self::CALC_FASTEST:  Fastest calculation. Less cpu load & less accurate than above.
     *
     * @param   int  $type  Calculation method.
     *
     * @return float The calculated ratio.
     *
     */
    public function getSimilarity(int $type = self::CALC_DEFAULT): float
    {
        if ($this->options['ignoreLines']) {
            // Backup original sequences and filter non blank lines.
            $this->stripLines();
        }

        switch ($type) {
            case self::CALC_FAST:
                $ratio = $this->getRatioFast();
                $this->restoreLines();
                break;
            case self::CALC_FASTEST:
                $ratio = $this->getRatioFastest();
                $this->restoreLines();
                break;
            default:
                $this->setSequences($this->old, $this->new);
                $matches = array_reduce(
                    $this->getMatchingBlocks(),
                    function ($carry, $item) {
                        return $this->ratioReduce($carry, $item);
                    },
                    0
                );

                $ratio = $this->calculateRatio($matches, count($this->old) + count($this->new));
                $this->restoreLines();
                $this->setSequences($this->old, $this->new);
        }

        return $ratio;
    }

    /**
     * Strip empty or blank lines from the sequences to compare.
     *
     */
    private function stripLines(): void
    {
        foreach (['old', 'new'] as $version) {
            // Remove empty lines.
            $this->$version = array_filter(
                $this->$version,
                function ($line, $index) use ($version) {
                    $sanitizedLine = $line;
                    if ($this->options['ignoreLines'] == self::DIFF_IGNORE_LINE_BLANK) {
                        $sanitizedLine = trim($line);
                    }

                    if ($sanitizedLine == '') {
                        // Store line to be able to restore later.
                        $this->stripped[$version][$index] = $line;

                        return false;
                    }

                    return true;
                },
                ARRAY_FILTER_USE_BOTH
            );

            // Re-index sequence.
            $this->$version = array_values($this->$version);
        }
    }

    /**
     * Quickly return an upper bound ratio for the similarity of the strings.
     *
     * This is quicker to compute than self::CALC_DEFAULT.
     *
     * @return float The calculated ratio.
     */
    private function getRatioFast(): float
    {
        if ($this->uniqueCount2 === null) {
            // Build unless cached.
            $this->uniqueCount2 = [];
            foreach ($this->new as $iteratorValue) {
                $char                      = $iteratorValue;
                $this->uniqueCount2[$char] = ($this->uniqueCount2[$char] ?? 0) + 1;
            }
        }

        $avail   = [];
        $matches = 0;
        foreach ($this->old as $iteratorValue) {
            $char         = $iteratorValue;
            $numb         = $avail[$char] ?? ($this->uniqueCount2[$char] ?? 0);
            $avail[$char] = $numb - 1;
            if ($numb > 0) {
                ++$matches;
            }
        }

        return $this->calculateRatio($matches, count($this->old) + count($this->new));
    }

    /**
     * Helper function for calculating the ratio to measure similarity for the strings.
     *
     * The ratio is defined as being 2 * (number of matches / total length)
     *
     * @param   int  $matches  The number of matches in the two strings.
     * @param   int  $length   The length of the two sequences.
     *
     * @return float The calculated ratio.
     */
    private function calculateRatio(int $matches, int $length = 0): float
    {
        $returnValue = 1;
        if ($length) {
            return 2 * ($matches / $length);
        }

        return $returnValue;
    }

    private function restoreLines(): void
    {
        foreach (['old', 'new'] as $version) {
            foreach ($this->stripped[$version] as $index => $line) {
                array_splice($this->$version, $index, 0, $line);
            }
        }
    }

    /**
     * Return an upper bound ratio really quickly for the similarity of the strings.
     *
     * This is quicker to compute than self::CALC_DEFAULT and self::CALC_FAST.
     *
     * @return float The calculated ratio.
     */
    private function getRatioFastest(): float
    {
        $aLength = count($this->old);
        $bLength = count($this->new);

        return $this->calculateRatio(min($aLength, $bLength), $aLength + $bLength);
    }

    /**
     * Helper function to calculate the number of matches for Ratio().
     *
     * @param   int    $sum     The running total for the number of matches.
     * @param   array  $triple  Array containing the matching block triple to add to the running total.
     *
     * @return int The new running total for the number of matches.
     */
    private function ratioReduce(int $sum, array $triple): int
    {
        return $sum + ($triple[count($triple) - 1]);
    }

    /**
     * Get diff statistics
     *
     * @return array
     */
    public function getDifference(): array
    {
        $return = [
            'inserted' => 0,
            'deleted'  => 0,
            'replaced' => 0,
        ];

        foreach ($this->getGroupedOpCodes() as $chunk) {
            foreach ($chunk as [$string, $one, $two, $three, $four]) {
                switch ($string) {
                    case 'delete':
                        $return['deleted'] += $two - $one;
                        break;
                    case 'insert':
                        $return['inserted'] += $four - $three;
                        break;
                    case 'replace':
                        $return['replaced'] += $two - $one;
                        break;
                }
            }
        }

        $return['equal'] = count($this->old) - $return['replaced'] - $return['deleted'];

        return $return;
    }
}
