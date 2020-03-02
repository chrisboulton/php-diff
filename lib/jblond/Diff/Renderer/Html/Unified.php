<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer\Html;

/**
 * Unified HTML diff generator for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package       jblond\Diff\Renderer\Html
 * @author        Ferry Cools <info@DigiLive.nl>
 * @copyright (c) 2009 Chris Boulton
 * @license       New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version       1.16
 * @link          https://github.com/JBlond/php-diff
 */
class Unified extends HtmlArray
{
    /**
     * Render and return a unified diff-view with changes between the two sequences displayed inline (under each other).
     *
     * @return string The generated inline diff-view.
     */
    public function render(): string
    {
        $changes = parent::render();

        return $this->renderHtml($changes);
    }

    /**
     * Render the unified diff-view as html.
     *
     * Since this class extends the "HtmlArray" class which in turn extends "RendererAbstract" class, this method needs
     * to match the signature of RendererAbstract::renderHTML(). However the second parameter isn't used and can be
     * omitted.
     *
     * @param array $changes Contains the op-codes about the differences between "old and "new".
     * @param null  $object  Unused.
     *
     * @return string HTML code containing the unified differences.
     */
    public function renderHtml($changes, $object = null): string
    {
        if (empty($changes)) {
            //No changes between "old" and "new"
            return 'No differences found.';
        }

        $html = '<span class="Differences DifferencesUnified">';

        foreach ($changes as $i => $blocks) {
            if ($i > 0) {
                // If this is a separate block, we're condensing code to output …,
                // indicating a significant portion of the code has been collapsed as it did not change.
                $html .= <<<HTML
<span class="Skipped" title="Equal lines collapsed!">&hellip;</span>
HTML;
            }

            foreach ($blocks as $change) {
                $html .= '<span class="Change' . ucfirst($change['tag']) . '">';
                switch ($change['tag']) {
                    case 'equal':
                        // Add unmodified lines.
                        $html .= $this->generateLinesEqual($change);
                        break;
                    case 'insert':
                        // Add Added lines.
                        $html .= $this->generateLinesInsert($change);
                        break;
                    case 'delete':
                        // Add deleted lines.
                        $html .= $this->generateLinesDelete($change);
                        break;
                    case 'replace':
                        // Add modified lines.
                        $html .= $this->generateLinesReplace($change);
                        break;
                }
                $html .= '</span>';
            }
        }
        $html .= '</span>';

        return $html;
    }


    /**
     * Generates a string representation of blocks of text with no difference.
     *
     * @param array $change Contains the op-codes about the changes between two blocks.
     *
     * @return string HTML code representing the blocks of text with no difference.
     */
    public function generateLinesEqual(array $change): string
    {
        $html = '';

        foreach ($change['base']['lines'] as $line) {
            $html .= '<span>' . $line . '</span><br>';
        }

        return $html;
    }

    /**
     * Generates a string representation of a block of text, where new text was added.
     *
     * @param array $change Contains the op-codes about the changes between two blocks.
     *
     * @return string HTML code representing a block of added text.
     */
    public function generateLinesInsert(array $change): string
    {
        $html = '';

        foreach ($change['changed']['lines'] as $line) {
            $html .= '<span class="Right"><ins>' . $line . '</ins></span><br>';
        }

        return $html;
    }

    /**
     * Generates a string representation of a block of text, where text was removed.
     *
     * @param array $change Contains the op-codes about the changes between two blocks.
     *
     * @return string HTML code representing a block of removed text.
     */
    public function generateLinesDelete(array $change): string
    {
        $html = '';
        foreach ($change['base']['lines'] as $line) {
            $html .= '<span class="Left"><del>' . $line . '</del></span><br>';
        }

        return $html;
    }

    /**
     * Generates a string representation of a block of text, where text was partially modified.
     *
     * @param array $change Contains the op-codes about the changes between two blocks.
     *
     * @return string HTML code representing a block of modified text.
     */
    public function generateLinesReplace(array $change): string
    {
        $html = '';

        // Lines with characters removed.
        foreach ($change['base']['lines'] as $line) {
            $html .= '<span class="Left">' . $line . '</span><br>';
        }

        // Lines with characters added.
        foreach ($change['changed']['lines'] as $line) {
            $html .= '<span class="Right">' . $line . '</span><br>';
        }

        return $html;
    }
}
