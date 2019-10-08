<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Html;

/**
 * Side by Side HTML diff generator for PHP DiffLib.
 *
 * PHP version 7.1 or greater
 *
 * @package jblond\Diff\Renderer\Html
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.14
 * @link https://github.com/JBlond/php-diff
 */
class SideBySide extends HtmlArray
{
    /**
     * Render a and return diff with changes between the two sequences
     * displayed side by side.
     *
     * @return string The generated side by side diff.
     */
    public function render(): string
    {
        $changes = parent::render();
        return parent::renderHtml($changes, $this);
    }

    /**
     * Generates a string representation of a predefined table and its head with
     * titles from options.
     *
     * @return string Html code representation of the table's header.
     */
    public function generateTableHeader(): string
    {
        $html = '<table class="Differences DifferencesSideBySide">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th colspan="2">' . $this->options['title_a'] . '</th>';
        $html .= '<th colspan="2">' . $this->options['title_b'] . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        return $html;
    }

    /**
     * Generates a string representation of empty table body.
     *
     * @return string Html code representing empty table body.
     */
    public function generateSkippedTable(): string
    {
        $html = '<tbody class="Skipped">';
        $html .= '<th>&hellip;</th><td>&#xA0;</td>';
        $html .= '<th>&hellip;</th><td>&#xA0;</td>';
        $html .= '</tbody>';
        return $html;
    }

    /**
     * Generates a string representation of one or more rows of a table of lines of text with no difference.
     *
     * @param array &$change Array with data about changes.
     * @return string Html code representing one or more rows of text with no difference.
     */
    public function generateTableRowsEqual(array &$change): string
    {
        $html = "";
        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>' . $fromLine . '</th>';
            $html .= '<td class="Left"><span>' . $line . '</span>&#xA0;</td>';
            $html .= '<th>' . $toLine . '</th>';
            $html .= '<td class="Right"><span>' . $line . '</span>&#xA0;</td>';
            $html .= '</tr>';
        }
        return $html;
    }

    /**
     * Generates a string representation of one or more rows of a table of lines, where new text was added.
     *
     * @param array &$change Array with data about changes.
     * @return string Html code representing one or more rows of added text.
     */
    public function generateTableRowsInsert(array &$change): string
    {
        $html = "";
        foreach ($change['changed']['lines'] as $no => $line) {
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>&#xA0;</th>';
            $html .= '<td class="Left">&#xA0;</td>';
            $html .= '<th>' . $toLine . '</th>';
            $html .= '<td class="Right"><ins>' . $line . '</ins>&#xA0;</td>';
            $html .= '</tr>';
        }
        return $html;
    }

    /**
     * Generates a string representation of one or more rows of a table of lines, where text was removed.
     *
     * @param array &$change Array with data about changes.
     * @return string Html code representing one or more rows of removed text.
     */
    public function generateTableRowsDelete(array &$change): string
    {
        $html = "";
        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>' . $fromLine . '</th>';
            $html .= '<td class="Left"><del>' . $line . '</del>&#xA0;</td>';
            $html .= '<th>&#xA0;</th>';
            $html .= '<td class="Right">&#xA0;</td>';
            $html .= '</tr>';
        }
        return $html;
    }

    /**
     * Generates a string representation of one or more rows of a table of lines, where text was partially modified.
     *
     * @param array &$change Array with data about changes.
     * @return string Html code representing one or more rows of modified.
     */
    public function generateTableRowsReplace(array &$change): string
    {
        $html = "";

        if (count($change['base']['lines']) >= count($change['changed']['lines'])) {
            foreach ($change['base']['lines'] as $no => $line) {
                $fromLine = $change['base']['offset'] + $no + 1;
                $html .= '<tr>';
                $html .= '<th>' . $fromLine . '</th>';
                $html .= '<td class="Left"><span>' . $line . '</span>&#xA0;</td>';
                if (!isset($change['changed']['lines'][$no])) {
                    $toLine = '&#xA0;';
                    $changedLine = '&#xA0;';
                } else {
                    $toLine = $change['changed']['offset'] + $no + 1;
                    $changedLine = '<span>' . $change['changed']['lines'][$no] . '</span>';
                }
                $html .= '<th>' . $toLine . '</th>';
                $html .= '<td class="Right">' . $changedLine . '</td>';
                $html .= '</tr>';
            }
        } else {
            foreach ($change['changed']['lines'] as $no => $changedLine) {
                if (!isset($change['base']['lines'][$no])) {
                    $fromLine = '&#xA0;';
                    $line = '&#xA0;';
                } else {
                    $fromLine = $change['base']['offset'] + $no + 1;
                    $line = '<span>' . $change['base']['lines'][$no] . '</span>';
                }
                $html .= '<tr>';
                $html .= '<th>' . $fromLine . '</th>';
                $html .= '<td class="Left"><span>' . $line . '</span>&#xA0;</td>';
                $toLine = $change['changed']['offset'] + $no + 1;
                $html .= '<th>' . $toLine . '</th>';
                $html .= '<td class="Right">' . $changedLine . '</td>';
                $html .= '</tr>';
            }
        }

        return $html;
    }
}
