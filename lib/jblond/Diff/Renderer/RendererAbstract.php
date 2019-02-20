<?php
declare(strict_types=1);
namespace jblond\Diff\Renderer;

/**
 * Abstract class for diff renderers in PHP DiffLib.
 *
 * PHP version 7.1 or greater
 *
 * @package jblond\Diff\Renderer
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.10
 * @link https://github.com/JBlond/php-diff
 */
abstract class RendererAbstract
{
    /**
     * @var object Instance of the diff class that this renderer is generating the rendered diff for.
     */
    public $diff;

    /**
     * @var array Array of the default options that apply to this renderer.
     */
    protected $defaultOptions = array(
        'title_a' => 'Old Version',
        'title_b' => 'New Version',
    );

    /**
     * @var array Array containing the user applied and merged default options for the renderer.
     */
    protected $options = array();

    /**
     * The constructor. Instantiates the rendering engine and if options are passed,
     * sets the options for the renderer.
     *
     * @param array $options Optionally, an array of the options for the renderer.
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Set the options of the renderer to those supplied in the passed in array.
     * Options are merged with the default to ensure that there aren't any missing
     * options.
     *
     * @param array $options Array of options to set.
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }
}
