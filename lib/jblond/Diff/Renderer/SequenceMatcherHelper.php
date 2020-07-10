<?php

namespace jblond\Diff\Renderer;

/**
 * Sequence matcher helper functions for Diff
 *
 * PHP version 7.2 or greater
 *
 * @package jblond\Diff
 * @author Mario Brandt <leet31337@web.de>
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 2.0.0
 * @link https://github.com/JBlond/php-diff
 */
class SequenceMatcherHelper
{
    /**
     * Helper function that provides the ability to return the value for a key
     * in an array of it exists, or if it doesn't then return a default value.
     * Essentially cleaner than doing a series of if (isset()) {} else {} calls.
     *
     * @param array $array The array to search.
     * @param string|int $key The key to check that exists.
     * @param mixed $default The value to return as the default value if the key doesn't exist.
     * @return mixed The value from the array if the key exists or otherwise the default.
     */
    protected function arrayGetDefault(array $array, $key, $default)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        return $default;
    }

    /**
     * Sort an array by the nested arrays it contains. Helper function for getMatchingBlocks
     *
     * @param array $aArray First array to compare.
     * @param array $bArray Second array to compare.
     * @return int -1, 0 or 1, as expected by the usort function.
     */
    protected function tupleSort(array $aArray, array $bArray): int
    {
        $max = max(count($aArray), count($bArray));
        for ($counter = 0; $counter < $max; ++$counter) {
            if ($aArray[$counter] < $bArray[$counter]) {
                return -1;
            } elseif ($aArray[$counter] > $bArray[$counter]) {
                return 1;
            }
        }

        if (count($aArray) == count($bArray)) {
            return 0;
        }
        if (count($aArray) < count($bArray)) {
            return -1;
        }
        return 1;
    }
}
