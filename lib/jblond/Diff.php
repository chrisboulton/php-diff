<?php

declare(strict_types=1);

namespace jblond;

use InvalidArgumentException;
use jblond\Diff\ConstantsInterface;
use jblond\Diff\SequenceMatcher;
use jblond\Diff\Similarity;
use OutOfRangeException;

/**
 * Diff
 *
 * A comprehensive library for comparing two strings and generating the differences between them in multiple formats.
 * (unified, side by side, inline, HTML, etc.)
 *
 * PHP version 7.3 or greater
 *
 * @package         jblond
 * @author          Chris Boulton <chris.boulton@interspire.com>
 * @author          Mario Brandt <leet31337@web.de>
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2020 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class Diff implements ConstantsInterface
{
    /**
     * @var array   The first version to compare.
     *              Each element contains a line of this string.
     */
    private $version1;

    /**
     * @var array   The second version to compare.
     *              Each element contains a line of this string.
     */
    private $version2;

    /**
     * @var array   Contains generated op-codes which represent the differences between version1 and version2.
     */
    private $groupedCodes;

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
     * @var array   Associative array containing the options that will be applied for generating the diff.
     *              The key-value pairs are set at the constructor of this class.
     *
     * @see Diff::setOptions()
     */
    private $options = [];

    /**
     * @var bool True when compared versions are identical, False otherwise.
     */
    private $identical;
    /**
     * @var float Similarity ratio of the two sequences.
     */
    private $similarity;

    /**
     * The constructor.
     *
     * The first two parameters define the data to compare to each other.
     * The values can be of type string or array.
     * If the type is string, it's split into array elements by line-end characters.
     *
     * Options for comparison can be set by using the third parameter. The format of this value is expected to be an
     * associative array where each key-value pair represents an option and its value (E.g. ['context' => 3], ...).
     * When a keyName matches the name of a default option, that option's value will be overridden by the key's value.
     * Any other keyName (and it's value) can be added as an option, but will not be used if not implemented.
     *
     * @param   string|array  $version1  Data to compare to.
     * @param   string|array  $version2  Data to compare.
     * @param   array         $options   User defined option values.
     *
     * @see Diff::$defaultOptions
     *
     */
    public function __construct($version1, $version2, array $options = [])
    {
        //Convert "old" and "new" into an array of lines when they are strings.
        $this->version1 = $this->getArgumentType($version1) ? preg_split("/\r\n|\n|\r/", $version1) : $version1;
        $this->version2 = $this->getArgumentType($version2) ? preg_split("/\r\n|\n|\r/", $version2) : $version2;

        //Override the default options, define others.
        $this->setOptions($options);
    }

    /**
     * Get the kind of variable.
     *
     * The return value depend on the type of variable:
     * 0    If the type is 'array'
     * 1    if the type is 'string'
     *
     * @param   mixed  $var  Variable to get type from.
     *
     * @return int Number indicating the type of the variable. 0 for array type and 1 for string type.
     * @throws InvalidArgumentException When the type isn't 'array' or 'string'.
     *
     */
    public function getArgumentType($var): int
    {
        switch (true) {
            case (is_array($var)):
                return 0;
            case (is_string($var)):
                return 1;
            default:
                throw new InvalidArgumentException('Invalid argument type! Argument must be of type array or string.');
        }
    }

    /**
     * Set the options to be used by the sequence matcher, called by this class.
     *
     * @param   array  $options  User defined option names and values.
     *
     * @see Diff::$defaultOptions
     *
     * @see Diff::getGroupedOpCodes()
     *
     * When a keyName matches the name of a default option, that option's value will be overridden by the key's value.
     * Any other keyName (and it's value) will be added as an option, but will not be used if not implemented.
     */
    public function setOptions(array $options): void
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Get the lines of "old".
     *
     * @return array Contains the lines of the "old" string to compare to.
     */
    public function getVersion1(): array
    {
        return $this->version1;
    }

    /**
     * Get the lines of "new".
     *
     * @return array Contains the lines of the "new" string to compare.
     */
    public function getVersion2(): array
    {
        return $this->version2;
    }

    /**
     * Render a diff-view using a rendering class and get its results.
     *
     * @param   object  $renderer  An instance of the rendering object, used for generating the diff-view.
     *
     * @return mixed The generated diff-view. The type of the return value depends on the applied renderer.
     */
    public function render(object $renderer)
    {
        $renderer->diff = $this;

        return $renderer->render();
    }

    /**
     * Get a range of elements of an array.
     *
     * The range must be defined as numeric
     * Start of the range is defined by the first parameter.
     * End of the range is defined by the second parameter.
     *
     * If the arguments for both parameters are omitted, the entire array will be returned.
     * If the argument for the second parameter is omitted, the element defined as start will be returned.
     *
     * @param   array     $array  The source array.
     * @param   int       $start  The first element of the range to get.
     * @param   int|null  $end    The last element of the range to get.
     *                            If not supplied, only the element at start will be returned.
     *
     * @return array Array containing all the elements of the specified range.
     * @throws OutOfRangeException When the value of start or end are invalid to define a range.
     *
     */
    public function getArrayRange(array $array, int $start = 0, ?int $end = null): array
    {
        if ($start < 0 || $end < 0 || $end < $start) {
            throw new OutOfRangeException('Start parameter must be lower than End parameter while both are positive!');
        }

        if ($start == 0 && $end === null) {
            //Return entire array.
            return $array;
        }

        if ($end === null) {
            //Return single element.
            return array_slice($array, $start, 1);
        }

        //Return range of elements.
        $length = $end - $start;

        return array_slice($array, $start, $length);
    }

    /**
     * Get if the compared versions are identical or have differences.
     *
     * @return bool True when identical.
     */
    public function isIdentical(): bool
    {
        if ($this->groupedCodes === null) {
            $this->getGroupedOpCodes();
        }

        return $this->identical;
    }

    /**
     * Generate a list of the compiled and grouped op-codes for the differences between two strings.
     *
     * Generally called by the renderer, this class instantiates the sequence matcher and performs the actual diff
     * generation and return an array of the op-codes for it.
     * Once generated, the results are cached in the diff class instance.
     *
     * @return array Array of the grouped op-codes for the generated diff.
     */
    public function getGroupedOpCodes(): array
    {
        if ($this->groupedCodes !== null) {
            //Return the cached results.
            return $this->groupedCodes;
        }

        //Get and cache the grouped op-codes.
        $sequenceMatcher    = new SequenceMatcher($this->version1, $this->version2, $this->options);
        $this->groupedCodes = $sequenceMatcher->getGroupedOpCodes();
        $opCodes            = $sequenceMatcher->getOpCodes();
        $this->identical    = count($opCodes) == 1 && $opCodes[0][0] == 'equal';

        return $this->groupedCodes;
    }

    /**
     * Get the similarity ratio of the two sequences.
     *
     * Once calculated, the results are cached in the diff class instance.
     *
     * @param   int  $method  Calculation method.
     *
     * @return float Similarity ratio.
     */
    public function getSimilarity(int $method = Similarity::CALC_DEFAULT): float
    {
        if ($this->similarity !== null) {
            return $this->similarity;
        }

        $similarity       = new Similarity($this->version1, $this->version2, $this->options);
        $this->similarity = $similarity->getSimilarity($method);

        return $this->similarity;
    }

    /**
     * Get diff statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $similarity = new Similarity($this->version1, $this->version2, $this->options);
        return $similarity->getDifference();
    }
}
