<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Html;

/**
 * Inline HTML diff generator for PHP DiffLib.
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
class Inline extends HtmlArray
{
    /**
     * Render a and return diff with changes between the two sequences
     * displayed inline (under each other)
     *
     * @return string The generated inline diff.
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
        $html = '<table class="Differences DifferencesInline">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Old</th>';
        $html .= '<th>New</th>';
        $html .= '<th>Differences</th>';
        $html .= '</tr>';
        $html .= '</thead>';
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
            $html .= '<th>' . $toLine . '</th>';
            $html .= '<td class="Left">' . $line . '</td>';
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
            $html .= '<th>&#xA0;</th>';
            $html .= '<td class="Left"><del>' . $line . '</del>&#xA0;</td>';
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

        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>' . $fromLine . '</th>';
            $html .= '<th>&#xA0;</th>';
            $html .= '<td class="Left"><span>' . $line . '</span></td>';
            $html .= '</tr>';
        }

        foreach ($change['changed']['lines'] as $no => $line) {
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>&#xA0;</th>';
            $html .= '<th>' . $toLine . '</th>';
            $html .= '<td class="Right"><span>' . $line . '</span></td>';
            $html .= '</tr>';
        }

        return $html;
    }
}
