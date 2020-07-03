<?php

use jblond\cli\Cli;
use jblond\Diff;
use jblond\Diff\Renderer\Text\UnifiedCli;

// Include and instantiate autoloader.
require '../vendor/autoload.php';

// Include two sample files for comparison.
$a = file_get_contents(dirname(__FILE__) . '/a.txt');
$b = file_get_contents(dirname(__FILE__) . '/b.txt');

$customOptions = [
    'context'          => 2,
    'trimEqual'        => false,
    'ignoreWhitespace' => true,
    'ignoreCase'       => true,
];

// Choose one of the initializations.
$diff = new Diff($a, $b);


// Generate a unified diff.
// \jblond\Diff\Renderer\Text
$renderer = new UnifiedCli();
$cli = new Cli();
$cli->output($diff->render($renderer));
