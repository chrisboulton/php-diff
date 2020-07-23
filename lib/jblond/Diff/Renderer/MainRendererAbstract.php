<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer;

use jblond\Diff;

/**
 * Abstract class for the main renderer in PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package     jblond\Diff\Renderer
 * @author      Mario Brandt <leet31337@web.de>
 * @author      Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2009 Chris Boulton
 * @license     New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version     2.2.0
 * @link        https://github.com/JBlond/php-diff
 */
abstract class MainRendererAbstract
{

    /**
     * @var Diff $diff Instance of the diff class that this renderer is generating the rendered diff for.
     */
    public $diff;

    /**
     * @var array   Associative array containing the default options available for this renderer and their default
     *              value.
     *              - tabSize           The amount of spaces to replace a tab character with.
     *              - format            The format of the input texts.
     *              - cliColor          Colorized output for cli.
     *              - deleteMarkers     Markers for removed text.
     *              - insertMarkers     Markers for inserted text.
     *              - equalityMarkers   Markers for unchanged and changed lines.
     *              - insertColors      Fore- and background color for inserted text. Only when cloColor = true.
     *              - deleteColors      Fore- and background color for removed text. Only when cloColor = true.
     */
    protected $mainOptions = [
        'tabSize'         => 4,
        'format'          => 'plain',
        'cliColor'        => false,
        'deleteMarkers'   => ['', ''],
        'insertMarkers'   => ['', ''],
        'equalityMarkers' => ['', ''],
        'insertColors'    => ['black', 'green'],
        'deleteColors'    => ['black', 'red'],
    ];

    /**
     * @var array Array containing a merge between the default options and user applied options for the renderer.
     * @see MainRendererAbstract::$mainOptions
     */
    protected $options = [];

    /**
     * The constructor. Instantiates the rendering engine and if options are passed,
     * sets the options for the renderer.
     *
     * @param array $options Optionally, an array of the options for the renderer.
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Set the options of the main renderer to the supplied options.
     *
     * Options are merged with the default to ensure that there aren't any missing options.
     * When custom options are added to the default ones, they can be overwritten, but they can't be removed.
     * @see MainRendererAbstract::$mainOptions
     *
     * @param array $options Array of options to set.
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->mainOptions, $this->options, $options);
    }
}
