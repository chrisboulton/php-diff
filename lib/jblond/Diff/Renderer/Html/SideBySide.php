<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Html;

/**
 * Side by Side HTML diff generator for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package       jblond\Diff\Renderer\Html
 * @author        Chris Boulton <chris.boulton@interspire.com>
 * @author        Ferry Cools <info@DigiLive.nl>
 * @copyright (c) 2009 Chris Boulton
 * @license       New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version       1.15
 * @link          https://github.com/JBlond/php-diff
 */
class SideBySide extends HtmlArray
{
    /**
     * Render a and return diff-view with changes between the two sequences displayed side by side.
     *
     * @return string The generated side by side diff-view.
     */
    public function render(): string
    {
        $changes = parent::render();

        return parent::renderHtml($changes, $this);
    }

    /**
     * Generates a string representation of the opening of a predefined table and its header with titles from options.
     *
     * @return string HTML code representation of a table's header.
     */
    public function generateTableHeader(): string
    {
        return <<<HTML
<table class="Differences DifferencesSideBySide">
    <thead>
        <tr>
            <th colspan="2">{$this->options['title1']}</th>
            <th colspan="2">{$this->options['title2']}</th>
        </tr>
    </thead>
HTML;
    }

    /**
     * Generates a string representation of table rows showing lines are skipped.
     *
     * @return string HTML code representation of a table's header.
     */
    public function generateTableRowsSkipped(): string
    {
        return <<<HTML
<tr>
    <th>&hellip;</th>
    <td class="Left Skipped">&hellip;</td>
    <th>&hellip;</th>
    <td class="Right Skipped">&hellip;</td>
</tr>
HTML;
    }

    /**
     * Generates a string representation of table rows showing text with no difference.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks.
     *
     * @return string HTML code representing table rows showing text with no difference.
     */
    public function generateTableRowsEqual(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1;
            $toLine   = $changes['changed']['offset'] + $lineNo + 1;

            $html .= <<<HTML
<tr>
    <th>$fromLine</th>
    <td class="Left">
        <span>$line</span>
    </td>
    <th>$toLine</th>
    <td class="Right">
        <span>$line</span>
    </td>
</tr>
HTML;
        }

        return $html;
    }

    /**
     * Generates a string representation of table rows showing added text.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string HTML code representing table rows showing with added text.
     */
    public function generateTableRowsInsert(array $changes): string
    {
        $html = '';

        foreach ($changes['changed']['lines'] as $lineNo => $line) {
            $toLine = $changes['changed']['offset'] + $lineNo + 1;

            $html .= <<<HTML
<tr>
    <th>&nbsp;</th>
    <td class="Left">&nbsp;</td>
    <th>$toLine</th>
    <td class="Right">
        <ins>$line</ins>
    </td>
</tr>
HTML;
        }

        return $html;
    }

    /**
     * Generates a string representation of table rows showing removed text.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string HTML code representing table rows showing removed text.
     */
    public function generateTableRowsDelete(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1;

            $html = <<<HTML
<tr>
    <th>$fromLine</th>
    <td class="Left">
        <del>$line</del>
    </td>
    <th>&nbsp;</th>
    <td class="Right">&nbsp;</td>
</tr>
HTML;
        }

        return $html;
    }

    /**
     * Generates a string representation of table rows showing partially modified text.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string Html code representing table rows showing modified text.
     */
    public function generateTableRowsReplace(array $changes): string
    {
        $html = '';

        // Is below comparison result ever false?
        if (count($changes['base']['lines']) >= count($changes['changed']['lines'])) {
            foreach ($changes['base']['lines'] as $lineNo => $line) {
                $fromLine    = $changes['base']['offset'] + $lineNo + 1;
                $toLine      = "&nbsp;";
                $changedLine = "&nbsp;";
                if (isset($changes['changed']['lines'][$lineNo])) {
                    $toLine      = $changes['changed']['offset'] + $lineNo + 1;
                    $changedLine = $changes['changed']['lines'][$lineNo];
                }

                $html .= <<<HTML
<tr>
    <th>$fromLine</th>
    <td class="Left">
        <span>$line</span>
    </td>
    <th>$toLine</th>
    <td class="Right">
        <span>$changedLine</span>
    </td>
</tr>
HTML;
            }

            return $html;
        }

        foreach ($changes['changed']['lines'] as $lineNo => $changedLine) {
            $toLine   = $changes['changed']['offset'] + $lineNo + 1;
            $fromLine = "&nbsp;";
            $line     = "&nbsp;";
            if (isset($changes['base']['lines'][$lineNo])) {
                $fromLine = $changes['base']['offset'] + $lineNo + 1;
                $line     = $changes['base']['lines'][$lineNo];
            }

            $html .= <<<HTML
<tr>
    <th>$fromLine</th>
    <td class="Left">
        <span>$line</span>
    </td>
    <th>$toLine</th>
    <td class="Right">
        <span>$changedLine</span>
    </td>
</tr>
HTML;
        }

        return $html;
    }
}
