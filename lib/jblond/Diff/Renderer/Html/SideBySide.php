<?php
declare(strict_types=1);
namespace jblond\Diff\Renderer\Html;

/**
 * Side by Side HTML diff generator for PHP DiffLib.
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
 * Class Diff_Renderer_Html_SideBySide
 */
class SideBySide extends HtmlArray
{
    /**
     * Render a and return diff with changes between the two sequences
     * displayed side by side.
     *
     * @return string The generated side by side diff.
     */
    public function render() : string
    {
        $changes = parent::render();

        $html = '';
        if (empty($changes)) {
            return $html;
        }

        $html .= $this->generateTableHeader();

        foreach ($changes as $i => $blocks) {
            // If this is a separate block, we're condensing code so output ...,
            // indicating a significant portion of the code has been collapsed as
            // it is the same
            if ($i > 0) {
                $html .= $this->generateSkippedTable();
            }

            foreach ($blocks as $change) {
                $html .= '<tbody class="Change'.ucfirst($change['tag']).'">';
                switch ($change['tag']) {
                    // Equal changes should be shown on both sides of the diff
                    case 'equal':
                        $html .= $this->generateTableRowsEqual($change);
                        break;
                    // Added lines only on the right side
                    case 'insert':
                        $html .= $this->generateTableRowsInsert($change);
                        break;
                    // Show deleted lines only on the left side
                    case 'delete':
                        $html .= $this->generateTableRowsDelete($change);
                        break;
                    // Show modified lines on both sides
                    case 'replace':
                        $html .= $this->generateTableRowsReplace($change);
                        break;
                }
                $html .= '</tbody>';
            }
        }
        $html .= '</table>';
        return $html;
    }

    /**
     * Generates a string representation of a predefined table and its head with
     * titles from options.
     *
     * @return string Html code representation of the table's header.
     */
    private function generateTableHeader() : string
    {
        $html = '<table class="Differences DifferencesSideBySide">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th colspan="2">'.$this->options['title_a'].'</th>';
        $html .= '<th colspan="2">'.$this->options['title_b'].'</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        return $html;
    }

    /**
     * Generates a string representation of empty table body.
     *
     * @return string Html code representing empty table body.
     */
    private function generateSkippedTable() : string
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
    private function generateTableRowsEqual(array &$change) : string
    {
        $html = "";
        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>'.$fromLine.'</th>';
            $html .= '<td class="Left"><span>'.$line.'</span>&#xA0;</td>';
            $html .= '<th>'.$toLine.'</th>';
            $html .= '<td class="Right"><span>'.$line.'</span>&#xA0;</td>';
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
    private function generateTableRowsInsert(array &$change) : string
    {
        $html = "";
        foreach ($change['changed']['lines'] as $no => $line) {
            $toLine = $change['changed']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>&#xA0;</th>';
            $html .= '<td class="Left">&#xA0;</td>';
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
    private function generateTableRowsDelete(array &$change) : string
    {
        $html = "";
        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;
            $html .= '<tr>';
            $html .= '<th>'.$fromLine.'</th>';
            $html .= '<td class="Left"><del>'.$line.'</del>&#xA0;</td>';
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
    private function generateTableRowsReplace(array &$change) : string
    {
        $html = "";

        if (count($change['base']['lines']) >= count($change['changed']['lines'])) {
            foreach ($change['base']['lines'] as $no => $line) {
                $fromLine = $change['base']['offset'] + $no + 1;
                $html .= '<tr>';
                $html .= '<th>'.$fromLine.'</th>';
                $html .= '<td class="Left"><span>'.$line.'</span>&#xA0;</td>';
                if (!isset($change['changed']['lines'][$no])) {
                    $toLine = '&#xA0;';
                    $changedLine = '&#xA0;';
                } else {
                    $toLine = $change['changed']['offset'] + $no + 1;
                    $changedLine = '<span>'.$change['changed']['lines'][$no].'</span>';
                }
                $html .= '<th>'.$toLine.'</th>';
                $html .= '<td class="Right">'.$changedLine.'</td>';
                $html .= '</tr>';
            }
        } else {
            foreach ($change['changed']['lines'] as $no => $changedLine) {
                if (!isset($change['base']['lines'][$no])) {
                    $fromLine = '&#xA0;';
                    $line = '&#xA0;';
                } else {
                    $fromLine = $change['base']['offset'] + $no + 1;
                    $line = '<span>'.$change['base']['lines'][$no].'</span>';
                }
                $html .= '<tr>';
                $html .= '<th>'.$fromLine.'</th>';
                $html .= '<td class="Left"><span>'.$line.'</span>&#xA0;</td>';
                $toLine = $change['changed']['offset'] + $no + 1;
                $html .= '<th>'.$toLine.'</th>';
                $html .= '<td class="Right">'.$changedLine.'</td>';
                $html .= '</tr>';
            }
        }

        return $html;
    }
}
