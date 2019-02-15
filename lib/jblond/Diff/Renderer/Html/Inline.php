<?php
declare(strict_types=1);
namespace jblond\Diff\Renderer\Html;

/**
 * Inline HTML diff generator for PHP DiffLib.
 *
 * PHP version 7.1 or greater
 *
 * Copyright (c) 2009 Chris Boulton <chris.boulton@interspire.com>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the Chris Boulton nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package DiffLib
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.9
 * @link https://github.com/JBlond/php-diff
 */

/**
 * Class Diff_Renderer_Html_Inline
 */
class Inline extends HtmlArray
{
    /**
     * Render a and return diff with changes between the two sequences
     * displayed inline (under each other)
     *
     * @return string The generated inline diff.
     */
    public function render() : string
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
    public function generateTableHeader() : string
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
     * Generates a string representation of empty table body.
     *
     * @return string Html code representing empty table body.
     */
    public function generateSkippedTable() : string
    {
        $html = '<tbody class="Skipped">';
        $html .= '<th>&hellip;</th>';
        $html .= '<th>&hellip;</th>';
        $html .= '<td>&#xA0;</td>';
        $html .= '</tbody>';
        return $html;
    }

    /**
     * Generates a string representation of one or more rows of a table of lines of text with no difference.
     *
     * @param array &$change Array with data about changes.
     * @return string Html code representing one or more rows of text with no difference.
     */
    public function generateTableRowsEqual(array &$change) : string
    {
        $html = "";
        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>'.$fromLine.'</th>';
            $html .= '<th>'.$toLine.'</th>';
            $html .= '<td class="Left">'.$line.'</td>';
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
    public function generateTableRowsInsert(array &$change) : string
    {
        $html = "";
        foreach ($change['changed']['lines'] as $no => $line) {
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>&#xA0;</th>';
            $html .= '<th>'.$toLine.'</th>';
            $html .= '<td class="Right"><ins>'.$line.'</ins>&#xA0;</td>';
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
    public function generateTableRowsDelete(array &$change) : string
    {
        $html = "";
        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>'.$fromLine.'</th>';
            $html .= '<th>&#xA0;</th>';
            $html .= '<td class="Left"><del>'.$line.'</del>&#xA0;</td>';
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
    public function generateTableRowsReplace(array &$change) : string
    {
        $html = "";

        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>'.$fromLine.'</th>';
            $html .= '<th>&#xA0;</th>';
            $html .= '<td class="Left"><span>'.$line.'</span></td>';
            $html .= '</tr>';
        }

        foreach ($change['changed']['lines'] as $no => $line) {
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>&#xA0;</th>';
            $html .= '<th>'.$toLine.'</th>';
            $html .= '<td class="Right"><span>'.$line.'</span></td>';
            $html .= '</tr>';
        }

        return $html;
    }
}
