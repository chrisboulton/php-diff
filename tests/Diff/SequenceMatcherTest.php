<?php

namespace Diff\Renderer;

use jblond\Diff\SequenceMatcher;
use PHPUnit\Framework\TestCase;

class SequenceMatcherTest extends TestCase
{

    /**
     * Constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public function testGetGroupedOpCodes()
    {
        // Test with default options.
        $sequenceMatcher = new SequenceMatcher('54321ABXDE12345', '54321ABxDE12345');
        $this->assertEquals(
            [[['equal', 4, 7, 4, 7], ['replace', 7, 8, 7, 8], ['equal', 8, 11, 8, 11]]],
            $sequenceMatcher->getGroupedOpCodes()
        );

        // Test with trimEqual disabled.
        $sequenceMatcher = new SequenceMatcher('54321ABXDE12345', '54321ABxDE12345', ['trimEqual' => false]);
        $this->assertEquals(
            [[['equal', 0, 3, 0, 3]], [['equal', 4, 7, 4, 7], ['replace', 7, 8, 7, 8], ['equal', 8, 11, 8, 11]]],
            $sequenceMatcher->getGroupedOpCodes()
        );

        // Test with ignoreWhitespace enabled.
        // Note: The sequenceMatcher evaluates the string character by character. Option ignoreWhitespace will ignore
        //       if the difference if the character is a tab in one sequence and a space in the other.
        $sequenceMatcher = new SequenceMatcher(
            "\t54321ABXDE12345 ",
            " 54321ABXDE12345\t",
            ['ignoreWhitespace' => true]
        );
        $this->assertEquals(
            [[['equal', 14, 17, 14, 17]]],
            $sequenceMatcher->getGroupedOpCodes()
        );

        // Test with ignoreCase enabled.
        $sequenceMatcher = new SequenceMatcher('54321ABXDE12345', '54321ABxDE12345', ['ignoreCase' => true]);
        $this->assertEquals(
            [[['equal', 12, 15, 12, 15]]],
            $sequenceMatcher->getGroupedOpCodes()
        );
    }
}
