<?php

namespace Tests\Diff;

use jblond\Diff\DiffUtils;
use PHPUnit\Framework\TestCase;

/**
 * PHPUnit Test for Diff Utils of PHP DiffLib.
 *
 * PHP version 7.3 or greater
 *
 * @package         Tests\Diff
 * @author          Mario Brandt <leet31337@web.de>
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2021 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class DiffUtilsTest extends TestCase
{
    /**
     * Test the sorting of an array by the nested arrays it contains
     */
    public function testTupleSort(): void
    {
        $this->assertEquals(
            1,
            DiffUtils::tupleSort(
                [

                    0 => [
                        'hashtag' => 'a7e87329b5eab8578f4f1098a152d6f4',
                        'title' => 'Flower',
                        'order' => 3,
                    ],
                    1 => [
                        'hashtag' => 'b24ce0cd392a5b0b8dedc66c25213594',
                        'title' => 'Free',
                        'order' => 2,
                    ],

                    2 => [
                        'hashtag' => 'e7d31fc0602fb2ede144d18cdffd816b',
                        'title' => 'Ready',
                        'order' => 1
                    ]
                ],
                [

                    0 => [
                        'hashtag' => 'a7e87329b5eab8578f4f1098a152d6f4',
                        'title' => 'Flower',
                        'order' => 3,
                    ],
                    1 => [
                        'hashtag' => 'b24ce0cd392a5b0b8dedc66c25213594',
                        'title' => 'Free',
                        'order' => 2,
                    ],

                    2 => [
                    ]
                ],
            )
        );

        $this->assertEquals(
            0,
            DiffUtils::tupleSort(
                [

                    0 => [
                        'hashtag' => 'a7e87329b5eab8578f4f1098a152d6f4',
                        'title' => 'Flower',
                        'order' => 3,
                    ],
                    1 => [
                        'hashtag' => 'b24ce0cd392a5b0b8dedc66c25213594',
                        'title' => 'Free',
                        'order' => 2,
                    ],

                    2 => [
                        'hashtag' => 'e7d31fc0602fb2ede144d18cdffd816b',
                        'title' => 'Ready',
                        'order' => 1
                    ]
                ],
                [

                    0 => [
                        'hashtag' => 'a7e87329b5eab8578f4f1098a152d6f4',
                        'title' => 'Flower',
                        'order' => 3,
                    ],
                    1 => [
                        'hashtag' => 'b24ce0cd392a5b0b8dedc66c25213594',
                        'title' => 'Free',
                        'order' => 2,
                    ],

                    2 => [
                        'hashtag' => 'e7d31fc0602fb2ede144d18cdffd816b',
                        'title' => 'Ready',
                        'order' => 1
                    ]
                ],
            )
        );

        $this->assertEquals(
            -1,
            DiffUtils::tupleSort(
                [

                    0 => [
                        'hashtag' => 'a7e87329b5eab8578f4f1098a152d6f4',
                        'title' => 'Flower',
                        'order' => 3,
                    ],
                    1 => [
                        'hashtag' => 'b24ce0cd392a5b0b8dedc66c25213594',
                        'title' => 'Free',
                        'order' => 2,
                    ],

                    2 => [

                    ]
                ],
                [

                    0 => [
                        'hashtag' => 'a7e87329b5eab8578f4f1098a152d6f4',
                        'title' => 'Flower',
                        'order' => 3,
                    ],
                    1 => [
                        'hashtag' => 'b24ce0cd392a5b0b8dedc66c25213594',
                        'title' => 'Free',
                        'order' => 2,
                    ],

                    2 => [
                        'hashtag' => 'e7d31fc0602fb2ede144d18cdffd816b',
                        'title' => 'Ready',
                        'order' => 1
                    ]
                ],
            )
        );
    }
}
