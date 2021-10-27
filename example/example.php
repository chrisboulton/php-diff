<?php

use jblond\Diff;
use jblond\Diff\Renderer\Html\Merged;
use jblond\Diff\Renderer\Html\SideBySide;
use jblond\Diff\Renderer\Html\Unified;
use jblond\Diff\Renderer\Text\Context;
use jblond\Diff\Renderer\Text\Unified as TextUnified;

// Include and instantiate autoloader.
require '../vendor/autoload.php';

// Include two sample files for comparison.
$sampleA = file_get_contents(__DIR__ . '/a.txt');
$sampleB = file_get_contents(__DIR__ . '/b.txt');

// Options for generating the diff.
$diffOptions = [
    'context'          => 2,
    'trimEqual'        => false,
    'ignoreWhitespace' => true,
    'ignoreCase'       => true,
    'ignoreLines'      => Diff::DIFF_IGNORE_LINE_EMPTY,
];

// Choose one of the initializations.
$diff = new Diff($sampleA, $sampleB);                 // Initialize the diff class with default options.
//$diff = new Diff($sampleA, $sampleB, $diffOptions); // Initialize the diff class with custom options.

// Options for rendering the diff.
$rendererOptions = [
    'inlineMarking' => $_GET['inlineMarking'] ?? Diff\Renderer\MainRenderer::CHANGE_LEVEL_LINE,
]
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>PHP LibDiff - Examples</title>
    <link rel="stylesheet" type="text/css" href="styles.css"/>
    <script>
        function changeCSS(cssFile, cssLinkIndex) {

            const oldLink = document.getElementsByTagName('link').item(cssLinkIndex);

            const newLink = document.createElement('link');
            newLink.setAttribute('rel', 'stylesheet');
            newLink.setAttribute('type', 'text/css');
            newLink.setAttribute('href', cssFile);

            document.getElementsByTagName('head').item(0).replaceChild(newLink, oldLink);
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
<hr>
<aside>
    <h2>Inline Marking</h2>
    <a href="example.php?inlineMarking=2">Line</a>
    <a href="example.php?inlineMarking=1">Word</a>
    <a href="example.php?inlineMarking=0">Character</a>
    <a href="example.php?inlineMarking=4">None</a>
</aside>
<hr>
<aside>
    <h2>Informational</h2>
    Between the two versions, there's a
    <?php
    echo round($diff->getSimilarity(), 2) * 100;
    ?>% match.
</aside>
<hr>

<h2>HTML Side by Side Diff</h2>

<?php
// Generate a side by side diff.
$renderer = new SideBySide($rendererOptions);
echo $diff->isIdentical() ? 'No differences found.' : $diff->Render($renderer);
?>

<h2>HTML Unified Diff</h2>
<?php
// Generate an unified diff.
$renderer = new Unified($rendererOptions);
echo $diff->isIdentical() ? 'No differences found.' : $diff->Render($renderer);
?>

<h2>HTML Merged Diff</h2>
<?php
// Generate an merged diff.
$renderer = new Merged();
echo $diff->isIdentical() ? 'No differences found.' : $diff->Render($renderer);
?>

<h2>Text Unified Diff</h2>
<?php
// Generate a unified diff.
$renderer = new TextUnified();
echo $diff->isIdentical() ?
    'No differences found.' : '<pre>' . htmlspecialchars($diff->render($renderer)) . '</pre>';
?>

<h2>Text Context Diff</h2>
<?php
// Generate a context diff.
$renderer = new Context();
echo $diff->isIdentical() ?
    'No differences found.' : '<pre>' . htmlspecialchars($diff->render($renderer)) . '</pre>';
?>
</body>
</html>
