<?php

namespace Tests\Diff;

use jblond\Diff\Similarity;
use PHPUnit\Framework\TestCase;

class SimilarityTest extends TestCase
{

    public function testGetSimilarity()
    {
        $similarity = new Similarity(range(1, 10), range(1, 5));

        $this->assertEquals(2 / 3, $similarity->getSimilarity(Similarity::CALC_DEFAULT));
        $this->assertEquals(2 / 3, $similarity->getSimilarity(Similarity::CALC_FAST));
        $this->assertEquals(2 / 3, $similarity->getSimilarity(Similarity::CALC_FASTEST));
    }
}
