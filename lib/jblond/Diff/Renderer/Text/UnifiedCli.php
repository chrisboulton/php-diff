<?php

namespace jblond\Diff\Renderer\Text;

use InvalidArgumentException;
use jblond\cli\CliColors;
use jblond\Diff\Renderer\MainRendererAbstract;

/**
 * Unified diff generator for PHP DiffLib.
 *
 * PHP version 7.3 or greater
 *
 * @package         jblond\Diff\Renderer\Text
 * @author          Mario Brandt <leet31337@web.de>
 * @copyright (c)   2020 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class UnifiedCli extends MainRendererAbstract
{

    /**
     * @var CliColors
     */
    private $colors;

    /**
     * @var array   Associative array containing the default options available for this renderer and their default
     *              value.
     */
    private $subOptions = [];

    /**
     * UnifiedCli constructor.
     *
     * @param   array  $options  Custom defined options for the UnifiedCli diff renderer.
     *
     */
    public function __construct(array $options = [])
    {
        parent::__construct();
        $this->setOptions($this->subOptions);
        $this->setOptions($options);
        $this->colors = new CliColors();
    }

    /**
     * Render and return a unified diff.
     *
     * @return string Direct Output to the console
     * @throws InvalidArgumentException
     */
    public function render(): string
    {
        return $this->output();
    }

    /**
     * Render and return a unified colored diff.
     *
     * @return string
     */
    private function output(): string
    {
        $diff    = '';
        $opCodes = $this->diff->getGroupedOpCodes();
        foreach ($opCodes as $group) {
            $lastItem = count($group) - 1;
            $i1       = $group['0']['1'];
            $i2       = $group[$lastItem]['2'];
            $j1       = $group['0']['3'];
            $j2       = $group[$lastItem]['4'];

            if ($i1 == 0 && $i2 == 0) {
                $i1 = -1;
                $i2 = -1;
            }

            $diff .= $this->colorizeString(
                '@@ -' . ($i1 + 1) . ',' . ($i2 - $i1) . ' +' . ($j1 + 1) . ',' . ($j2 - $j1) . " @@\n",
                'purple'
            );
            foreach ($group as [$tag, $i1, $i2, $j1, $j2]) {
                if ($tag == 'equal') {
                    $string = implode(
                        "\n ",
                        $this->diff->getArrayRange($this->diff->getVersion1(), $i1, $i2)
                    );
                    $diff   .= $this->colorizeString(' ' . $string . "\n", 'grey');
                    continue;
                }
                if ($tag == 'replace' || $tag == 'delete') {
                    $string = implode(
                        "\n- ",
                        $this->diff->getArrayRange($this->diff->getVersion1(), $i1, $i2)
                    );
                    $diff   .= $this->colorizeString('-' . $string . "\n", 'light_red');
                }
                if ($tag == 'replace' || $tag == 'insert') {
                    $string = implode(
                        "\n+",
                        $this->diff->getArrayRange($this->diff->getVersion2(), $j1, $j2)
                    );
                    $diff   .= $this->colorizeString('+' . $string . "\n", 'light_green');
                }
            }
        }

        return $diff;
    }

    /**
     * @param string $string
     * @param string $color
     *
     * @return string
     */
    private function colorizeString(string $string, string $color = ''): string
    {
        if ($this->options['cliColor']) {
            return $this->colors->getColoredString($string, $color);
        }

        return $string;
    }
}
