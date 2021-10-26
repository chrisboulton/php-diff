<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Text;

use jblond\cli\CliColors;
use jblond\Diff\Renderer\MainRenderer;
use jblond\Diff\Renderer\SubRendererInterface;

/**
 * Inline diff generator for PHP DiffLib.
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
class InlineCli extends MainRenderer implements SubRendererInterface
{
    /**
     * @var array   Associative array containing the default options available for this renderer and their default
     *              value.
     */
    private $subOptions = [];

    /**
     * InlineCli constructor.
     *
     * @param   array  $options  Custom defined options for the InlineCli diff renderer.
     *
     * @see InlineCli::$subOptions
     */
    public function __construct(array $options = [])
    {
        parent::__construct($this->subOptions);
        $this->setOptions($options);
    }

    /**
     * @inheritDoc
     *
     * @return string|false The generated diff-view or false when there's no difference.
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
        return '';
    }

    /**
     * @inheritDoc
     *
     * @return string Start of the diff view.
     */
    public function generateBlockHeader(array $changes): string
    {
        return '';
    }

    /**
     * @inheritDoc
     *
     * @return string Representation of skipped lines.
     */
    public function generateSkippedLines(): string
    {
        return "...\n";
    }

    /**
     * @inheritDoc
     *
     * @return string Text with no difference.
     */
    public function generateLinesEqual(array $changes): string
    {
        $returnValue = '';
        $padding     = str_repeat(
            ' ',
            max($this->maxLineMarkerWidth - strlen($this->options['equalityMarkers'][0]), 0)
        );

        foreach ($changes['base']['lines'] as $line) {
            $returnValue .= $this->options['equalityMarkers'][0] . $padding . '|' . $line . "\n";
        }

        return $returnValue;
    }

    /**
     * @inheritDoc
     *
     * @return string Added text.
     */
    public function generateLinesInsert(array $changes): string
    {
        $colorize    = new CliColors();
        $returnValue = '';
        $padding     = str_repeat(
            ' ',
            max($this->maxLineMarkerWidth - strlen($this->options['insertMarkers'][0]), 0)
        );

        foreach ($changes['changed']['lines'] as $line) {
            if ($this->options['cliColor']) {
                [$fgColor, $bgColor] = $this->options['insertColors'];
                $line = $colorize->getColoredString($line, $fgColor, $bgColor);
            }
            $returnValue .= $this->options['insertMarkers'][0] . $padding . '|' . $line . "\n";
        }

        return $returnValue;
    }

    /**
     * @inheritDoc
     *
     * @return string Removed text.
     */
    public function generateLinesDelete(array $changes): string
    {
        $colorize    = new CliColors();
        $returnValue = '';
        $padding     = str_repeat(
            ' ',
            max($this->maxLineMarkerWidth - strlen($this->options['deleteMarkers'][0]), 0)
        );

        foreach ($changes['base']['lines'] as $line) {
            if ($this->options['cliColor']) {
                [$fgColor, $bgColor] = $this->options['deleteColors'];
                $line = $colorize->getColoredString($line, $fgColor, $bgColor);
            }
            $returnValue .= $this->options['deleteMarkers'][0] . $padding . '|' . $line . "\n";
        }

        return $returnValue;
    }

    /**
     * @inheritDoc
     *
     * @return string Modified text.
     */
    public function generateLinesReplace(array $changes): string
    {
        $returnValue = '';

        $changes['base']['lines'] = $this->mergeChanges(
            $changes['base']['lines'],
            $changes['changed']['lines'],
            $this->options['deleteColors'],
            $this->options['insertColors']
        );

        $returnValue .= implode("\n", $changes['base']['lines']) . "\n";

        return $returnValue;
    }

    /**
     * Merge the changes between two lines together and mark these changes.
     *
     * @param   array         $baseLines     Lines of version 1.
     * @param   array         $changedLines  Lines of version 2.
     * @param   array|null[]  $deleteColors  Fore- and background colors of part that is removed from the 2nd version.
     * @param   array|null[]  $insertColors  Fore- and background colors of part that is added to the 2nd version.
     *
     * Option $deleteColors and $insertColors only have affect when this class's cliColors option is set to true.
     *
     * @return array
     */
    private function mergeChanges(
        array $baseLines,
        array $changedLines,
        array $deleteColors = [null, null],
        array $insertColors = [null, null]
    ): array {
        $padding = str_repeat(
            ' ',
            max($this->maxLineMarkerWidth - strlen($this->options['equalityMarkers'][1]), 0)
        );

        foreach ($baseLines as $lineKey => $line) {
            $iterator         = 0;
            $baselineParts    = preg_split('/\x00(.*?)\x01/', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
            $changedLineParts = preg_split('/\x00(.*?)\x01/', $changedLines[$lineKey], -1, PREG_SPLIT_DELIM_CAPTURE);

            foreach ($baselineParts as $partKey => &$part) {
                if ($iterator++ % 2) {
                    // This part of the line has been changed. Surround it with user defined markers.
                    $basePart    = $this->options['deleteMarkers'][0] . $part . $this->options['deleteMarkers'][1];
                    $changedPart = '';
                    if (isset($changedLineParts[$partKey])) {
                        $changedPart =
                            $this->options['insertMarkers'][0] .
                            $changedLineParts[$partKey] .
                            $this->options['insertMarkers'][1];
                    }

                    if ($this->options['cliColor']) {
                        $colorize = new CliColors();
                        $basePart = $colorize->getColoredString($basePart, ...$deleteColors);
                        if (!empty($changedPart)) {
                            $changedPart = $colorize->getColoredString($changedPart, ...$insertColors);
                        }
                    }
                    $part = $basePart . $changedPart;
                }
            }
            unset($part);
            $baseLines[$lineKey] = $this->options['equalityMarkers'][1] . $padding . '|' . implode('', $baselineParts);
        }

        return $baseLines;
    }

    /**
     * @inheritDoc
     *
     * @return string End of the block
     */
    public function generateBlockFooter(array $changes): string
    {
        return '';
    }

    /**
     * @inheritDoc
     *
     * @return string End of the diff view.
     */
    public function generateDiffFooter(): string
    {
        return '';
    }
}
