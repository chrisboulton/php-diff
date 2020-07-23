<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Html;

use jblond\Diff\Renderer\MainRenderer;
use jblond\Diff\Renderer\SubRendererInterface;

/**
 * Inline HTML diff generator for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package       jblond\Diff\Renderer\Html
 * @author        Chris Boulton <chris.boulton@interspire.com>
 * @author        Mario Brandt <leet31337@web.de>
 * @author        Ferry Cools <info@DigiLive.nl>
 * @copyright (c) 2009 Chris Boulton
 * @license       New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version       2.2.0
 * @link          https://github.com/JBlond/php-diff
 */
class Inline extends MainRenderer implements SubRendererInterface
{
    /**
     * @var array   Associative array containing the default options available for this renderer and their default
     *              value.
     *              - format            Format of the texts.
     *              - insertMarkers     Markers for inserted text.
     *              - deleteMarkers     Markers for removed text.
     *              - title1            Title of the 1st version of text.
     *              - title2            Title of the 2nd version of text.
     */
    protected $subOptions = [
        'format'        => 'html',
        'insertMarkers' => ['<ins>', '</ins>'],
        'deleteMarkers' => ['<del>', '</del>'],
        'title1'        => 'Version1',
        'title2'        => 'Version2',
    ];

    /**
     * Inline constructor.
     *
     * @param array $options Custom defined options for the inline diff renderer.
     *
     * @see Inline::$subOptions
     */
    public function __construct(array $options = [])
    {
        parent::__construct();
        $this->setOptions($this->subOptions);
        $this->setOptions($options);
    }

    /**
     * Render and return a diff-view with changes between the two sequences displayed inline (under each other).
     *
     * @return string|false The generated diff-view or false when there's no difference.
     */
    public function render()
    {
        $changes = parent::renderSequences();

        return parent::renderOutput($changes, $this);
    }

    /**
     * Generates a string representation of the opening of a table and its header with titles from the sub renderer's
     * options.
     *
     * @return string HTML code representation of a table's header.
     */
    public function generateDiffHeader(): string
    {
        return <<<HTML
<table class="Differences DifferencesInline">
    <thead>
        <tr>
            <th>{$this->options['title1']}</th>
            <th>{$this->options['title2']}</th>
            <th>Differences</th>
        </tr>
    </thead>
HTML;
    }

    /**
     * Generates a string representation of table rows with lines that are skipped.
     *
     * @return string HTML code representation of skipped lines.
     */
    public function generateSkippedLines(): string
    {
        return <<<HTML
<tr>
    <th>&hellip;</th>
    <th>&hellip;</th>
    <td class="Left Skipped">&hellip;</td>
</tr>
HTML;
    }

    /**
     * Generate a string representation of table rows with lines without differences between both versions.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks.
     *
     * @return string HTML code representing table rows showing text without differences.
     */
    public function generateLinesEqual(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1;
            $toLine   = $changes['changed']['offset'] + $lineNo + 1;

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
     * Generates a string representation of table rows with lines that are added to the 2nd version.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string HTML code representing table rows showing with added text.
     */
    public function generateLinesInsert(array $changes): string
    {
        $html = '';

        foreach ($changes['changed']['lines'] as $lineNo => $line) {
            $toLine = $changes['changed']['offset'] + $lineNo + 1;

            $html .= <<<HTML
<tr>
    <th>&nbsp;</th>
    <th>$toLine</th>
    <td class="Right">
        <ins>$line</ins>
        &nbsp;
    </td>
</tr>
HTML;
        }

        return $html;
    }

    /**
     * Generates a string representation of table rows with lines that are removed from the 2nd version.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string HTML code representing table rows showing removed text.
     */
    public function generateLinesDelete(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1;

            $html .= <<<HTML
<tr>
    <th>$fromLine</th>
    <th>&nbsp;</th>
    <td class="Left">
        <del>$line</del>
        &nbsp;
    </td>
</tr>
HTML;
        }

        return $html;
    }

    /**
     * Generates a string representation of table rows with lines that are partially modified.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string Html code representing table rows showing modified text.
     */
    public function generateLinesReplace(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1;
            $line     = str_replace(["\0", "\1"], $this->options['deleteMarkers'], $line);
            $html     .= <<<HTML
<tr>
    <th>$fromLine</th>
    <th>&nbsp;</th>
    <td class="Left">
        <span>$line</span>
    </td>
</tr>
HTML;
        }

        foreach ($changes['changed']['lines'] as $lineNo => $line) {
            $toLine = $changes['changed']['offset'] + $lineNo + 1;
            $line   = str_replace(["\0", "\1"], $this->options['insertMarkers'], $line);
            $html   .= <<<HTML
<tr>
    <th>&nbsp;</th>
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
     * Generate a string representation of the start of a block.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string Start of the diff view.
     */
    public function generateBlockHeader(array $changes): string
    {
        return '<tbody class="Change' . ucfirst($changes['tag']) . '">';
    }

    /**
     * Generate a string representation of the end of a block.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string End of the block.
     */
    public function generateBlockFooter(array $changes): string
    {
        return '</tbody>';
    }

    /**
     * Generate a string representation of the end of a diff view.
     *
     * @return string End of the diff view.
     */
    public function generateDiffFooter(): string
    {
        return '</table>';
    }
}
