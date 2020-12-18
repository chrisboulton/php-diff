<?php

namespace jblond\Diff\Renderer\Html;

use jblond\Diff\Renderer\MainRenderer;
use jblond\Diff\Renderer\SubRendererInterface;

/**
 * Merged diff generator for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package         jblond\Diff\Renderer\Text
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2020 Ferry Cools
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.3.0
 * @link            https://github.com/JBlond/php-diff
 */
class Merged extends MainRenderer implements SubRendererInterface
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
    private $subOptions = [
        'format'        => 'html',
        'insertMarkers' => ['<ins>', '</ins>'],
        'deleteMarkers' => ['<del>', '</del>'],
        'title1'        => 'Version1',
        'title2'        => 'Version2',
    ];
    /**
     * @var int Line offset to keep correct line number for merged diff.
     */
    private $lineOffset = 0;
    /**
     * @var string last block of lines which where removed from version 2.
     */
    private $lastDeleted;
    /**
     * @var string
     */
    private $headerClass = '';

    /**
     * Merged constructor.
     *
     * @param   array  $options  Custom defined options for the merged diff renderer.
     *
     * @see Merged::$subOptions
     */
    public function __construct(array $options = [])
    {
        parent::__construct($this->subOptions);
        $this->setOptions($options);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $changes = parent::renderSequences();

        return parent::renderOutput($changes, $this);
    }

    /**
     * @inheritDoc
     *
     * @return string Start of the diff view.
     */
    public function generateDiffHeader(): string
    {
        return <<<HTML
<table class="Differences DifferencesMerged">
    <thead>
        <tr>
            <th colspan="2">Merge of {$this->options['title1']} &amp; {$this->options['title2']}</th>
        </tr>
    </thead>
HTML;
    }

    /**
     * @inheritDoc
     *
     * @return string Start of the block.
     */
    public function generateBlockHeader(array $changes): string
    {
        return $changes['tag'] != 'delete' ? '<tbody class="Change' . ucfirst($changes['tag']) . '">' : '';
    }

    /**
     * @inheritDoc
     *
     * @return string Representation of skipped lines.
     */
    public function generateSkippedLines(): string
    {
        $html = <<<HTML
<tr>
    <th class="{$this->headerClass}" title="{$this->lastDeleted}">&hellip;</th>
    <td class="Skipped">&hellip;</td>
</tr>
HTML;

        $this->headerClass = '';
        $this->lastDeleted = null;

        return $html;
    }

    /**
     * @inheritDoc
     *
     * @return string Text with no difference.
     */
    public function generateLinesEqual(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1 + $this->lineOffset;

            $html .= <<<HTML
<tr>
    <th class="{$this->headerClass}" title="{$this->lastDeleted}">$fromLine</th>
    <td>$line</td>
</tr>
HTML;

            $this->lastDeleted = null;
            $this->headerClass = '';
        }

        return $html;
    }

    /**
     * @inheritDoc
     *
     * @return string Added text.
     */
    public function generateLinesInsert(array $changes): string
    {
        $html = '';

        foreach ($changes['changed']['lines'] as $line) {
            $this->lineOffset++;
            $toLine = $changes['base']['offset'] + $this->lineOffset;

            $html              .= <<<HTML
<tr>
    <th class="{$this->headerClass}" title="{$this->lastDeleted}">$toLine</th>
    <td><ins>$line</ins></td>
</tr>
HTML;

            $this->headerClass = '';
            $this->lastDeleted = null;
        }


        return $html;
    }

    /**
     * @inheritDoc
     *
     * @return string Removed text.
     */
    public function generateLinesDelete(array $changes): string
    {
        $this->lineOffset -= count($changes['base']['lines']);

        $title = "Lines deleted at {$this->options['title2']}:\n";

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1;

            $title .= <<<TEXT
$fromLine: $line

TEXT;
        }

        $this->lastDeleted = $title;
        $this->headerClass = 'ChangeDelete';

        return '';
    }

    /**
     * @inheritDoc
     *
     * @return string Modified text.
     */
    public function generateLinesReplace(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1 + $this->lineOffset;

            // Capture added parts.
            $addedParts = [];
            preg_match_all('/\x0.*?\x1/', $changes['changed']['lines'][$lineNo], $addedParts, PREG_PATTERN_ORDER);
            array_unshift($addedParts[0], '');

            // Concatenate removed parts with added parts.
            $line = preg_replace_callback(
                '/\x0.*?\x1/',
                function ($removedParts) use ($addedParts) {
                    $addedPart   = str_replace(["\0", "\1"], $this->options['insertMarkers'], next($addedParts[0]));
                    $removedPart = str_replace(["\0", "\1"], $this->options['deleteMarkers'], $removedParts[0]);

                    return "$removedPart$addedPart";
                },
                $line
            );

            $html              .= <<<HTML
<tr>
    <th class="{$this->headerClass}" title="{$this->lastDeleted}">$fromLine</th>
    <td>$line</td>
</tr>
HTML;
            $this->headerClass = '';
            $this->lastDeleted = null;
        }

        return $html;
    }

    /**
     * @inheritDoc
     *
     * @return string End of the block.
     */
    public function generateBlockFooter(array $changes): string
    {
        return $changes['tag'] != 'delete' ? '</tbody>' : '';
    }

    /**
     * @inheritDoc
     *
     * @return string End of the diff view.
     */
    public function generateDiffFooter(): string
    {
        return '</table>';
    }

    /**
     * @inheritDoc
     *
     * @return string Modified text.
     */
    public function generateLinesIgnore(array $changes): string
    {
        $baseLineCount    = count($changes['base']['lines']);
        $changedLineCount = count($changes['changed']['lines']);

        $this->lineOffset -= $baseLineCount;

        $title = "Lines ignored at {$this->options['title2']}: ";
        $title .= $changes['changed']['offset'] + 1 . '-' . ($changes['changed']['offset'] + $changedLineCount);

        if ($baseLineCount > $changedLineCount) {
            $title = "Lines ignored at {$this->options['title1']}: ";
            $title .= $changes['base']['offset'] + 1 . '-' . ($changes['base']['offset'] + $baseLineCount);
        }

        $this->lastDeleted = $title;
        $this->headerClass = 'ChangeIgnore';

        return '';
    }
}
