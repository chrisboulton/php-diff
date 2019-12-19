<?php

declare(strict_types=1);

namespace jblond;

use jblond\Diff\SequenceMatcher;

/**
 * Diff
 *
 * A comprehensive library for comparing two strings and generating the differences between them in multiple formats.
 * (unified, side by side, inline, HTML, etc.)
 *
 * PHP version 7.1 or greater
 *
 * @package jblond
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.15
 * @link https://github.com/JBlond/php-diff
 */
class Diff
{
    /**
     * @var array   The "old" string to compare to.
     *              Each element contains a line of this string.
     */
    private $old;

    /**
     * @var array   The "new" string to compare.
     *              Each element contains a line of this string.
     */
    private $new;

    /**
     * @var array   Contains generated op-codes which represent the differences between "old" and "new".
     */
    private $groupedCodes;

    /**
     * @var array   Associative array containing the default options available for the diff class and their default
     *              value.
     *              - context           The amount of lines to include around blocks that differ.
     *              - ignoreWhitespace  When true, tabs and spaces are ignored while comparing.
     *              - ignoreCase        When true, character casing is ignored while comparing.
     */
    private $defaultOptions = [
        'context'           => 3,
        'ignoreWhitespace'  => false,
        'ignoreCase'        => false,
    ];

    /**
     * @var array   Associative array containing the options that will be applied for generating the diff.
     *              The key-value pairs are set at the contructor of this class.
     *              @see Diff::setOptions()
     */
    private $options = [];

    /**
     * The constructor.
     *
     * The first two parameters define the data to compare to eachother.
     * The values can be of type string or array.
     * If the type is string, it's splitted into array elements by line-end characters.
     *
     * Options for comparison can be set by using the third parameter. The format of this value is expected to be a
     * associative array where each key-value pair represents an option and its value (E.g. ['context' => 3], ...).
     * When a keyName matches the name of a default option, that option's value will be overridden by the key's value.
     * Any other keyName (and it's value) can be added as an option, but will not be used if not implemented.
     * @see Diff::$defaultOptions
     *
     * @param string|array  $old        Data to compare to.
     * @param string|array  $new        Data to compare.
     * @param array         $options    User defined option values.
     */
    public function __construct($old, $new, array $options = [])
    {
        //Convert "old" and "new" into an array of lines when they are strings.
        $this->old = $this->getArgumentType($old) ? preg_split("/\r\n|\n|\r/", $old) : $old;
        $this->new = $this->getArgumentType($new) ? preg_split("/\r\n|\n|\r/", $new) : $new;

        //Override the default options, define others.
        $this->setOptions($options);
    }

    /**
     * Set the options to be used by the sequence matcher, called by this class.
     * @see Diff::getGroupedOpcodes()
     *
     * When a keyName matches the name of a default option, that option's value will be overridden by the key's value.
     * Any other keyName (and it's value) will be added as an option, but will not be used if not implemented.
     * @see Diff::$defaultOptions
     *
     * @param array $options User defined option names and values.
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Get the lines of "old".
     *
     * @return array Contains the lines of the "old" string to compare to.
     */
    public function getOld(): array
    {
        return $this->old;
    }

    /**
     * Get the lines of "new".
     *
     * @return array Contains the lines of the "new" string to compare.
     */
    public function getNew(): array
    {
        return $this->new;
    }


    /**
     * Render a diff-view using a rendering class and get its results.
     *
     * @param object $renderer An instance of the rendering object, used for generating the diff-view.
     *
     * @return mixed The generated diff-view. The type of the return value depends on the applied rendereder.
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
     * If the argument for the second parameter is ommitted, the element defined as start will be returned.
     *
     * @param array     $array  The source array.
     * @param int       $start  The first element of the range to get.
     * @param int|null  $end    The last element of the range to get.
     *                          If not supplied, only the element at start will be returned.
     *
     * @throws \OutOfRangeException When the value of start or end are invalid to define a range.
     *
     * @return array Array containing all of the elements of the specified range.
     */
    public function getArrayRange(array $array, int $start = 0, $end = null): array
    {
        if ($start < 0 || $end < 0 || $end < $start) {
            throw new \OutOfRangeException('Start parameter must be lower than End parameter while both are positive!');
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
     * Get the type of a variable.
     *
     * The return value depend on the type of variable:
     * 0    If the type is 'array'
     * 1    if the type is 'string'
     *
     * @param mixed $var    Variable to get type from.
     *
     * @throws \InvalidArgumentException    When the type isn't 'array' or 'string'.
     *
     * @return int  Number indicating the type of the variable. 0 for array type and 1 for string type.
     */
    public function getArgumentType($var): int
    {
        switch (true) {
            case (is_array($var)):
                return 0;
            case (is_string($var)):
                return 1;
            default:
                throw new \InvalidArgumentException('Invalid argument type! Argument must be of type array or string.');
        }

        $length = $end - $start;
        return array_slice($this->new, $start, $length);
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
    public function getGroupedOpcodes(): array
    {
        if (!is_null($this->groupedCodes)) {
            //Return the cached results.
            return $this->groupedCodes;
        }

        //Get and cahche the grouped op-codes.
        $sequenceMatcher    = new SequenceMatcher($this->old, $this->new, $this->options, null);
        $this->groupedCodes = $sequenceMatcher->getGroupedOpcodes($this->options['context']);

        return $this->groupedCodes;
    }
}
