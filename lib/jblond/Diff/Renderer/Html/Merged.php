<?php

namespace jblond\Diff\Renderer\Html;

use jblond\Diff\Renderer\MainRenderer;
use jblond\Diff\Renderer\SubRendererInterface;

/**
 * Merged diff generator for PHP DiffLib.
 *
 * PHP version 7.3 or greater
 *
 * @package         jblond\Diff\Renderer\Text
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2020 Ferry Cools
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.4.0
 * @link            https://github.com/JBlond/php-diff
 */
class Merged extends MainRenderer implements SubRendererInterface
{
    /**
     * @var array   Associative array containing the default options available for this renderer and their default
     *              value.
     *              - format            The Format of the texts.
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
        $marker      = '&hellip;';
        $headerClass = '';

        if ($this->lastDeleted !== null) {
            $headerClass = 'ChangeDelete';
        }

        $this->lastDeleted = null;

        return <<<HTML
<tr>
    <th class="$headerClass" title="$this->lastDeleted">$marker</th>
    <td class="Skipped">&hellip;</td>
</tr>
HTML;
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
            $fromLine    = $changes['base']['offset'] + $lineNo + 1 + $this->lineOffset;
            $headerClass = '';

            if (!$lineNo && $this->lastDeleted !== null) {
                $headerClass = 'ChangeDelete';
            }

            $html              .= <<<HTML
<tr>
    <th class="$headerClass" title="$this->lastDeleted">$fromLine</th>
    <td>$line</td>
</tr>
HTML;
            $this->lastDeleted = null;
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

        foreach ($changes['changed']['lines'] as $lineNo => $line) {
            $this->lineOffset++;
            $toLine      = $changes['base']['offset'] + $this->lineOffset;
            $headerClass = '';
            if (!$lineNo && $this->lastDeleted !== null) {
                $headerClass = 'ChangeDelete';
            }

            $html              .= <<<HTML
<tr>
    <th class="$headerClass" title="$this->lastDeleted">$toLine</th>
    <td><ins>$line</ins></td>
</tr>
HTML;
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

        $title = "Lines of {$this->options['title1']} deleted at {$this->options['title2']}:\n";

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1;

            $title .= <<<TEXT
$fromLine: $line

TEXT;
        }

        $this->lastDeleted = htmlentities($title);

        return '';
    }

    /**
     * @inheritDoc
     *
     * @return string Modified text.
     */
    public function generateLinesReplace(array $changes): string
    {
        $html             = '';
        $baseLineCount    = count($changes['base']['lines']);
        $changedLineCount = count($changes['changed']['lines']);

        if (count($changes['base']['lines']) == $changedLineCount) {
            // Lines of Version 1 are modified at version 2.
            foreach ($changes['base']['lines'] as $lineNo => $line) {
                $fromLine = $changes['base']['offset'] + $lineNo + 1 + $this->lineOffset;

                // Capture line-parts which are added to the same line at version 2.
                $addedParts = [];
                preg_match_all('/\x0.*?\x1/', $changes['changed']['lines'][$lineNo], $addedParts, PREG_PATTERN_ORDER);
                array_unshift($addedParts[0], '');

                // Inline Replacement:
                // Concatenate line-parts which are removed at version2 with line-parts which are added at version 2.
                $line = preg_replace_callback(
                    '/\x0.*?\x1/',
                    function ($removedParts) use ($addedParts) {
                        $addedPart   = str_replace(["\0", "\1"], $this->options['insertMarkers'], next($addedParts[0]));
                        $removedPart = str_replace(["\0", "\1"], $this->options['deleteMarkers'], $removedParts[0]);

                        return "$removedPart$addedPart";
                    },
                    $line
                );

                $html .= <<<HTML
<tr>
    <th>$fromLine</th>
    <td>$line</td>
</tr>
HTML;
            }

            return $html;
        }

        // More or less lines at version 2. Block of version 1 is replaced by block of version 2.
        $title       = '';

        foreach ($changes['changed']['lines'] as $lineNo => $line) {
            $toLine = $changes['changed']['offset'] + $lineNo + 1;

            if (!$lineNo) {
                $title       = "Lines replaced at {$this->options['title1']}:\n";
                foreach ($changes['base']['lines'] as $baseLineNo => $baseLine) {
                    $title .= $changes['base']['offset'] + $baseLineNo + 1 . ": $baseLine\n";
                }
            }

            $title = htmlentities($title);
            $html  .= <<<HTML
<tr>
    <th class="ChangeReplace" title="$title">$toLine</th>
    <td class="ChangeReplace">$line</td>
</tr>
HTML;
        }

        $this->lineOffset = $this->lineOffset + $changedLineCount - $baseLineCount;

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
}
