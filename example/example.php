<?php

use jblond\Diff;
use jblond\Diff\Renderer\Html\Inline;
use jblond\Diff\Renderer\Html\Unified as HtmlUnified;
use jblond\Diff\Renderer\Html\SideBySide;
use jblond\Diff\Renderer\Text\Context;
use jblond\Diff\Renderer\Text\Unified;

// Include and instantiate autoloader.
require '../vendor/autoload.php';

// Include two sample files for comparison.
$a = file_get_contents(dirname(__FILE__) . '/a.txt');
$b = file_get_contents(dirname(__FILE__) . '/b.txt');

// Options for generating the diff.
$customOptions = [
    'context'          => 2,
    'trimEqual'        => false,
    'ignoreWhitespace' => true,
    'ignoreCase'       => true,
];

// Choose one of the initializations.
$diff = new Diff($a, $b);                   // Initialize the diff class with default options.
//$diff = new Diff($a, $b, $customOptions); // Initialize the diff class with custom options.
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>PHP LibDiff - Examples</title>
        <link rel="stylesheet" type="text/css" href="styles.css" />
        <script>
            function changeCSS(cssFile, cssLinkIndex) {

                var oldLink = document.getElementsByTagName("link").item(cssLinkIndex);

                var newLink = document.createElement("link");
                newLink.setAttribute("rel", "stylesheet");
                newLink.setAttribute("type", "text/css");
                newLink.setAttribute("href", cssFile);

                document.getElementsByTagName("head").item(0).replaceChild(newLink, oldLink);
            }
        </script>
    </head>
    <body>
        <h1>PHP LibDiff - Examples</h1>
        <aside>
            <h2>Change Theme</h2>
            <a href="#" onclick="changeCSS('styles.css', 0);">Light Theme</a>
            <a href="#" onclick="changeCSS('dark-theme.css', 0);">Dark Theme</a>
        </aside>
        <hr />

        <h2>HTML Side by Side Diff</h2>

        <?php
        // Generate a side by side diff.
        // \jblond\Diff\Renderer\Html
        $renderer = new SideBySide([
            'title1' => 'Custom title for version1',
            'title2' => 'Custom title for version2',
        ]);
        echo $diff->Render($renderer);
        $cli = new \jblond\cli\Cli();
        ?>

        <h2>HTML Inline Diff</h2>

        <?php

        // Generate an inline diff.
        // \jblond\Diff\Renderer\Html
        $renderer = new Inline();
        echo $diff->render($renderer);
        ?>

        <h2>HTML Unified Diff</h2>
        <?php
        // Generate a unified diff.
        // \jblond\Diff\Renderer\Html
        $renderer = new HtmlUnified();
        echo "<pre>{$diff->render($renderer)}</pre>";
        ?>

        <h2>Text Unified Diff</h2>
        <?php
        // Generate a unified diff.
        // \jblond\Diff\Renderer\Text
        $renderer = new Unified();
        echo '<pre>' . htmlspecialchars($diff->render($renderer)) . '</pre>';
        ?>

        <h2>Text Context Diff</h2>
        <?php
        // Generate a context diff.
        // jblond\Diff\Renderer\Text\Context
        $renderer = new Context();
        echo '<pre>' . htmlspecialchars($diff->render($renderer)) . '</pre>';
        ?>
    </body>
</html>
