<?php
declare(strict_types=1);
namespace jblond;

use jblond\Diff\SequenceMatcher;

/**
 * Diff
 *
 * A comprehensive library for generating differences between two strings
 * in multiple formats (unified, side by side HTML etc)
 *
 * PHP version 7.1 or greater
 *
 * @package jblond
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.12
 * @link https://github.com/JBlond/php-diff
 */
class Diff
{
    /**
     * @var array The "old" sequence to use as the basis for the comparison.
     */
    private $old = null;

    /**
     * @var array The "new" sequence to generate the changes for.
     */
    private $new = null;

    /**
     * @var array Array containing the generated op codes for the differences between the two items.
     */
    private $groupedCodes = null;

    /**
     * @var array Associative array of the default options available for the diff class and their default value.
     */
    private $defaultOptions = array(
        'context' => 3,
        'ignoreNewLines' => false,
        'ignoreWhitespace' => false,
        'ignoreCase' => false,
        'labelDifferences'=>'Differences'
    );

    /**
     * @var array Array of the options that have been applied for generating the diff.
     */
    public $options = array();

    /**
     * The constructor.
     *
     * @param array $oldArray Array containing the lines of the first string to compare.
     * @param array $newArray Array containing the lines for the second string to compare.
     * @param array $options Array for the options
     */
    public function __construct(array $oldArray, array $newArray, array $options = array())
    {
        $this->old = $oldArray;
        $this->new = $newArray;

        if (is_array($options)) {
            $this->options = array_merge($this->defaultOptions, $options);
        } else {
            $this->options = $this->defaultOptions;
        }
    }


    /**
     * Render a diff using the supplied rendering class and return it.
     *
     * @param object $renderer object $renderer An instance of the rendering object to use for generating the diff.
     * @return mixed The generated diff. Exact return value depends on the rendered.
     */
    public function render($renderer)
    {
        $renderer->diff = $this;
        return $renderer->render();
    }

    /**
     * Get a range of lines from $start to $end from the first comparison string
     * and return them as an array. If no values are supplied, the entire string
     * is returned. It's also possible to specify just one line to return only
     * that line.
     *
     * @param int $start The starting number.
     * @param int|null $end The ending number. If not supplied, only the item in $start will be returned.
     * @return array Array of all of the lines between the specified range.
     */
    public function getOld(int $start = 0, $end = null) : array
    {
        if ($start == 0 && $end === null) {
            return $this->old;
        }

        if ($end === null) {
            return array_slice($this->old, $start, 1);
        }

        $length = $end - $start;
        return array_slice($this->old, $start, $length);
    }

    /**
     * Get a range of lines from $start to $end from the second comparison string
     * and return them as an array. If no values are supplied, the entire string
     * is returned. It's also possible to specify just one line to return only
     * that line.
     *
     * @param int $start The starting number.
     * @param int|null $end The ending number. If not supplied, only the item in $start will be returned.
     * @return array Array of all of the lines between the specified range.
     */
    public function getNew(int $start = 0, $end = null) : array
    {
        if ($start == 0 && $end === null) {
            return $this->new;
        }

        if ($end === null) {
            return array_slice($this->new, $start, 1);
        }

        $length = $end - $start;
        return array_slice($this->new, $start, $length);
    }

    /**
     * Generate a list of the compiled and grouped op codes for the differences between the
     * two strings. Generally called by the renderer, this class instantiates the sequence
     * matcher and performs the actual diff generation and return an array of the op codes
     * for it. Once generated, the results are cached in the diff class instance.
     *
     * @return array Array of the grouped op codes for the generated diff.
     */
    public function getGroupedOpcodes() : array
    {
        if (!is_null($this->groupedCodes)) {
            return $this->groupedCodes;
        }

        $sequenceMatcher = new SequenceMatcher($this->old, $this->new, $this->options, null);
        $this->groupedCodes = $sequenceMatcher->getGroupedOpcodes($this->options['context']);
        return $this->groupedCodes;
    }
}
