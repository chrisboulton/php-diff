<?php

namespace Tests\Diff;

use jblond\Diff\Similarity;
use PHPUnit\Framework\TestCase;

/**
 * PHPUnit Test for the Similarity class of PHP DiffLib.
 *
 * PHP version 7.3 or greater
 *
 * @package         Tests\Diff
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2020 Ferry Cools
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class SimilarityTest extends TestCase
{

    /**
     * Test the similarity ratio between two sequences with different methods.
     */
    public function testGetSimilarity(): void
    {
        $similarity = new Similarity(range(1, 10), range(1, 5));

        $this->assertEquals(2 / 3, $similarity->getSimilarity(Similarity::CALC_DEFAULT));
        $this->assertEquals(2 / 3, $similarity->getSimilarity(Similarity::CALC_FAST));
        $this->assertEquals(2 / 3, $similarity->getSimilarity(Similarity::CALC_FASTEST));
    }
}
