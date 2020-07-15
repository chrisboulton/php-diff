<?php

declare(strict_types=1);

namespace jblond\Diff\Renderer;

/**
 * Sub rendering class interface for PHP DiffLib.
 *
 * PHP version 7.2 or greater
 *
 * @package     jblond\Diff\Renderer\Html
 * @author      Ferry Cools <info@DigiLive.nl>
 * @copyright   (c) 2020 Ferry Cools
 * @license     New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version     2.0.0
 * @link        https://github.com/JBlond/php-diff
 */
interface SubRenderer
{
    /**
     * Render and return a diff-view with changes between two sequences.
     *
     * @return string The generated diff-view.
     */
    public function render(): string;

    /**
     * Generate a string representation of the start of a diff view.
     *
     * @return string Start of the diff view.
     */
    public function generateDiffHeader(): string;

    /**
     * Generate a string representation of the start of a block.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string Start of the diff view.
     */
    public function generateBlockHeader(array $changes): string;

    /**
     * Generate a string representation of lines that are skipped in the diff view.
     *
     * @return string Representation of skipped lines.
     */
    public function generateSkippedLines(): string;

    /**
     * Generate a string representation of lines without differences between both versions.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string Text with no difference.
     */
    public function generateLinesEqual(array $changes): string;

    /**
     * Generate a string representation of lines that are added to the 2nd version.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string Added text.
     */
    public function generateLinesInsert(array $changes): string;

    /**
     * Generate a string representation of lines that are removed from the 2nd version.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string Removed text.
     */
    public function generateLinesDelete(array $changes): string;

    /**
     * Generate a string representation of lines that are partially modified.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string Modified text.
     */
    public function generateLinesReplace(array $changes): string;

    /**
     * Generate a string representation of the end of a block.
     *
     * @param array $changes Contains the op-codes about the changes between two blocks of text.
     *
     * @return string End of the block.
     */
    public function generateBlockFooter(array $changes): string;

    /**
     * Generate a string representation of the end of a diff view.
     *
     * @return string End of the diff view.
     */
    public function generateDiffFooter(): string;
}
