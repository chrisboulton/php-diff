<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Text;

use jblond\Diff\Renderer\MainRendererAbstract;

/**
 * Context diff generator for PHP DiffLib.
 *
 * PHP version 7.3 or greater
 *
 * @package         jblond\Diff\Renderer\Text
 * @author          Chris Boulton <chris.boulton@interspire.com>
 * @author          Mario Brandt <leet31337@web.de>
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2009 Chris Boulton
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class Context extends MainRendererAbstract
{
    /**
     * @var array Array of the different op-code tags and how they map to the context diff-view equivalent.
     */
    private $tagMap = [
        'insert'  => '+',
        'delete'  => '-',
        'replace' => '!',
        'equal'   => ' ',
    ];

    /**
     * Render and return a context formatted (old school!) diff-view.
     *
     * @link https://www.gnu.org/software/diffutils/manual/html_node/Detailed-Context.html#Detailed-Context
     *
     * @return string|false The generated diff-view or false when there's no difference.
     */
    public function render()
    {
        $diff    = false;
        $opCodes = $this->diff->getGroupedOpCodes();

        foreach ($opCodes as $group) {
            $diff     .= "***************\n";
            $lastItem = count($group) - 1;
            $start1   = $group['0']['1'];
            $end1     = $group[$lastItem]['2'];
            $start2   = $group['0']['3'];
            $end2     = $group[$lastItem]['4'];

            // Line to line header for version 1.
            $diffStart = $end1 - $start1 >= 2 ? $start1 + 1 . ',' : '';
            $diff      .= '*** ' . $diffStart . $end1 . " ****\n";

            // Line to line header for version 2.
            $diffStart = $end2 - $start2 >= 2 ? ($start2 + 1) . ',' : '';
            $separator = '--- ' . $diffStart . $end2 . " ----\n";

            // Check for visible changes by replace or delete operations.
            if (!empty(array_intersect(['replace', 'delete'], array_column($group, 0)))) {
                // Line differences between versions or lines of version 1 are removed from version 2.
                // Add all operations to diff-view of version 1, except for insert.
                $filteredGroups = $this->filterGroups($group, 'insert');
                $filteredGroups = $this->filterGroups($filteredGroups, 'ignore');
                foreach ($filteredGroups as [$tag, $start1, $end1, $start2, $end2]) {
                    $diff .= $this->tagMap[$tag] . ' ' .
                        implode(
                            "\n" . $this->tagMap[$tag] . ' ',
                            $this->diff->getArrayRange($this->diff->getVersion1(), $start1, $end1)
                        ) . "\n";
                }
            }

            $diff .= $separator;

            // Check for visible changes by replace or insert operations.
            if (!empty(array_intersect(['replace', 'insert'], array_column($group, 0)))) {
                // Line differences between versions or lines are inserted into version 2.
                // Add all operations to diff-view of version 2, except for delete.
                $filteredGroups = $this->filterGroups($group, 'delete');
                $filteredGroups = $this->filterGroups($filteredGroups, 'ignore');
                foreach ($filteredGroups as [$tag, $start1, $end1, $start2, $end2]) {
                    $diff .= $this->tagMap[$tag] . ' ' .
                        implode(
                            "\n" . $this->tagMap[$tag] . ' ',
                            $this->diff->getArrayRange($this->diff->getVersion2(), $start2, $end2)
                        ) . "\n";
                }
            }
        }

        return $diff;
    }

    /**
     * Filter out groups by tag.
     *
     * Given an array of groups, all groups which don't have the specified tag are returned.
     *
     * @param   array   $groups       A series of opCode groups.
     * @param   string  $excludedTag  Name of the opCode Tag to filter out.
     *
     * @return array Filtered opCode Groups.
     */
    private function filterGroups(array $groups, string $excludedTag): array
    {
        return array_filter(
            $groups,
            function ($operation) use ($excludedTag) {
                return $operation[0] != $excludedTag;
            }
        );
    }
}
