<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Html;

use jblond\Diff\Renderer\MainRenderer;
use jblond\Diff\Renderer\SubRendererInterface;

/**
 * Unified HTML diff generator for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package         jblond\Diff\Renderer\Html
 * @author          Chris Boulton <chris.boulton@interspire.com>
 * @author          Mario Brandt <leet31337@web.de>
 * @author          Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2009 Chris Boulton
 * @license         New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version         2.3.0
 * @link            https://github.com/JBlond/php-diff
 */
class Unified extends MainRenderer implements SubRendererInterface
{
    /**
     * @var array   Associative array containing the default options available for this renderer and their default
     *              value.
     *              - format            Format of the texts.
     *              - insertMarkers     Markers for inserted text.
     *              - deleteMarkers     Markers for removed text.
     *              - title1            Title of the "old" version of text.
     *              - title2            Title of the "new" version of text.
     */
    private $subOptions = [
        'format'        => 'html',
        'insertMarkers' => ['<ins>', '</ins>'],
        'deleteMarkers' => ['<del>', '</del>'],
        'title1'        => 'Version1',
        'title2'        => 'Version2',
    ];

    /**
     * Unified constructor.
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
     * @return string HTML code representation of the diff-view header.
     */
    public function generateDiffHeader(): string
    {
        return '<span class="Differences DifferencesUnified">';
    }

    /**
     * @inheritDoc
     *
     * @return string HTML code representation of a table's header.
     */
    public function generateSkippedLines(): string
    {
        return '<span class="Skipped" title="Equal lines collapsed!">&hellip;</span>';
    }


    /**
     * @inheritDoc
     *
     * @return string HTML code representing the blocks of text with no difference.
     */
    public function generateLinesEqual(array $changes): string
    {
        $html = '';

        foreach ($changes['base']['lines'] as $line) {
            $html .= '<span>' . $line . '</span><br>';
        }

        return $html;
    }

    /**
     * @inheritDoc
     *
     * @return string HTML code representing a block of added text.
     */
    public function generateLinesInsert(array $changes): string
    {
        $html = '';

        foreach ($changes['changed']['lines'] as $line) {
            $html .= '<span class="Right"><ins>' . $line . '</ins></span><br>';
        }

        return $html;
    }

    /**
     * @inheritDoc
     *
     * @return string HTML code representing a block of removed text.
     */
    public function generateLinesDelete(array $changes): string
    {
        $html = '';
        foreach ($changes['base']['lines'] as $line) {
            $html .= '<span class="Left"><del>' . $line . '</del></span><br>';
        }

        return $html;
    }

    /**
     * @inheritDoc
     *
     * @return string HTML code representing a block of modified text.
     */
    public function generateLinesReplace(array $changes): string
    {
        $html = '';

        // Lines with characters removed.
        foreach ($changes['base']['lines'] as $line) {
            $line = str_replace(["\0", "\1"], $this->options['deleteMarkers'], $line);
            $html .= '<span class="Left">' . $line . '</span><br>';
        }

        // Lines with characters added.
        foreach ($changes['changed']['lines'] as $line) {
            $line = str_replace(["\0", "\1"], $this->options['insertMarkers'], $line);
            $html .= '<span class="Right">' . $line . '</span><br>';
        }

        return $html;
    }

    /**
     * @inheritDoc
     *
     * @return string Start of the block.
     */
    public function generateBlockHeader(array $changes): string
    {
        return '<span class="Change' . ucfirst($changes['tag']) . '">';
    }

    /**
     * @inheritDoc
     *
     * @return string End of the block.
     */
    public function generateBlockFooter(array $changes): string
    {
        return '</span>';
    }

    /**
     * @inheritDoc
     *
     * @return string End of the diff view.
     */
    public function generateDiffFooter(): string
    {
        return '</span>';
    }
}
