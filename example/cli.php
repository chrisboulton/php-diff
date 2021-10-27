<?php

use jblond\cli\Cli;
use jblond\Diff;
use jblond\Diff\Renderer\Text\InlineCli;
use jblond\Diff\Renderer\Text\UnifiedCli;

// Validate the interpreter.
if (PHP_SAPI !== 'cli') {
    echo 'This script demonstrates console support for the php-diff package.<br>';
    echo 'Please execute it from a cli interpreter.';
    throw new RuntimeException('Script for CLI use only!');
}


// Include and instantiate autoloader.
require '../vendor/autoload.php';

// Include two sample files for comparison.
$sampleA = file_get_contents(__DIR__ . '/a.txt');
$sampleB = file_get_contents(__DIR__ . '/b.txt');

$customOptions = [
    'context'          => 2,
    'trimEqual'        => false,
    'ignoreWhitespace' => true,
    'ignoreCase'       => true,
];

// Choose one of the initializations.
$diff = new Diff($sampleA, $sampleB);
//$diff = new Diff($a, $b, $customOptions); // Initialize the diff class with custom options.

// Instantiate Cli wrapper
$cli = new Cli();

// Generate a unified diff.
$renderer = new UnifiedCli();
echo "-= Unified Default =-\n\n";
$cli->output($diff->render($renderer));

echo "\n\n-= Unified Colored =-\n\n";

$renderer = new UnifiedCli(
// Define renderer options.
    [
        'cliColor' => true,
    ]
);

$cli->output($diff->render($renderer));


// Generate an inline diff.
$renderer = new InlineCli(
// Define renderer options.
    [
        'deleteMarkers'   => ['-', '-'],
        'insertMarkers'   => ['+', '+'],
        'equalityMarkers' => ['=', 'x'],
    ]
);
echo "-= Inline Marked =-\n\n";
$cli->output($diff->render($renderer));

echo "-= Inline Colored =-\n\n";

$coloredRenderer = new InlineCli(
// Define renderer options.
    [
        'cliColor' => true,
    ]
);

$cli->output($diff->render($coloredRenderer));
