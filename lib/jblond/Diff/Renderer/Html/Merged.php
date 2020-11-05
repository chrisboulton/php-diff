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
 * @version         2.2.1
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
    protected $subOptions = [
        'format'        => 'html',
        'insertMarkers' => ['<ins>', '</ins>'],
        'deleteMarkers' => ['<del>', '</del>'],
        'title1'        => 'Version1',
        'title2'        => 'Version2',
    ];
    protected $lineOffset = 0;
    /**
     * @var string
     */
    protected $lastDeleted;

    /**
     * Merged constructor.
     *
     * @param   array  $options  Custom defined options for the inline diff renderer.
     *
     * @see Inline::$subOptions
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
     */
    public function generateBlockHeader(array $changes): string
    {
        return '<tbody class="Change' . ucfirst($changes['tag']) . '">';
    }

    /**
     * @inheritDoc
     */
    public function generateSkippedLines(): string
    {
        $marker = '&hellip;';
        if ($this->lastDeleted !== null) {
            $marker = "*$marker";
        }

        $this->lastDeleted = null;

        return <<<HTML
<tr>
    <th title="{$this->lastDeleted}">$marker</th>
    <td class="Skipped">&hellip;</td>
</tr>
HTML;
    }

    /**
     * @inheritDoc
     */
    public function generateLinesEqual(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1 + $this->lineOffset;
            if (!$lineNo && $this->lastDeleted !== null) {
                $fromLine = "*$fromLine";
            }

            $html              .= <<<HTML
<tr>
    <th title="{$this->lastDeleted}">$fromLine</th>
    <td>$line</td>
</tr>
HTML;
            $this->lastDeleted = null;
        }

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function generateLinesInsert(array $changes): string
    {
        $html = '';

        foreach ($changes['changed']['lines'] as $lineNo => $line) {
            $this->lineOffset++;
            $toLine = $changes['base']['offset'] + $this->lineOffset;
            if (!$lineNo && $this->lastDeleted !== null) {
                $toLine = "*$toLine";
            }

            $html              .= <<<HTML
<tr>
    <th title="{$this->lastDeleted}">$toLine</th>
    <td><ins>$line</ins></td>
</tr>
HTML;
            $this->lastDeleted = null;
        }


        return $html;
    }

    /**
     * @inheritDoc
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

        return '';
    }

    /**
     * @inheritDoc
     */
    public function generateLinesReplace(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $lineNo => $line) {
            $fromLine = $changes['base']['offset'] + $lineNo + 1 + $this->lineOffset;
            if (!$lineNo && $this->lastDeleted !== null) {
                $fromLine = "*$fromLine";
            }

            // Capture added parts.
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
    <th title="{$this->lastDeleted}">$fromLine</th>
    <td>$line</td>
</tr>
HTML;
            $this->lastDeleted = null;
        }

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function generateBlockFooter(array $changes): string
    {
        return '</tbody>';
    }

    /**
     * @inheritDoc
     */
    public function generateDiffFooter(): string
    {
        return '</table>';
    }
}
