<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Text;

use jblond\Diff\Renderer\MainRendererAbstract;

/**
 * Unified diff generator for PHP DiffLib.
 *
 * PHP version 7.3 or greater
 *
 * @package         jblond\Diff\Renderer\Text
 * @author          Chris Boulton <chris.boulton@interspire.com>
 * @author          Mario Brandt <leet31337@web.de>
 * @copyright   (c) 2020 Mario Brandt
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */

/**
 * Class Diff_Renderer_Text_Unified
 */
class Unified extends MainRendererAbstract
{
    /**
     * Render and return a unified diff.
     *
     * @return string|false The generated diff-view or false when there's no difference.
     */
    public function render()
    {
        $diff    = false;
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

            $diff .= '@@ -' . ($i1 + 1) . ',' . ($i2 - $i1) . ' +' . ($j1 + 1) . ',' . ($j2 - $j1) . " @@\n";
            foreach ($group as [$tag, $i1, $i2, $j1, $j2]) {
                if ($tag == 'equal') {
                    $diff .= ' ' .
                        implode(
                            "\n ",
                            $this->diff->getArrayRange($this->diff->getVersion1(), $i1, $i2)
                        ) . "\n";
                    continue;
                }
                if ($tag == 'replace' || $tag == 'delete') {
                    $diff .= '-' .
                        implode(
                            "\n-",
                            $this->diff->getArrayRange($this->diff->getVersion1(), $i1, $i2)
                        ) . "\n";
                }
                if ($tag == 'replace' || $tag == 'insert') {
                    $diff .= '+' .
                        implode(
                            "\n+",
                            $this->diff->getArrayRange($this->diff->getVersion2(), $j1, $j2)
                        ) . "\n";
                }
            }
        }

        return $diff;
    }
}
