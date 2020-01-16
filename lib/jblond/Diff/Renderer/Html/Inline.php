<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Html;

/**
 * Inline HTML diff generator for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package jblond\Diff\Renderer\Html
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @copyright (c) 2009 Chris Boulton
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.15
 * @link https://github.com/JBlond/php-diff
 */
class Inline extends HtmlArray
{
    /**
     * Render a and return diff-view with changes between the two sequences displayed side by side. (under each other)
     *
     * @return string The generated inline diff-view.
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
<table class="Differences DifferencesInline">
    <thead>
        <tr>
            <th>{$this->options['title_a']}</th>
            <th>{$this->options['title_b']}</th>
            <th>Differences</th>
        </tr>
    </thead>
HTML;
    }

    /**
     * Generates a string representation of table rows showing text with no difference.
     *
     * @param array $change Contains the op-codes about the changes between two blocks.
     *
     * @return string HTML code representing table rows showing text with no difference.
     */
    public function generateTableRowsEqual(array $change): string
    {
        $html = '';

        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine   = $change['base']['offset'] + $no + 1;
            $toLine     = $change['changed']['offset'] + $no + 1;

            $html .= <<<HTML
<tr>
    <th>$fromLine</th>
    <th>$toLine</th>
    <td class="Left">$line</td>
</tr>
HTML;
        }

        return $html;
    }

    /**
     * Generates a string representation of table rows showing added text.
     *
     * @param array $change Contains the op-codes about the changes between two blocks of text.
     *
     * @return string HTML code representing table rows showing with added text.
     */
    public function generateTableRowsInsert(array $change): string
    {
        $html = '';

        foreach ($change['changed']['lines'] as $no => $line) {
            $toLine = $change['changed']['offset'] + $no + 1;

            $html .= <<<HTML
<tr>
    <th>&#xA0;</th>
    <th>$toLine</th>
    <td class="Right">
        <ins>$line</ins>
        &#xA0;
    </td>
</tr>
HTML;
        }

        return $html;
    }

    /**
     * Generates a string representation of table rows showing removed text.
     *
     * @param array $change Contains the op-codes about the changes between two blocks of text.
     *
     * @return string HTML code representing table rows showing removed text.
     */
    public function generateTableRowsDelete(array $change): string
    {
        $html = '';

        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;

            $html .= <<<HTML
<tr>
    <th>$fromLine</th>
    <th>&#xA0;</th>
    <td class="Left">
        <del>$line</del>
        &#xA0;
    </td>
</tr>
HTML;
        }

        return $html;
    }

    /**
     * Generates a string representation of table rows showing partially modified text.
     *
     * @param array $change Contains the op-codes about the changes between two blocks of text.
     *
     * @return string Html code representing table rows showing modified text.
     */
    public function generateTableRowsReplace(array &$change): string
    {
        $html = '';

        foreach ($change['base']['lines'] as $no => $line) {
            $fromLine = $change['base']['offset'] + $no + 1;

            $html .= <<<HTML
<tr>
    <th>$fromLine</th>
    <th>&#xA0;</th>
    <td class="Left">
        <span>$line</span>
    </td>
</tr>
HTML;
        }

        foreach ($change['changed']['lines'] as $no => $line) {
            $toLine = $change['changed']['offset'] + $no + 1;

            $html .= <<<HTML
<tr>
    <th>&#xA0;</th>
    <th>$toLine</th>
    <td class="Right">
        <span>$line</span>
    </td>
</tr>
HTML;
        }

        return $html;
    }
}
