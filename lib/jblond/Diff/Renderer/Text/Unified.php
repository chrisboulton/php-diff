<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Text;

use jblond\Diff\Renderer\RendererAbstract;

/**
 * Unified diff generator for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package  jblond\Diff\Renderer\Text
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.15
 * @link https://github.com/JBlond/php-diff
 */

/**
 * Class Diff_Renderer_Text_Unified
 */
class Unified extends RendererAbstract
{
    /**
     * Render and return a unified diff.
     *
     * @return string The unified diff.
     */
    public function render(): string
    {
        $diff = '';
        $opCodes = $this->diff->getGroupedOpcodes();
        foreach ($opCodes as $group) {
            $lastItem = count($group) - 1;
            $i1 = $group['0']['1'];
            $i2 = $group[$lastItem]['2'];
            $j1 = $group['0']['3'];
            $j2 = $group[$lastItem]['4'];

            if ($i1 == 0 && $i2 == 0) {
                $i1 = -1;
                $i2 = -1;
            }

            $diff .= '@@ -' . ($i1 + 1) . ',' . ($i2 - $i1) . ' +' . ($j1 + 1) . ',' . ($j2 - $j1) . " @@\n";
            foreach ($group as $code) {
                list($tag, $i1, $i2, $j1, $j2) = $code;
                if ($tag == 'equal') {
                    $diff .= ' ' .
                        implode(
                            "\n ",
                            $this->diff->getArrayRange($this->diff->getOld(), $i1, $i2)
                        ) . "\n";
                } else {
                    if ($tag == 'replace' || $tag == 'delete') {
                        $diff .= '-' .
                            implode(
                                "\n-",
                                $this->diff->getArrayRange($this->diff->getOld(), $i1, $i2)
                            ) . "\n";
                    }

                    if ($tag == 'replace' || $tag == 'insert') {
                        $diff .= '+' .
                            implode(
                                "\n+",
                                $this->diff->getArrayRange($this->diff->getNew(), $j1, $j2)
                            ) . "\n";
                    }
                }
            }
        }
        return $diff;
    }
}
