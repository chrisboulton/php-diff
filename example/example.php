<?php

use jblond\Autoloader;
use jblond\Diff;
use jblond\Diff\Renderer\Html\Inline;
use jblond\Diff\Renderer\Html\Unified as HtmlUnified;
use jblond\Diff\Renderer\Html\SideBySide;
use jblond\Diff\Renderer\Text\Context;
use jblond\Diff\Renderer\Text\Unified;

// Include and instantiate autoloader.
require dirname(__FILE__) . '/../lib/Autoloader.php';
new Autoloader();

// Include two sample files for comparison.
$a = file_get_contents(dirname(__FILE__) . '/a.txt');
$b = file_get_contents(dirname(__FILE__) . '/b.txt');

// Options for generating the diff.
$options = [
    //'ignoreWhitespace' => true,
    //'ignoreCase' => true,
];

// Initialize the diff class.
// \jblond\diff
$diff = new Diff($a, $b, $options);
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>PHP LibDiff - Examples</title>
        <link rel="stylesheet" href="styles.css" type="text/css" />
    </head>
    <body>
        <h1>PHP LibDiff - Examples</h1>
        <hr />

        <h2>HTML Side by Side Diff</h2>

        <?php
        // Generate a side by side diff.
        // \jblond\Diff\Renderer\Html
        $renderer = new SideBySide([
            'title_a' => 'Custom title for OLD version',
            'title_b' => 'Custom title for NEW version',
        ]);
        echo $diff->Render($renderer);

        ?>

        <h2>HTML Inline Diff</h2>

        <?php

        // Generate an inline diff.
        // \jblond\Diff\Renderer\Html
        $renderer = new Inline();
        echo $diff->render($renderer);
        ?>

        <h2>HTML Unified Diff</h2>
        <pre>
            <?php
            // Generate a unified diff.
            // \jblond\Diff\Renderer\Html
            $renderer = new HtmlUnified();
            echo $diff->render($renderer);
            ?>
        </pre>

        <h2>Text Unified Diff</h2>
        <pre>
            <?php
            // Generate a unified diff.
            // \jblond\Diff\Renderer\Text
            $renderer = new Unified();
            echo htmlspecialchars($diff->render($renderer));
            ?>
        </pre>

        <h2>Text Context Diff</h2>
        <pre>
            <?php
            // Generate a context diff.
            // jblond\Diff\Renderer\Text\Context
            $renderer = new Context();
            echo htmlspecialchars($diff->render($renderer));
            ?>
        </pre>
    </body>
</html>
