<?php

namespace jblond\Diff;

/**
 * Constant Interface
 *
 * Defines the library constants which needs to be shared across the different classes.
 *
 * PHP version 7.2 or greater
 *
 * @package         jblond
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2020 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.3.0
 * @link            https://github.com/JBlond/php-diff
 */
interface ConstantsInterface
{
    /**
     * Flag to disable ignore of successive empty/blank lines.
     */
    public const DIFF_IGNORE_LINE_NONE = 0;
    /**
     * Flag to ignore empty lines.
     */
    public const DIFF_IGNORE_LINE_EMPTY = 1;
    /**
     * Flag to ignore blank lines. (Lines which contain no or only non-printable characters.)
     */
    public const DIFF_IGNORE_LINE_BLANK = 2;
}
