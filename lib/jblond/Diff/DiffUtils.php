<?php

namespace jblond\Diff;

/**
 * Sequence matcher helper functions for Diff
 *
 * PHP version 7.3 or greater
 *
 * @package         jblond\Diff
 * @author          Mario Brandt <leet31337@web.de>
 * @copyright   (c) 2020 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class DiffUtils
{
    /**
     * Sort an array by the nested arrays it contains. Helper function for getMatchingBlocks
     *
     * @param   array  $aArray  First array to compare.
     * @param   array  $bArray  Second array to compare.
     *
     * @return int -1, 0 or 1, as expected by the usort function.
     */
    public static function tupleSort(array $aArray, array $bArray): int
    {
        $max = max(count($aArray), count($bArray));
        for ($counter = 0; $counter < $max; ++$counter) {
            if ($aArray[$counter] < $bArray[$counter]) {
                return -1;
            }

            if ($aArray[$counter] > $bArray[$counter]) {
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
